import Controller from "../utils/controller";

type TaskStatus = "assigned" | "in-progress" | "done";
type TaskPriority = "" | "Low" | "Medium" | "High";

interface Task {
    id: number;

    name: string;

    assigned: string;

    assigneeId: string;

    dueDate: string;

    priority: TaskPriority;

    status: TaskStatus;

    subtasks: Task[];

    expanded: boolean;

    draft?: boolean;

    parentTaskId?: number;
}

interface ProjectItem {
    id: number;

    name: string;

    description: string;

    tasks: Task[];

    members: MemberItem[];
}

interface MemberItem {
    id: number;
    name: string;
    email: string;
}

export default class Project extends Controller {
    private readonly STATE_EMPTY = "state-empty";

    private readonly STATE_CREATE_PROJECT = "state-create-project";

    private readonly STATE_PROJECT = "state-project";

    private readonly STATE_PROJECT_DETAIL = "state-project-detail";

    private projects: ProjectItem[] = [];

    private currentProject = -1;

    private editing = false;

    private editingParentTaskId: number | null = null;

    private members: MemberItem[] = [];

    private projectDetailEditing = false;

    protected initialize(): void {
        queueMicrotask(() => {
            this.loadProjects();

            this.registerEvents();

            this.renderProjects();

            this.restoreSelectedProject();
        });
    }

    private restoreSelectedProject(): void {
        const storedProjectId = sessionStorage.getItem(
            "synctrack.selectedProjectId",
        );
        const projectId =
            storedProjectId == null ? NaN : Number(storedProjectId);
        const projectIndex = this.projects.findIndex(
            (project) => project.id === projectId,
        );

        if (projectIndex >= 0) {
            this.selectProject(projectIndex);

            return;
        }

        sessionStorage.removeItem("synctrack.selectedProjectId");
        this.showState(this.STATE_EMPTY);
    }

    private loadProjects(): void {
        const rawProjects = this.rootElement.dataset.projects;

        if (!rawProjects) {
            return;
        }

        const projects = JSON.parse(rawProjects) as Array<{
            id: number;
            title: string;
            description: string | null;
            tasks?: Array<Record<string, unknown>>;
            members?: Array<{
                id: number;
                username: string;
                email: string;
            }>;
        }>;

        this.projects = projects.map((project) => {
            const members = (project.members ?? []).map((member) => ({
                id: member.id,
                name: member.username,
                email: member.email,
            }));

            return {
                id: project.id,
                name: project.title,
                description: project.description ?? "",
                members,
                tasks: (project.tasks ?? []).map((task) =>
                    this.mapStoredTask(task, undefined, members),
                ),
            };
        });
    }

    private mapStoredTask(
        task: Record<string, unknown>,
        parentTaskId?: number,
        members: MemberItem[] = [],
    ): Task {
        const id = Number(task.id);
        const storedSubtasks = (task.sub_tasks ?? task.subTasks ?? []) as Array<
            Record<string, unknown>
        >;
        const assigneeId =
            task.assignee_id == null ? "" : String(task.assignee_id);
        const assignee = members.find(
            (member) => member.id === Number(assigneeId),
        );

        return {
            id,
            name: String(task.title ?? ""),
            assigned: assignee?.name ?? "",
            assigneeId,
            dueDate: task.due_date ? String(task.due_date).slice(0, 10) : "",
            priority: this.fromStoredPriority(task.priority),
            status: this.fromStoredStatus(task.status),
            subtasks: storedSubtasks.map((subtask) =>
                this.mapStoredTask(subtask, id, members),
            ),
            expanded: false,
            parentTaskId,
        };
    }

    private fromStoredPriority(priority: unknown): TaskPriority {
        const priorities: Record<string, TaskPriority> = {
            low: "Low",
            medium: "Medium",
            high: "High",
        };

        return priorities[String(priority ?? "")] ?? "";
    }

    private fromStoredStatus(status: unknown): TaskStatus {
        const statuses: Record<string, TaskStatus> = {
            draft: "assigned",
            in_progress: "in-progress",
            completed: "done",
        };

        return statuses[String(status ?? "")] ?? "assigned";
    }

    private async submitRequest(
        action: string,
        method: "POST" | "PUT" | "DELETE",
        fields: Record<string, string>,
    ): Promise<void> {
        const token =
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? "";

        if (this.currentProject >= 0) {
            sessionStorage.setItem(
                "synctrack.selectedProjectId",
                this.projects[this.currentProject].id.toString(),
            );
        }

        const body = new URLSearchParams({
            ...fields,
            _token: token,
            ...(method === "POST" ? {} : { _method: method }),
        });

        try {
            const response = await fetch(action, {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded;charset=UTF-8",
                    "X-CSRF-TOKEN": token,
                },
                body,
            });

            if (!response.ok) {
                this.showToast(false);
                return;
            }

            const html = await response.text();
            const documentResult = new DOMParser().parseFromString(
                html,
                "text/html",
            );
            const projectWrapper =
                documentResult.querySelector<HTMLElement>("#project-wrapper");
            const projects = projectWrapper?.dataset.projects;

            if (projects) {
                this.rootElement.dataset.projects = projects;
                this.loadProjects();
                this.renderProjects();
                this.restoreSelectedProject();
            }

            this.showToast(true);
        } catch (error) {
            console.error(error);
            this.showToast(false);
        }
    }

    private taskFields(task: Task): Record<string, string> {
        const statuses: Record<TaskStatus, string> = {
            assigned: "draft",
            "in-progress": "in_progress",
            done: "completed",
        };

        return {
            assignee_id: task.assigneeId,
            title: task.name,
            due_date: task.dueDate,
            priority: task.priority.toLowerCase(),
            status: statuses[task.status],
        };
    }

    private async sendRequest(
        action: string,
        method: "POST" | "PUT" | "DELETE",
        fields: Record<string, string>,
    ): Promise<boolean> {
        const token =
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? "";
        const body = new URLSearchParams({
            ...fields,
            _token: token,
            ...(method === "POST" ? {} : { _method: method }),
        });

        try {
            const response = await fetch(action, {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded;charset=UTF-8",
                    "X-CSRF-TOKEN": token,
                },
                body,
            });

            this.showToast(response.ok);
            return response.ok;
        } catch (error) {
            console.error(error);
            this.showToast(false);
            return false;
        }
    }

    private persistTask(task: Task): void {
        const endpoint = task.parentTaskId == null ? "task" : "subtask";
        void this.sendRequest(
            `/${endpoint}/${task.id}`,
            "PUT",
            this.taskFields(task),
        );
    }

    private registerEvents(): void {
        this.querySelector<HTMLButtonElement>(
            "#create-project-btn",
        )?.addEventListener("click", () => {
            this.showState(this.STATE_CREATE_PROJECT);
        });

        this.querySelector<HTMLButtonElement>(
            "#cancel-project-btn",
        )?.addEventListener("click", () => {
            this.clearProjectForm();

            if (this.projects.length === 0) {
                this.showState(this.STATE_EMPTY);
            } else {
                this.showState(this.STATE_PROJECT);
            }
        });

        this.querySelector<HTMLButtonElement>(
            "#save-project-btn",
        )?.addEventListener("click", () => {
            this.createProject();
        });

        this.querySelector<HTMLButtonElement>(
            ".task-settings-button",
        )?.addEventListener("click", () => this.openProjectDetail());

        this.querySelector<HTMLButtonElement>(
            "#back-to-tasks-btn",
        )?.addEventListener("click", () => this.showState(this.STATE_PROJECT));

        this.querySelector<HTMLButtonElement>(
            "#edit-project-btn",
        )?.addEventListener("click", () => this.setProjectDetailEditing(true));

        this.querySelector<HTMLButtonElement>(
            "#cancel-project-edit-btn",
        )?.addEventListener("click", () => this.cancelProjectDetailEdit());

        this.querySelector<HTMLButtonElement>(
            "#save-project-edit-btn",
        )?.addEventListener("click", () => this.saveProjectDetail());

        this.querySelector<HTMLButtonElement>(
            "#delete-project-btn",
        )?.addEventListener("click", () => this.openDeleteProjectModal());

        document.addEventListener("click", (event) => {
            const target = event.target as HTMLElement;

            if (
                this.editing &&
                !target.closest(".task-row.editing") &&
                !target.closest(".member-modal-backdrop")
            ) {
                const taskName =
                    this.querySelector<HTMLInputElement>(
                        "#editing-task-name",
                    )?.value.trim() ?? "";
                const clickedAction =
                    target.closest("button, a, input, select, textarea") !==
                    null;

                if (!clickedAction && taskName) {
                    this.saveTask();
                } else {
                    this.cancelTask();
                }
            }

            const project = target.closest(".project-item");

            if (project) {
                this.selectProject(
                    Number((project as HTMLElement).dataset.index),
                );

                return;
            }

            if (target.closest("#create-task-btn")) {
                this.addTaskRow();

                return;
            }

            const memberPicker =
                target.closest<HTMLButtonElement>(".member-picker");

            if (memberPicker) {
                this.openMemberModal(memberPicker);

                return;
            }

            const subtaskButton = target.closest<HTMLButtonElement>(
                ".create-subtask-btn",
            );

            if (subtaskButton) {
                this.addTaskRow(Number(subtaskButton.dataset.parentTaskId));

                return;
            }

            const addSubtaskButton =
                target.closest<HTMLButtonElement>(".task-add-subtask");

            if (addSubtaskButton) {
                this.addTaskRow(Number(addSubtaskButton.dataset.parentTaskId));

                return;
            }

            const expandButton =
                target.closest<HTMLButtonElement>(".task-expand");

            if (expandButton) {
                this.toggleTaskExpanded(Number(expandButton.dataset.taskId));

                return;
            }

            if (target.closest(".task-save")) {
                this.saveTask();

                return;
            }

            if (target.closest(".task-cancel")) {
                this.cancelTask();

                return;
            }

            const statusButton =
                target.closest<HTMLButtonElement>(".task-status");

            if (statusButton) {
                this.advanceTaskStatus(
                    Number(statusButton.dataset.taskId),
                    statusButton.closest(".subtask-row") !== null,
                );

                return;
            }

            const deleteButton =
                target.closest<HTMLButtonElement>(".task-delete");

            if (deleteButton) {
                this.deleteTask(
                    Number(deleteButton.dataset.taskId),
                    deleteButton.closest(".subtask-row") !== null,
                );

                return;
            }
        });

        document.addEventListener("change", (event) => {
            const target = event.target as HTMLInputElement | HTMLSelectElement;

            if (target.classList.contains("task-name-input")) {
                this.updateTaskField(
                    Number(target.dataset.taskId),
                    "name",
                    target.value,
                    target.closest(".subtask-row") !== null,
                );
                return;
            }

            if (target.classList.contains("task-date-input")) {
                target.classList.toggle("has-value", Boolean(target.value));
                this.updateTaskField(
                    Number(target.dataset.taskId),
                    "dueDate",
                    target.value,
                    target.closest(".subtask-row") !== null,
                );
                return;
            }

            if (
                target instanceof HTMLSelectElement &&
                target.classList.contains("task-priority-select")
            ) {
                target.classList.remove("default", "low", "medium", "high");
                target.classList.add(
                    target.value ? target.value.toLowerCase() : "default",
                );

                this.updateTaskPriority(
                    Number(target.dataset.taskId),
                    target.value as Task["priority"],
                    target.closest(".subtask-row") !== null,
                );

                return;
            }
        });
    }

    private validateProject(): boolean {
        const name = this.querySelector<HTMLInputElement>("#project-name");

        const description = this.querySelector<HTMLTextAreaElement>(
            "#project-description",
        );

        return (
            !!name &&
            !!description &&
            name.value.trim() !== "" &&
            description.value.trim() !== ""
        );
    }

    private createProject(): void {
        if (!this.validateProject()) {
            this.showToast(false);

            return;
        }

        const name = this.querySelector<HTMLInputElement>("#project-name")!;
        const description = this.querySelector<HTMLTextAreaElement>(
            "#project-description",
        )!;

        void this.submitRequest("/project", "POST", {
            title: name.value.trim(),
            description: description.value.trim(),
        });

        this.clearProjectForm();
        this.showState(this.STATE_EMPTY);
    }

    private renderProjects(): void {
        const list = this.querySelector<HTMLElement>("#project-item-list");

        const empty = this.querySelector<HTMLElement>("#project-empty-list");

        const count = this.querySelector<HTMLElement>(".project-count");

        if (!list || !empty || !count) {
            return;
        }

        list.innerHTML = "";

        count.textContent = this.projects.length.toString();

        if (this.projects.length === 0) {
            empty.classList.add("active");

            list.classList.remove("active");

            return;
        }

        empty.classList.remove("active");

        list.classList.add("active");

        this.projects.forEach((project, index) => {
            const button = document.createElement("button");

            button.type = "button";

            button.className = "project-item";

            button.dataset.index = index.toString();

            if (index === this.currentProject) {
                button.classList.add("active");
            }

            button.innerHTML = `<span class="project-name body-s">${project.name}</span>`;

            list.appendChild(button);
        });
    }

    private selectProject(index: number): void {
        this.editing = false;

        this.editingParentTaskId = null;

        this.currentProject = index;

        this.members = this.projects[index]?.members ?? [];

        this.renderProjects();

        this.renderTasks();

        this.showState(this.STATE_PROJECT);
    }

    private openProjectDetail(): void {
        if (this.currentProject === -1) {
            return;
        }

        this.projectDetailEditing = false;

        this.populateProjectDetail();

        this.setProjectDetailEditing(false);

        this.showState(this.STATE_PROJECT_DETAIL);
    }

    private populateProjectDetail(): void {
        const project = this.projects[this.currentProject];

        const name = this.querySelector<HTMLInputElement>(
            "#detail-project-name",
        );

        const description = this.querySelector<HTMLTextAreaElement>(
            "#detail-project-description",
        );

        const list = this.querySelector<HTMLElement>("#detail-member-list");

        if (!project || !name || !description || !list) {
            return;
        }

        name.value = project.name;

        description.value = project.description;

        list.innerHTML = this.members.length
            ? this.members
                  .map(
                      (member, index) => `
                <div class="detail-member-row body-s">
                    <span>${index + 1}</span><span>${member.name}</span><span>${member.email}</span>
                </div>
            `,
                  )
                  .join("")
            : `<div class="detail-member-empty helper-text">No members have been added.</div>`;
    }

    private setProjectDetailEditing(editing: boolean): void {
        this.projectDetailEditing = editing;

        const name = this.querySelector<HTMLInputElement>(
            "#detail-project-name",
        );

        const description = this.querySelector<HTMLTextAreaElement>(
            "#detail-project-description",
        );

        const detail = this.querySelector<HTMLElement>("#state-project-detail");

        name && (name.disabled = !editing);

        description && (description.disabled = !editing);

        detail?.classList.toggle("is-editing", editing);

        if (detail?.classList.contains("active")) {
            this.showState(this.STATE_PROJECT_DETAIL);
        }

        if (editing) {
            name?.focus();
        }
    }

    private cancelProjectDetailEdit(): void {
        this.populateProjectDetail();

        this.setProjectDetailEditing(false);

        this.showState(this.STATE_PROJECT_DETAIL);
    }

    private saveProjectDetail(): void {
        const project = this.projects[this.currentProject];

        const name = this.querySelector<HTMLInputElement>(
            "#detail-project-name",
        );

        const description = this.querySelector<HTMLTextAreaElement>(
            "#detail-project-description",
        );

        if (
            !project ||
            !name ||
            !description ||
            !name.value.trim() ||
            !description.value.trim()
        ) {
            this.showToast(false);

            return;
        }

        project.name = name.value.trim();

        project.description = description.value.trim();

        void this.sendRequest(`/project/${project.id}`, "PUT", {
            title: project.name,
            description: project.description,
        });

        this.renderProjects();

        this.renderTasks();

        this.populateProjectDetail();

        this.setProjectDetailEditing(false);

        this.showState(this.STATE_PROJECT_DETAIL);

        this.showToast(true);
    }

    private deleteCurrentProject(): void {
        if (this.currentProject === -1) {
            return;
        }

        const project = this.projects[this.currentProject];

        sessionStorage.removeItem("synctrack.selectedProjectId");

        this.currentProject = -1;

        this.submitRequest(`/project/${project.id}`, "DELETE", {});
    }

    private openDeleteProjectModal(): void {
        if (this.currentProject === -1) {
            return;
        }

        const modal = document.createElement("div");

        modal.className = "delete-project-backdrop";

        modal.innerHTML = `
            <div class="delete-project-modal" role="dialog" aria-modal="true" aria-labelledby="delete-project-title">
                <div class="delete-project-modal-header">
                    <h2 id="delete-project-title">Delete Project</h2>
                    <button type="button" class="delete-project-close" aria-label="Close">&times;</button>
                </div>
                <div class="delete-project-modal-content">
                    <p>Are you sure you want to delete this project?</p>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="delete-project-modal-footer">
                    <button type="button" class="delete-project-cancel">No</button>
                    <button type="button" class="delete-project-confirm">Yes</button>
                </div>
            </div>
        `;

        modal.addEventListener("click", (event) => {
            const target = event.target as HTMLElement;

            if (
                target === modal ||
                target.closest(".delete-project-close") ||
                target.closest(".delete-project-cancel")
            ) {
                modal.remove();

                return;
            }

            if (target.closest(".delete-project-confirm")) {
                modal.remove();

                this.deleteCurrentProject();
            }
        });

        document.body.appendChild(modal);
    }
    /* =====================================================
   TASKS
====================================================== */

    private renderTasks(): void {
        const taskList = this.querySelector<HTMLElement>("#task-list");

        const title = this.querySelector<HTMLElement>("#task-project-title");

        if (!taskList || !title) {
            return;
        }

        if (this.currentProject === -1) {
            title.textContent = "Select a Project";

            taskList.innerHTML = `
        <div class="task-empty">
            Select a project from the left to view tasks.
        </div>
    `;

            return;
        }

        const project = this.projects[this.currentProject];

        title.textContent = project.name;

        taskList.innerHTML = "";

        project.tasks.forEach((task) => {
            const row = document.createElement("div");

            row.className = "task-row";

            row.innerHTML = `

                <div class="status-column">

                    ○

                </div>

                <div class="task-name-column body-s">

                    ${task.name}

                </div>

                <div class="assigned-column helper-text">

                    <button
                        type="button"
                        class="member-picker"
                        data-task-id="${task.id}"
                        data-member-id="${task.assigneeId}"
                        data-value="${task.assigned}">

                        ${
                            task.subtasks.length > 0
                                ? "..."
                                : task.assigned || "Member"
                        }

                    </button>

                </div>

                <div class="date-column helper-text">
                    ${this.renderEditableDate(task)}
                </div>

                <div class="priority-column">

                    ${this.renderPrioritySelect(task)}

                </div>

                <div class="action-column">

                    ⋯

                </div>

            `;

            row.querySelector<HTMLElement>(".status-column")!.innerHTML =
                task.subtasks.length > 0
                    ? `
                    <button
                        type="button"
                        class="task-expand ${task.expanded ? "expanded" : ""}"
                        data-task-id="${task.id}"
                        aria-label="${task.expanded ? "Collapse" : "Expand"} ${task.name}">
                        &#8250;
                    </button>
                `
                    : `
                    <button
                        type="button"
                        class="task-status ${task.status}"
                        data-task-id="${task.id}"
                        aria-label="${task.name}: ${this.getStatusLabel(task.status)}"
                        >
                        ${this.getStatusIcon(task.status)}
                    </button>
                `;

            row.querySelector<HTMLElement>(".action-column")!.innerHTML = `
            <button
                type="button"
                class="task-delete"
                data-task-id="${task.id}"
                aria-label="Delete ${task.name}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 6h18M9 6V4h6v2m-8 0 1 14h8l1-14M10 10v6m4-6v6" />
                </svg>
            </button>
        `;

            const completedSubtasks = task.subtasks.filter(
                (subtask) => subtask.status === "done",
            ).length;

            row.querySelector<HTMLElement>(".task-name-column")!.innerHTML = `
            ${this.renderEditableName(task)}
            ${
                task.subtasks.length > 0 &&
                !(this.editing && this.editingParentTaskId === task.id)
                    ? `<span class="subtask-count">${completedSubtasks}/${task.subtasks.length}</span>`
                    : ""
            }
        `;

            taskList.appendChild(row);

            const createSubtask = document.createElement("button");

            createSubtask.type = "button";

            createSubtask.className = "create-subtask-btn hover-subtask-btn";

            createSubtask.dataset.parentTaskId = task.id.toString();

            createSubtask.innerHTML =
                "<span>+</span><span>Create SubTask</span>";

            if (task.expanded) {
                task.subtasks.forEach((subtask) => {
                    this.renderSubtaskRow(taskList, subtask);
                });

                if (this.editing && this.editingParentTaskId === task.id) {
                    this.renderEditingRow(taskList);
                }
            }

            taskList.appendChild(createSubtask);
        });

        if (this.editing && this.editingParentTaskId === null) {
            this.renderEditingRow(taskList);
        }
    }

    private renderSubtaskRow(taskList: HTMLElement, task: Task): void {
        const row = document.createElement("div");

        row.className = "task-row subtask-row";

        row.innerHTML = `
            <div class="status-column">
                <button
                    type="button"
                    class="task-status ${task.status}"
                    data-task-id="${task.id}"
                    aria-label="${task.name}: ${this.getStatusLabel(task.status)}"
                    >
                    ${this.getStatusIcon(task.status)}
                </button>
            </div>
            <div class="task-name-column body-s">${this.renderEditableName(task)}</div>
            <div class="assigned-column helper-text">
                <button type="button" class="member-picker" data-task-id="${task.id}" data-member-id="${task.assigneeId}" data-value="${task.assigned}">${task.assigned || "Member"}</button>
            </div>
            <div class="date-column helper-text">${this.renderEditableDate(task)}</div>
            <div class="priority-column">
                ${this.renderPrioritySelect(task)}
            </div>
            <div class="action-column">
                <button type="button" class="task-delete" data-task-id="${task.id}" aria-label="Delete ${task.name}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2m-8 0 1 14h8l1-14M10 10v6m4-6v6" /></svg>
                </button>
            </div>
        `;

        taskList.appendChild(row);
    }

    private getStatusLabel(status: TaskStatus): string {
        return {
            assigned: "Assigned",

            "in-progress": "In Progress",

            done: "Done",
        }[status];
    }

    private getStatusIcon(status: TaskStatus): string {
        if (status === "assigned") {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 3h10M7 21h10M8 3c0 4 2 5 4 6-2 1-4 2-4 6M16 3c0 4-2 5-4 6 2 1 4 2 4 6" />
                </svg>
            `;
        }

        if (status === "done") {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m6 12 4 4 8-8" />
                </svg>
            `;
        }

        return "";
    }

    private findTask(taskId: number, isSubtask = false): Task | undefined {
        const tasks = this.projects[this.currentProject]?.tasks ?? [];

        return isSubtask
            ? tasks
                  .flatMap((task) => task.subtasks)
                  .find((task) => task.id === taskId)
            : tasks.find((task) => task.id === taskId);
    }

    private toggleTaskExpanded(taskId: number): void {
        const task = this.projects[this.currentProject]?.tasks.find(
            (item) => item.id === taskId,
        );

        if (!task) {
            return;
        }

        task.expanded = !task.expanded;

        this.renderTasks();
    }

    private advanceTaskStatus(taskId: number, isSubtask: boolean): void {
        const task = this.findTask(taskId, isSubtask);

        if (!task) {
            return;
        }

        const nextStatus: Record<TaskStatus, TaskStatus> = {
            assigned: "in-progress",

            "in-progress": "done",

            done: "assigned",
        };

        task.status = nextStatus[task.status];

        this.renderTasks();

        this.persistTask(task);
    }

    private deleteTask(taskId: number, isSubtask: boolean): void {
        if (this.currentProject === -1) {
            return;
        }

        this.submitRequest(
            isSubtask ? `/subtask/${taskId}` : `/task/${taskId}`,
            "DELETE",
            {},
        );
    }

    private updateTaskPriority(
        taskId: number,
        priority: Task["priority"],
        isSubtask: boolean,
    ): void {
        const task = this.findTask(taskId, isSubtask);

        if (!task || !["", "Low", "Medium", "High"].includes(priority)) {
            return;
        }

        task.priority = priority;

        this.persistTask(task);
    }

    private updateTaskField(
        taskId: number,
        field: "name" | "dueDate",
        value: string,
        isSubtask: boolean,
    ): void {
        const task = this.findTask(taskId, isSubtask);

        if (!task) {
            return;
        }

        const nextValue = field === "name" ? value.trim() : value;

        if (!nextValue) {
            this.renderTasks();
            return;
        }

        task[field] = nextValue;

        this.persistTask(task);
    }

    private renderEditableName(task: Task): string {
        return `<input class="task-inline-input task-name-input" type="text" maxlength="100" value="${this.escapeAttribute(task.name)}" placeholder="Task Name" data-task-id="${task.id}" aria-label="Edit task name">`;
    }

    private renderEditableDate(task: Task): string {
        return `<input class="task-inline-input task-date-input${task.dueDate ? " has-value" : ""}" type="date" value="${this.escapeAttribute(task.dueDate)}" data-task-id="${task.id}" aria-label="Edit due date for ${this.escapeAttribute(task.name)}">`;
    }

    private escapeAttribute(value: string): string {
        return value
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    private renderPrioritySelect(task: Task): string {
        return `
            <select
                class="task-priority-select ${task.priority ? task.priority.toLowerCase() : "default"}"
                data-task-id="${task.id}"
                aria-label="Priority for ${task.name}">
                <option value="" ${task.priority === "" ? "selected" : ""}>Priority</option>
                ${["Low", "Medium", "High"]
                    .map(
                        (priority) => `
                    <option value="${priority}" ${task.priority === priority ? "selected" : ""}>
                        ${priority}
                    </option>
                `,
                    )
                    .join("")}
            </select>
        `;
    }

    /* =====================================================
       CREATE TASK ROW
    ====================================================== */

    private addTaskRow(parentTaskId: number | null = null): void {
        if (this.currentProject === -1) {
            return;
        }

        if (this.editing) {
            this.cancelTask();
        }

        if (parentTaskId !== null) {
            const parentTask = this.projects[this.currentProject].tasks.find(
                (task) => task.id === parentTaskId,
            );

            if (!parentTask) {
                return;
            }

            parentTask.expanded = true;
        }

        this.editing = true;

        this.editingParentTaskId = parentTaskId;

        this.renderTasks();

        this.querySelector<HTMLInputElement>("#editing-task-name")?.focus();
    }

    private openMemberModal(
        memberPicker: HTMLButtonElement,
        applySelectionImmediately = false,
    ): void {
        const modal = document.createElement("div");

        const currentMember = memberPicker.dataset.value ?? "";

        const taskId = Number(memberPicker.dataset.taskId);

        const isUpdate = Boolean(taskId && currentMember);

        modal.className = "member-modal-backdrop";

        modal.innerHTML = `
            <div class="member-modal" role="dialog" aria-modal="true" aria-label="${isUpdate ? "Update Project Member" : "Assign Member"}">
                <div class="member-modal-header"><span>${isUpdate ? "Update Project Member" : "Assign Member"}</span><button type="button" data-close>&times;</button></div>
                ${isUpdate ? `<div class="member-current"><span>Current Member</span><strong>${currentMember}</strong></div>` : ""}
                <div class="member-modal-table member-modal-head"><span>No</span><span>Member Name</span><span>Email</span><span>Action</span></div>
                <button type="button" class="member-option" data-member="" data-member-id="0">
                    <span>0</span><span>No member</span><span>-</span><span></span>
                </button>
                ${this.members
                    .map(
                        (member, index) => `
                        <div class="member-row">
                            <button type="button" class="member-option" data-member="${member.name}" data-member-id="${member.id}">
                                <span>${index + 1}</span><span>${member.name}</span><span>${member.email}</span><span></span>
                            </button>
                            <button type="button" class="member-delete" data-member="${member.name}" aria-label="Remove ${member.name}">&#128465;</button>
                        </div>
                    `,
                    )
                    .join("")}
                <button type="button" class="member-modal-invite">+ <span>Invite Other Member</span></button>
                <p class="member-selection-error" role="alert">Please select a new member.</p>
                <div class="member-modal-footer"><button type="button" data-close>Cancel</button><button type="button" class="member-confirm">Confirm</button></div>
            </div>
        `;

        let selectedMember = "";
        let selectedMemberId = 0;
        let selectionMade = false;

        modal.addEventListener("click", (event) => {
            const target = event.target as HTMLElement;

            const memberDelete =
                target.closest<HTMLButtonElement>(".member-delete");

            if (memberDelete) {
                modal.remove();

                this.openMemberDeleteConfirmation(
                    memberPicker,
                    memberDelete.dataset.member ?? "",
                );

                return;
            }

            const option = target.closest<HTMLButtonElement>(".member-option");

            if (option) {
                selectedMember = option.dataset.member ?? "";
                selectedMemberId = Number(option.dataset.memberId);
                selectionMade = true;

                modal
                    .querySelector(".member-selection-error")
                    ?.classList.remove("show");

                modal
                    .querySelectorAll(".member-option")
                    .forEach((item) =>
                        item.classList.toggle("selected", item === option),
                    );

                if (applySelectionImmediately && selectionMade) {
                    this.applyMemberSelection(memberPicker, selectedMemberId);

                    modal.remove();
                }

                return;
            }

            if (target.closest(".member-modal-invite")) {
                this.openInviteMemberModal(modal);

                return;
            }

            if (target.closest(".member-confirm")) {
                if (
                    !selectionMade ||
                    (isUpdate && selectedMember === currentMember)
                ) {
                    modal
                        .querySelector(".member-selection-error")
                        ?.classList.add("show");

                    return;
                }

                this.applyMemberSelection(memberPicker, selectedMemberId);

                modal.remove();
            }

            if (target.closest("[data-close]") || target === modal) {
                modal.remove();
            }
        });

        document.body.appendChild(modal);
    }

    private applyMemberSelection(
        memberPicker: HTMLButtonElement,
        memberId: number,
    ): void {
        const member = this.members.find((item) => item.id === memberId);
        const memberName = member?.name ?? "";

        memberPicker.dataset.value = memberName;
        memberPicker.dataset.memberId = member?.id.toString() ?? "";

        memberPicker.textContent =
            memberName || (memberPicker.dataset.taskId ? "..." : "Member");

        const taskId = Number(memberPicker.dataset.taskId);

        if (!taskId) {
            return;
        }

        const task = this.findTask(
            taskId,
            memberPicker.closest(".subtask-row") !== null,
        );

        if (!task) {
            return;
        }

        task.assigned = memberName;
        task.assigneeId = member?.id.toString() ?? "";

        this.renderTasks();

        this.persistTask(task);
    }

    private openMemberDeleteConfirmation(
        memberPicker: HTMLButtonElement,
        memberName: string,
    ): void {
        const modal = document.createElement("div");

        modal.className = "member-modal-backdrop";

        modal.innerHTML = `
            <div class="member-delete-modal" role="dialog" aria-modal="true" aria-labelledby="member-delete-title">
                <div class="member-modal-header">
                    <span id="member-delete-title">Delete Member</span>
                    <button type="button" data-close aria-label="Close">&times;</button>
                </div>
                <div class="member-delete-content">
                    <p>Are you sure you want to delete <strong>${memberName}</strong>?</p>
                    <p>This member will be removed from the project.</p>
                </div>
                <div class="member-modal-footer">
                    <button type="button" data-close>Cancel</button>
                    <button type="button" class="member-delete-confirm">Yes</button>
                </div>
            </div>
        `;

        modal.addEventListener("click", (event) => {
            const target = event.target as HTMLElement;

            if (target === modal || target.closest("[data-close]")) {
                modal.remove();

                this.openMemberModal(memberPicker);

                return;
            }

            if (target.closest(".member-delete-confirm")) {
                this.members = this.members.filter(
                    (member) => member.name !== memberName,
                );

                memberPicker.dataset.value = "";

                memberPicker.textContent = memberPicker.dataset.taskId
                    ? "..."
                    : "Member";

                this.clearDeletedMemberAssignments(memberName);

                modal.remove();

                this.openMemberModal(memberPicker, true);
            }
        });

        document.body.appendChild(modal);
    }

    private clearDeletedMemberAssignments(memberName: string): void {
        this.projects.forEach((project) => {
            project.tasks.forEach((task) => {
                if (task.assigned === memberName) {
                    task.assigned = "";
                }

                task.subtasks.forEach((subtask) => {
                    if (subtask.assigned === memberName) {
                        subtask.assigned = "";
                    }
                });
            });
        });

        this.renderTasks();
    }

    private openInviteMemberModal(assignModal: HTMLElement): void {
        const modal = document.createElement("div");

        modal.className = "member-modal-backdrop";

        modal.innerHTML = `
            <div class="invite-member-modal" role="dialog" aria-modal="true" aria-label="Invite Member">
                <div class="member-modal-header"><span>Invite Member</span><button type="button" data-close>&times;</button></div>
                <div class="invite-member-content">
                    <p>Please enter the email of member you want to invite</p>
                    <input type="email" id="invite-member-email" placeholder="you@example.com">
                </div>
                <div class="member-modal-footer"><button type="button" data-close>Cancel</button><button type="button" class="invite-confirm">Confirm</button></div>
            </div>
        `;

        modal.addEventListener("click", (event) => {
            const target = event.target as HTMLElement;

            if (target.closest("[data-close]")) {
                modal.remove();

                return;
            }

            if (target.closest(".invite-confirm")) {
                const email = modal
                    .querySelector<HTMLInputElement>("#invite-member-email")
                    ?.value.trim();

                if (!email) {
                    return;
                }

                modal.remove();

                assignModal.remove();

                this.submitRequest("/member", "POST", {
                    email,
                    project_id:
                        this.projects[this.currentProject].id.toString(),
                });
            }
        });

        document.body.appendChild(modal);
    }

    /* =====================================================
       EDITING ROW
    ====================================================== */

    private renderEditingRow(taskList: HTMLElement): void {
        const row = document.createElement("div");

        row.className = "task-row editing";

        row.innerHTML = `

            <div class="status-column">

                ○

            </div>

            <div class="task-name-column">

                <input
                    id="editing-task-name"
                    type="text"
                    placeholder="Task Name">

            </div>

            <div class="assigned-column">

                <button
                    type="button"
                    id="editing-member"
                    class="member-picker"
                    data-value="">

                    Member

                </button>

            </div>

            <div class="date-column">

                <input
                    id="editing-date"
                    type="date">

            </div>

            <div class="priority-column">

                <select
                    id="editing-priority">

                    <option value="">

                        Priority

                    </option>

                    <option>

                        Low

                    </option>

                    <option>

                        Medium

                    </option>

                    <option>

                        High

                    </option>

                </select>

            </div>

            <div class="action-column">

                <button
                    type="button"
                    class="task-save">

                    ✔

                </button>

                <button
                    type="button"
                    class="task-cancel">

                    ✕

                </button>

            </div>

        `;

        row.querySelector<HTMLElement>(".status-column")!.innerHTML = `
        <span class="task-status assigned" aria-label="Assigned">
            ${this.getStatusIcon("assigned")}
        </span>
    `;

        taskList.appendChild(row);
    }

    private hasDraftTaskValue(): boolean {
        const name = this.querySelector<HTMLInputElement>("#editing-task-name");
        const member = this.querySelector<HTMLElement>("#editing-member");
        const date = this.querySelector<HTMLInputElement>("#editing-date");
        const priority =
            this.querySelector<HTMLSelectElement>("#editing-priority");

        return Boolean(
            name?.value.trim() ||
            member?.dataset.value ||
            date?.value ||
            priority?.value,
        );
    }

    /* =====================================================
       SAVE TASK
    ====================================================== */

    private saveTask(): void {
        if (this.currentProject === -1) {
            return;
        }

        const name = this.querySelector<HTMLInputElement>("#editing-task-name");

        const member = this.querySelector<HTMLElement>("#editing-member");

        const date = this.querySelector<HTMLInputElement>("#editing-date");

        const priority =
            this.querySelector<HTMLSelectElement>("#editing-priority");

        if (!name || !member || !date || !priority) {
            return;
        }

        if (!this.hasDraftTaskValue()) {
            this.showToast(false);

            return;
        }

        const parentTask =
            this.editingParentTaskId === null
                ? undefined
                : this.findTask(this.editingParentTaskId);

        if (!name.value.trim()) {
            this.showToast(false);

            return;
        }

        const fields = {
            assignee_id: member.dataset.memberId ?? "",
            title: name.value.trim(),
            due_date: date.value,
            priority: priority.value.toLowerCase(),
            status: "draft",
        };

        if (parentTask) {
            this.submitRequest("/subtask", "POST", {
                ...fields,
                task_id: parentTask.id.toString(),
            });
        } else {
            this.submitRequest("/task", "POST", {
                ...fields,
                project_id: this.projects[this.currentProject].id.toString(),
            });
        }
    }

    /* =====================================================
       CANCEL TASK
    ====================================================== */

    private cancelTask(): void {
        this.editing = false;

        this.editingParentTaskId = null;

        this.renderTasks();
    }
    /* =====================================================
   CLEAR FORM
====================================================== */

    private clearProjectForm(): void {
        const name = this.querySelector<HTMLInputElement>("#project-name");

        const description = this.querySelector<HTMLTextAreaElement>(
            "#project-description",
        );

        if (name) {
            name.value = "";
        }

        if (description) {
            description.value = "";
        }
    }

    /* =====================================================
       SHOW PAGE
    ====================================================== */

    private showState(state: string): void {
        this.querySelectorAll<HTMLElement>(".content-state").forEach(
            (element) => {
                element.classList.remove("active");
            },
        );

        this.querySelectorAll<HTMLElement>(".header-actions").forEach(
            (element) => {
                element.classList.remove("active");
            },
        );

        switch (state) {
            case this.STATE_EMPTY:
                this.querySelector("#state-empty")?.classList.add("active");

                this.querySelector("#header-default-actions")?.classList.add(
                    "active",
                );

                break;

            case this.STATE_CREATE_PROJECT:
                this.querySelector("#state-create-project")?.classList.add(
                    "active",
                );

                this.querySelector("#header-create-actions")?.classList.add(
                    "active",
                );

                break;

            case this.STATE_PROJECT:
                this.querySelector("#state-project")?.classList.add("active");

                this.querySelector("#header-default-actions")?.classList.add(
                    "active",
                );

                break;

            case this.STATE_PROJECT_DETAIL:
                this.querySelector("#state-project-detail")?.classList.add(
                    "active",
                );

                this.querySelector(
                    this.projectDetailEditing
                        ? "#header-project-edit-actions"
                        : "#header-project-detail-actions",
                )?.classList.add("active");

                break;
        }
    }

    /* =====================================================
       TOAST
    ====================================================== */

    private showToast(success: boolean): void {
        const toast = document.querySelector<HTMLElement>(
            success ? "#toast-success" : "#toast-error",
        );

        if (!toast) {
            return;
        }

        toast.classList.add("show");

        window.setTimeout(() => {
            toast.classList.remove("show");
        }, 3000);
    }
}
