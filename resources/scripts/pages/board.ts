import Controller from "@/utils/Controller";

type BoardColumn = "in_progress" | "completed" | "overdue";
type EditableBoardColumn = Exclude<BoardColumn, "overdue">;
type BoardItemType = "task" | "subtask";
type StoredTaskStatus = "draft" | "in_progress" | "completed";
type TaskView = "list" | "calendar";

type CalendarTask = {
    id: number;
    type: BoardItemType;
    title: string;
    description: string | null;
    project: string | null;
    parent: string | null;
    createdDate: string | null;
    dueDate: string | null;
    status: StoredTaskStatus;
    priority: string;
    assignee_id: number | string | null;
    updateUrl: string;
};

type DragState = {
    card: HTMLElement;
    itemId: number;
    itemType: BoardItemType;
    originalStatus: BoardColumn;
    originalTaskStatus: string;
    originalContainer: HTMLElement;
    originalNextSibling: ChildNode | null;
    updateUrl: string;
};

const editableColumns: EditableBoardColumn[] = ["in_progress", "completed"];
const weekdayCount = 7;
const calendarWeekCount = 6;
const visibleTaskLimit = 3;

export default class BoardController extends Controller {
    private dragState: DragState | null = null;
    private calendarTasks: CalendarTask[] = [];
    private activeCalendarDate: Date | null = new Date();
    private activeDetailTaskKey: string | null = null;
    private suppressCardClickUntil = 0;

    protected initialize(): void {
        this.activeCalendarDate = this.getInitialCalendarDate();
        this.calendarTasks = this.mergeCalendarTasks(
            this.getCalendarTasks(),
            this.getCalendarTasksFromMarkup(),
        );

        this.bindTaskCards();
        this.bindCalendarTaskButtons();
        this.bindColumnDropTargets();
        this.bindViewSwitcher();
        this.bindCalendarControls();
        this.bindDetailModal();
        this.refreshAllColumns();
        this.renderCalendar(
            this.getActiveCalendarDate().getFullYear(),
            this.getActiveCalendarDate().getMonth(),
        );
    }

    private bindTaskCards(): void {
        this.querySelectorAll<HTMLElement>(".task-card").forEach((card) => {
            card.addEventListener("click", () => {
                if (Date.now() < this.suppressCardClickUntil) {
                    return;
                }

                this.openTaskDetails(card.dataset.taskKey ?? "", card);
            });

            card.addEventListener("keydown", (event) => {
                if (event.key !== "Enter" && event.key !== " ") {
                    return;
                }

                event.preventDefault();
                this.openTaskDetails(card.dataset.taskKey ?? "", card);
            });

            card.addEventListener("dragstart", (event) => {
                const itemId = Number(card.dataset.itemId);
                const itemType = this.getItemType(card);
                const originalStatus = this.getCardStatus(card);
                const originalContainer = card.closest<HTMLElement>(".task-card-container");
                const updateUrl = card.dataset.updateUrl ?? "";

                if (
                    itemId <= 0 ||
                    itemType == null ||
                    originalStatus == null ||
                    originalContainer == null ||
                    updateUrl.length === 0
                ) {
                    event.preventDefault();
                    return;
                }

                this.dragState = {
                    card,
                    itemId,
                    itemType,
                    originalStatus,
                    originalTaskStatus: card.dataset.taskStatus ?? "",
                    originalContainer,
                    originalNextSibling: card.nextSibling,
                    updateUrl,
                };

                card.classList.add("is-dragging");
                event.dataTransfer?.setData("text/plain", String(itemId));

                if (event.dataTransfer != null) {
                    event.dataTransfer.effectAllowed = "move";
                }
            });

            card.addEventListener("dragend", () => {
                card.classList.remove("is-dragging");
                this.suppressCardClickUntil = Date.now() + 250;
                this.clearAllDropStates();
                this.dragState = null;
            });
        });
    }

    private bindColumnDropTargets(): void {
        this.querySelectorAll<HTMLElement>(".task-column").forEach((column) => {
            column.addEventListener("dragover", (event) => {
                event.preventDefault();

                const targetStatus = this.getColumnStatus(column);
                const isBlocked = targetStatus === "overdue";

                column.classList.toggle("is-drop-blocked", isBlocked);
                column.classList.toggle("is-drag-over", !isBlocked);

                if (event.dataTransfer != null) {
                    event.dataTransfer.dropEffect = isBlocked ? "none" : "move";
                }
            });

            column.addEventListener("dragleave", () => {
                this.clearDropState(column);
            });

            column.addEventListener("drop", async (event) => {
                event.preventDefault();
                this.clearDropState(column);

                const targetStatus = this.getColumnStatus(column);
                const targetContainer = targetStatus == null
                    ? null
                    : this.getColumnContainer(targetStatus);

                if (
                    this.dragState == null ||
                    targetStatus == null ||
                    targetContainer == null ||
                    !this.isValidStatusChange(this.dragState.originalStatus, targetStatus)
                ) {
                    return;
                }

                await this.moveBoardItem(this.dragState, targetStatus, targetContainer);
            });
        });
    }

    private bindViewSwitcher(): void {
        this.querySelectorAll<HTMLButtonElement>("[data-task-view]").forEach((button) => {
            button.addEventListener("click", () => {
                const view = this.getTaskView(button);

                if (view == null) {
                    return;
                }

                this.setTaskView(view);
            });
        });
    }

    private bindCalendarControls(): void {
        this.querySelector<HTMLButtonElement>("[data-calendar-previous]")?.addEventListener("click", () => {
            const activeDate = this.getActiveCalendarDate();
            this.activeCalendarDate = new Date(
                activeDate.getFullYear(),
                activeDate.getMonth() - 1,
                1,
            );
            this.renderCalendar(
                this.getActiveCalendarDate().getFullYear(),
                this.getActiveCalendarDate().getMonth(),
            );
        });

        this.querySelector<HTMLButtonElement>("[data-calendar-next]")?.addEventListener("click", () => {
            const activeDate = this.getActiveCalendarDate();
            this.activeCalendarDate = new Date(
                activeDate.getFullYear(),
                activeDate.getMonth() + 1,
                1,
            );
            this.renderCalendar(
                this.getActiveCalendarDate().getFullYear(),
                this.getActiveCalendarDate().getMonth(),
            );
        });

        this.querySelector<HTMLButtonElement>("[data-calendar-today]")?.addEventListener("click", () => {
            this.activeCalendarDate = new Date();
            this.renderCalendar(
                this.getActiveCalendarDate().getFullYear(),
                this.getActiveCalendarDate().getMonth(),
            );
        });
    }

    private bindDetailModal(): void {
        this.querySelectorAll<HTMLElement>("[data-task-detail-close]").forEach((element) => {
            element.addEventListener("click", () => this.closeTaskDetails());
        });
        this.querySelector<HTMLSelectElement>("[data-detail-status-control]")?.addEventListener("change", (event) => {
            const statusControl = event.currentTarget as HTMLSelectElement | null;

            if (statusControl == null) {
                return;
            }

            void this.updateDetailStatus(statusControl.value);
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                this.closeTaskDetails();
            }
        });
    }

    private setTaskView(view: TaskView): void {
        const listView = this.querySelector<HTMLElement>("#task-list-view");
        const calendarView = this.querySelector<HTMLElement>("#task-calendar-view");

        if (listView == null || calendarView == null) {
            return;
        }

        listView.hidden = view !== "list";
        calendarView.hidden = view !== "calendar";

        this.querySelectorAll<HTMLButtonElement>("[data-task-view]").forEach((button) => {
            const isActive = button.dataset.taskView === view;
            button.classList.toggle("is-active", isActive);
            button.setAttribute("aria-pressed", String(isActive));
        });

        if (view === "calendar") {
            const activeDate = this.getActiveCalendarDate();
            this.renderCalendar(
                activeDate.getFullYear(),
                activeDate.getMonth(),
            );
        }
    }

    private async moveBoardItem(
        dragState: DragState,
        targetStatus: EditableBoardColumn,
        targetContainer: HTMLElement,
    ): Promise<void> {
        const displayStatus = this.getDisplayStatusForCard(dragState.card, targetStatus);
        const displayContainer = this.getColumnContainer(displayStatus) ?? targetContainer;

        this.placeCard(dragState.card, displayContainer, displayStatus, targetStatus);

        try {
            const token = this.getCsrfToken();
            const response = await fetch(dragState.updateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: this.buildUpdatePayload(dragState.card, targetStatus, token),
            });

            if (!response.ok) {
                throw new Error(`Unable to update ${dragState.itemType} status.`);
            }

            this.updateCalendarTaskStatus(dragState.card, targetStatus);
        } catch (error) {
            this.restoreTaskPosition(dragState);
            console.error(error);
        } finally {
            const activeDate = this.getActiveCalendarDate();
            this.refreshAllColumns();
            this.renderCalendar(
                activeDate.getFullYear(),
                activeDate.getMonth(),
            );
        }
    }

    private placeCard(
        card: HTMLElement,
        targetContainer: HTMLElement,
        displayStatus: BoardColumn,
        taskStatus: StoredTaskStatus,
    ): void {
        targetContainer.appendChild(card);
        card.dataset.currentStatus = displayStatus;
        card.dataset.taskStatus = taskStatus;
        this.updateStatusIndicator(card, displayStatus);
        this.refreshAllColumns();
    }

    private buildUpdatePayload(
        card: HTMLElement,
        targetStatus: StoredTaskStatus,
        token: string,
    ): URLSearchParams {
        return new URLSearchParams({
            assignee_id: card.dataset.assigneeId ?? "",
            title: card.dataset.title ?? "",
            due_date: card.dataset.dueDate ?? "",
            priority: card.dataset.priority ?? "",
            status: targetStatus,
            _token: token,
            _method: "PUT",
        });
    }

    private restoreTaskPosition(dragState: DragState): void {
        dragState.originalContainer.insertBefore(
            dragState.card,
            dragState.originalNextSibling,
        );
        dragState.card.dataset.currentStatus = dragState.originalStatus;
        dragState.card.dataset.taskStatus = dragState.originalTaskStatus;
        this.updateStatusIndicator(dragState.card, dragState.originalStatus);
    }

    private refreshAllColumns(): void {
        this.querySelectorAll<HTMLElement>(".task-column").forEach((column) => {
            const status = this.getColumnStatus(column);

            if (status != null) {
                this.refreshColumn(status);
            }
        });
    }

    private refreshColumn(status: BoardColumn): void {
        const container = this.getColumnContainer(status);
        const count = this.querySelector<HTMLElement>(`[data-count-for="${status}"]`);
        const emptyState = this.querySelector<HTMLElement>(
            `[data-empty-state="${status}"]`,
        );
        const taskCount = container?.querySelectorAll(".task-card").length ?? 0;

        if (count != null) {
            count.textContent = String(taskCount);
        }

        if (container != null) {
            container.hidden = taskCount === 0;
        }

        if (emptyState != null) {
            emptyState.hidden = taskCount > 0;
        }
    }

    private renderCalendar(year: number, month: number): void {
        const title = this.querySelector<HTMLElement>("[data-calendar-title]");
        const grid = this.querySelector<HTMLElement>("[data-calendar-grid]");

        if (title == null || grid == null) {
            return;
        }

        title.textContent = new Intl.DateTimeFormat("en-US", {
            month: "long",
            year: "numeric",
        }).format(new Date(year, month, 1));

        if (
            grid.children.length > 0 &&
            Number(grid.dataset.calendarYear) === year &&
            Number(grid.dataset.calendarMonth) === month
        ) {
            this.bindCalendarTaskButtons();
            return;
        }

        this.calendarTasks = this.mergeCalendarTasks(
            this.calendarTasks,
            this.getCalendarTasksFromMarkup(),
        );

        grid.replaceChildren();
        grid.dataset.calendarYear = String(year);
        grid.dataset.calendarMonth = String(month);

        const groupedTasks = this.groupTasksByDueDate(this.calendarTasks);
        const todayKey = this.toDateKey(new Date());

        this.getCalendarDates(year, month).forEach((date) => {
            const dateKey = this.toDateKey(date);
            const tasks = groupedTasks.get(dateKey) ?? [];
            const cell = document.createElement("div");
            const cellHeader = document.createElement("div");
            const dateNumber = document.createElement("span");
            const taskList = document.createElement("div");

            cell.className = "calendar-day";
            cell.classList.toggle("is-other-month", date.getMonth() !== month);
            cell.classList.toggle("is-today", dateKey === todayKey);
            cell.dataset.date = dateKey;

            cellHeader.className = "calendar-day-header";
            dateNumber.textContent = String(date.getDate());
            cellHeader.appendChild(dateNumber);
            cell.appendChild(cellHeader);

            taskList.className = "calendar-task-list";
            tasks.slice(0, visibleTaskLimit).forEach((task) => {
                taskList.appendChild(this.createCalendarTaskButton(task));
            });

            if (tasks.length > visibleTaskLimit) {
                const more = document.createElement("span");
                more.className = "calendar-more";
                more.textContent = `+${tasks.length - visibleTaskLimit} more`;
                taskList.appendChild(more);
            }

            cell.appendChild(taskList);
            grid.appendChild(cell);
        });
    }

    private createCalendarTaskButton(task: CalendarTask): HTMLButtonElement {
        const button = document.createElement("button");
        const status = this.getCalendarTaskStatus(task);
        const taskDate = this.getCalendarTaskDate(task);

        button.type = "button";
        button.className = `calendar-task calendar-task--${status} calendar-task--priority-${task.priority}`;
        button.dataset.calendarTask = "";
        button.dataset.taskKey = this.getTaskKey(task);
        button.dataset.itemId = String(task.id);
        button.dataset.itemType = task.type;
        button.dataset.title = task.title;
        button.dataset.projectName = task.project ?? "";
        button.dataset.parentTask = task.parent ?? "None";
        button.dataset.startDate = task.createdDate ?? "";
        button.dataset.dueDate = task.dueDate ?? "";
        button.dataset.taskDate = taskDate ?? "";
        button.dataset.priority = task.priority;
        button.dataset.status = task.status;
        button.dataset.assigneeId = task.assignee_id == null ? "" : String(task.assignee_id);
        button.dataset.updateUrl = task.updateUrl;
        button.textContent = task.title;
        button.title = `${task.title} - ${this.formatStatus(status)} - ${this.formatPriority(task.priority)}`;
        button.setAttribute(
            "aria-label",
            `${task.title}, ${this.formatStatus(status)}, ${this.formatPriority(task.priority)} priority`,
        );
        button.addEventListener("click", () => this.openTaskDetails(this.getTaskKey(task), button));

        return button;
    }

    private getCalendarDates(year: number, month: number): Date[] {
        const firstDate = new Date(year, month, 1);
        const calendarStart = new Date(year, month, 1 - firstDate.getDay());
        const totalCells = weekdayCount * calendarWeekCount;

        return Array.from({ length: totalCells }, (_, index) => (
            new Date(
                calendarStart.getFullYear(),
                calendarStart.getMonth(),
                calendarStart.getDate() + index,
            )
        ));
    }

    private groupTasksByDueDate(tasks: CalendarTask[]): Map<string, CalendarTask[]> {
        return tasks.reduce((grouped, task) => {
            const taskDate = this.getCalendarTaskDate(task);

            if (taskDate == null) {
                return grouped;
            }

            const existing = grouped.get(taskDate) ?? [];
            existing.push(task);
            grouped.set(taskDate, existing);

            return grouped;
        }, new Map<string, CalendarTask[]>());
    }

    private bindCalendarTaskButtons(): void {
        this.querySelectorAll<HTMLButtonElement>("[data-calendar-task]").forEach((button) => {
            if (button.dataset.isCalendarBound === "true") {
                return;
            }

            button.dataset.isCalendarBound = "true";
            button.addEventListener("click", () => this.openTaskDetails(button.dataset.taskKey ?? "", button));
        });
    }

    private openTaskDetails(taskKey: string, sourceElement?: HTMLElement): void {
        const task = this.getTaskForDetails(taskKey, sourceElement);
        const modal = this.querySelector<HTMLElement>("[data-task-detail-modal]");

        if (task == null || modal == null) {
            return;
        }

        this.setDetailText("[data-detail-type]", task.type === "subtask" ? "Subtask" : "Task");
        this.setDetailText("[data-detail-title]", task.title);
        this.setDetailText("[data-detail-project]", task.project || "-");
        this.setDetailText("[data-detail-parent]", task.parent || "None");
        this.setDetailText("[data-detail-start-date]", this.formatDate(task.createdDate));
        this.setDetailText("[data-detail-due-date]", this.formatDate(task.dueDate));
        this.setDetailText("[data-detail-priority]", this.formatPriority(task.priority));
        this.activeDetailTaskKey = taskKey;
        const statusControl = this.querySelector<HTMLSelectElement>("[data-detail-status-control]");
        if (statusControl != null) {
            statusControl.value = task.status === "completed" ? "completed" : "in_progress";
            statusControl.disabled = false;
        }

        modal.hidden = false;
        modal.querySelector<HTMLButtonElement>("[data-task-detail-close]")?.focus();
    }

    private closeTaskDetails(): void {
        const modal = this.querySelector<HTMLElement>("[data-task-detail-modal]");

        if (modal != null) {
            modal.hidden = true;
        }
        this.activeDetailTaskKey = null;
    }

    private setDetailText(selector: string, value: string): void {
        const element = this.querySelector<HTMLElement>(selector);

        if (element != null) {
            element.textContent = value;
        }
    }

    private updateCalendarTaskStatus(card: HTMLElement, status: EditableBoardColumn): void {
        const taskKey = card.dataset.taskKey ?? "";
        const task = this.calendarTasks.find((item) => this.getTaskKey(item) === taskKey);

        if (task != null) {
            task.status = status;
            this.syncCalendarTaskButtons(task);
        }
    }

    private async updateDetailStatus(value: string): Promise<void> {
        if (!this.isEditableTaskStatus(value) || this.activeDetailTaskKey == null) {
            return;
        }

        const task = this.calendarTasks.find((item) => this.getTaskKey(item) === this.activeDetailTaskKey);
        const statusControl = this.querySelector<HTMLSelectElement>("[data-detail-status-control]");

        if (task == null) {
            return;
        }

        const previousStatus = task.status;
        statusControl && (statusControl.disabled = true);

        try {
            const token = this.getCsrfToken();
            const response = await fetch(task.updateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: this.buildTaskUpdatePayload(task, value, token),
            });

            if (!response.ok) {
                throw new Error(`Unable to update ${task.type} status.`);
            }

            task.status = value;
            this.syncCardStatus(task);
            this.syncCalendarTaskButtons(task);
            this.openTaskDetails(this.getTaskKey(task));
        } catch (error) {
            task.status = previousStatus;
            if (statusControl != null) {
                statusControl.value = previousStatus;
            }
            console.error(error);
        } finally {
            statusControl && (statusControl.disabled = false);
            const activeDate = this.getActiveCalendarDate();
            this.refreshAllColumns();
            this.renderCalendar(activeDate.getFullYear(), activeDate.getMonth());
        }
    }

    private buildTaskUpdatePayload(
        task: CalendarTask,
        status: StoredTaskStatus,
        token: string,
    ): URLSearchParams {
        return new URLSearchParams({
            assignee_id: task.assignee_id == null ? "" : String(task.assignee_id),
            title: task.title,
            due_date: task.dueDate ?? "",
            priority: task.priority,
            status,
            _token: token,
            _method: "PUT",
        });
    }

    private syncCardStatus(task: CalendarTask): void {
        const card = this.querySelector<HTMLElement>(`.task-card[data-task-key="${this.getTaskKey(task)}"]`);

        if (card == null) {
            return;
        }

        const displayStatus = this.getCalendarTaskStatus(task);
        const container = this.getColumnContainer(displayStatus);

        if (container != null) {
            this.placeCard(card, container, displayStatus, task.status);
        }
    }

    private syncCalendarTaskButtons(task: CalendarTask): void {
        const status = this.getCalendarTaskStatus(task);

        this.querySelectorAll<HTMLButtonElement>(`[data-calendar-task][data-task-key="${this.getTaskKey(task)}"]`).forEach((button) => {
            button.dataset.status = task.status;
            button.classList.remove(
                "calendar-task--in_progress",
                "calendar-task--completed",
                "calendar-task--overdue",
            );
            button.classList.add(`calendar-task--${status}`);
            button.title = `${task.title} - ${this.formatStatus(status)} - ${this.formatPriority(task.priority)}`;
        });
    }

    private getCalendarTaskStatus(task: CalendarTask): BoardColumn {
        if (task.status === "completed") {
            return "completed";
        }

        if (task.dueDate != null && task.dueDate < this.toDateKey(new Date())) {
            return "overdue";
        }

        return "in_progress";
    }

    private getDisplayStatusForCard(
        card: HTMLElement,
        nextTaskStatus: EditableBoardColumn,
    ): BoardColumn {
        if (nextTaskStatus === "completed") {
            return "completed";
        }

        const dueDate = card.dataset.dueDate ?? "";

        if (dueDate.length > 0 && dueDate < this.toDateKey(new Date())) {
            return "overdue";
        }

        return "in_progress";
    }

    private getCalendarTasks(): CalendarTask[] {
        const dataElement = this.querySelector<HTMLScriptElement>("#task-calendar-data");

        if (dataElement == null) {
            return [];
        }

        try {
            const parsed: unknown = JSON.parse(dataElement.textContent ?? "[]");
            const items = Array.isArray(parsed)
                ? parsed
                : typeof parsed === "object" && parsed != null
                    ? Object.values(parsed)
                    : null;

            if (items == null) {
                return [];
            }

            return items
                .map((item) => this.normalizeCalendarTask(item))
                .filter((item): item is CalendarTask => item != null);
        } catch (error) {
            console.error(error);
            return [];
        }
    }

    private getCalendarTasksFromMarkup(): CalendarTask[] {
        return Array.from(this.querySelectorAll<HTMLButtonElement>("[data-calendar-task]"))
            .map((button) => this.normalizeCalendarTask({
                id: button.dataset.itemId,
                type: button.dataset.itemType,
                title: button.dataset.title ?? button.textContent,
                project: button.dataset.projectName,
                parent: button.dataset.parentTask,
                createdDate: button.dataset.startDate,
                dueDate: button.dataset.dueDate,
                status: button.dataset.status,
                priority: button.dataset.priority,
                assignee_id: button.dataset.assigneeId,
                updateUrl: button.dataset.updateUrl,
            }))
            .filter((item): item is CalendarTask => item != null);
    }

    private mergeCalendarTasks(
        primaryTasks: CalendarTask[],
        fallbackTasks: CalendarTask[],
    ): CalendarTask[] {
        const merged = new Map<string, CalendarTask>();

        primaryTasks.forEach((task) => {
            merged.set(this.getTaskKey(task), task);
        });

        fallbackTasks.forEach((task) => {
            const taskKey = this.getTaskKey(task);
            const existingTask = merged.get(taskKey);

            merged.set(taskKey, {
                ...task,
                ...existingTask,
                status: existingTask?.status ?? task.status,
            });
        });

        return Array.from(merged.values());
    }

    private getTaskForDetails(taskKey: string, sourceElement?: HTMLElement): CalendarTask | null {
        const existingTask = this.calendarTasks.find((item) => this.getTaskKey(item) === taskKey);

        if (existingTask != null) {
            return existingTask;
        }

        const sourceTask = sourceElement == null
            ? null
            : this.normalizeCalendarTask({
                id: sourceElement.dataset.itemId,
                type: sourceElement.dataset.itemType,
                title: sourceElement.dataset.title ?? sourceElement.textContent,
                project: sourceElement.dataset.projectName,
                parent: sourceElement.dataset.parentTask,
                createdDate: sourceElement.dataset.startDate,
                dueDate: sourceElement.dataset.dueDate,
                status: sourceElement.dataset.status ?? sourceElement.dataset.taskStatus,
                priority: sourceElement.dataset.priority,
                assignee_id: sourceElement.dataset.assigneeId,
                updateUrl: sourceElement.dataset.updateUrl,
            });

        if (sourceTask == null || this.getTaskKey(sourceTask) !== taskKey) {
            return null;
        }

        this.calendarTasks.push(sourceTask);

        return sourceTask;
    }

    private getInitialCalendarDate(): Date {
        const grid = this.querySelector<HTMLElement>("[data-calendar-grid]");
        const selectedDate = this.parseDateKey(grid?.dataset.selectedDate);

        if (selectedDate != null) {
            return selectedDate;
        }

        const year = Number(grid?.dataset.calendarYear);
        const month = Number(grid?.dataset.calendarMonth);

        if (Number.isFinite(year) && Number.isFinite(month)) {
            return new Date(year, month, 1);
        }

        return new Date();
    }

    private getActiveCalendarDate(): Date {
        if (!(this.activeCalendarDate instanceof Date)) {
            this.activeCalendarDate = new Date();
        }

        return this.activeCalendarDate;
    }

    private normalizeCalendarTask(value: unknown): CalendarTask | null {
        if (typeof value !== "object" || value == null) {
            return null;
        }

        const task = value as Record<string, unknown>;
        const id = Number(task.id);
        const type = task.type === "subtask" ? "subtask" : task.type === "task" ? "task" : null;
        const status = this.isStoredTaskStatus(task.status) ? task.status : null;

        if (!Number.isFinite(id) || type == null || status == null) {
            return null;
        }

        return {
            id,
            type,
            title: String(task.title ?? ""),
            description: this.toNullableString(task.description),
            project: this.toNullableString(task.project ?? task.project_title),
            parent: this.toNullableString(task.parent ?? task.parent_title),
            createdDate: this.toDateString(task.createdDate ?? task.created_at),
            dueDate: this.toDateString(task.dueDate ?? task.due_date),
            status,
            priority: String(task.priority ?? "medium"),
            assignee_id: this.toNullableString(task.assignee_id),
            updateUrl: String(task.updateUrl ?? task.update_url ?? `/${type}/${id}`),
        };
    }

    private isStoredTaskStatus(value: unknown): value is StoredTaskStatus {
        return value === "draft" || value === "in_progress" || value === "completed";
    }

    private isEditableTaskStatus(value: unknown): value is EditableBoardColumn {
        return value === "in_progress" || value === "completed";
    }

    private getCalendarTaskDate(task: CalendarTask): string | null {
        return task.dueDate ?? task.createdDate;
    }

    private toNullableString(value: unknown): string | null {
        if (value == null || value === "") {
            return null;
        }

        return String(value);
    }

    private toDateString(value: unknown): string | null {
        if (value == null || value === "") {
            return null;
        }

        const text = String(value);
        const match = text.match(/^\d{4}-\d{2}-\d{2}/);

        return match?.[0] ?? null;
    }

    private parseDateKey(value: string | undefined): Date | null {
        if (value == null || value.length === 0) {
            return null;
        }

        const [year, month, day] = value.split("-").map(Number);

        if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) {
            return null;
        }

        return new Date(year, month - 1, day);
    }

    private isValidStatusChange(
        originalStatus: BoardColumn,
        targetStatus: BoardColumn,
    ): targetStatus is EditableBoardColumn {
        if (!editableColumns.includes(targetStatus as EditableBoardColumn)) {
            return false;
        }

        if (originalStatus === targetStatus) {
            return false;
        }

        return originalStatus !== "overdue" || targetStatus === "completed";
    }

    private updateStatusIndicator(card: HTMLElement, status: BoardColumn): void {
        const indicator = card.querySelector<HTMLElement>(".task-status-indicator");

        if (indicator == null) {
            return;
        }

        indicator.classList.remove(
            "task-status-indicator--in_progress",
            "task-status-indicator--completed",
            "task-status-indicator--overdue",
        );
        indicator.classList.add(`task-status-indicator--${status}`);
    }

    private getTaskView(button: HTMLButtonElement): TaskView | null {
        if (button.dataset.taskView === "list" || button.dataset.taskView === "calendar") {
            return button.dataset.taskView;
        }

        return null;
    }

    private getColumnStatus(column: HTMLElement): BoardColumn | null {
        return this.toBoardColumn(column.dataset.column);
    }

    private getCardStatus(card: HTMLElement): BoardColumn | null {
        return this.toBoardColumn(card.dataset.currentStatus);
    }

    private getColumnContainer(status: BoardColumn): HTMLElement | null {
        return this.querySelector<HTMLElement>(`[data-card-container="${status}"]`);
    }

    private getItemType(card: HTMLElement): BoardItemType | null {
        if (card.dataset.itemType === "task" || card.dataset.itemType === "subtask") {
            return card.dataset.itemType;
        }

        return null;
    }

    private toBoardColumn(value: string | undefined): BoardColumn | null {
        if (value === "in_progress" || value === "completed" || value === "overdue") {
            return value;
        }

        return null;
    }

    private getTaskKey(task: CalendarTask): string {
        return `${task.type}-${task.id}`;
    }

    private toDateKey(date: Date): string {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    private formatDate(value: string | null): string {
        if (value == null || value.length === 0) {
            return "-";
        }

        const [year, month, day] = value.split("-").map(Number);

        if (year == null || month == null || day == null) {
            return value;
        }

        return new Intl.DateTimeFormat(undefined, {
            month: "short",
            day: "numeric",
            year: "numeric",
        }).format(new Date(year, month - 1, day));
    }

    private formatStatus(status: BoardColumn | string): string {
        return {
            draft: "Draft",
            in_progress: "In Progress",
            completed: "Completed",
            overdue: "Overdue",
        }[status] ?? status;
    }

    private formatPriority(priority: string): string {
        if (priority.length === 0) {
            return "Medium";
        }

        return priority.charAt(0).toUpperCase() + priority.slice(1);
    }

    private clearAllDropStates(): void {
        this.querySelectorAll<HTMLElement>(".task-column").forEach((column) => {
            this.clearDropState(column);
        });
    }

    private clearDropState(column: HTMLElement): void {
        column.classList.remove("is-drag-over", "is-drop-blocked");
    }

    private getCsrfToken(): string {
        return document.querySelector<HTMLMetaElement>("meta[name='csrf-token']")?.content ?? "";
    }
}
