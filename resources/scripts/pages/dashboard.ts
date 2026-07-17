import Controller from "@/utils/controller";

type DashboardColumn = "in_progress" | "completed" | "overdue";
type EditableDashboardColumn = Exclude<DashboardColumn, "overdue">;

type DragState = {
  card: HTMLElement;
  taskId: number;
  originalStatus: DashboardColumn;
  originalTaskStatus: string;
  originalContainer: HTMLElement;
  originalNextSibling: ChildNode | null;
};

const editableColumns: EditableDashboardColumn[] = ["in_progress", "completed"];

export default class DashboardController extends Controller {
  private dragState: DragState | null = null;

  protected initialize(): void {
    this.bindTaskCards();
    this.bindColumnDropTargets();
    this.refreshAllColumns();
  }

  private bindTaskCards(): void {
    this.querySelectorAll<HTMLElement>(".task-card").forEach((card) => {
      card.addEventListener("dragstart", (event) => {
        const taskId = Number(card.dataset.taskId);
        const originalStatus = this.getCardStatus(card);
        const originalContainer = card.closest<HTMLElement>(".task-card-container");

        if (taskId <= 0 || originalStatus == null || originalContainer == null) {
          event.preventDefault();
          return;
        }

        this.dragState = {
          card,
          taskId,
          originalStatus,
          originalTaskStatus: card.dataset.taskStatus ?? "",
          originalContainer,
          originalNextSibling: card.nextSibling,
        };

        card.classList.add("is-dragging");
        event.dataTransfer?.setData("text/plain", String(taskId));

        if (event.dataTransfer != null) {
          event.dataTransfer.effectAllowed = "move";
        }
      });

      card.addEventListener("dragend", () => {
        card.classList.remove("is-dragging");
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

        await this.moveTaskCard(this.dragState, targetStatus, targetContainer);
      });
    });
  }

  private async moveTaskCard(
    dragState: DragState,
    targetStatus: EditableDashboardColumn,
    targetContainer: HTMLElement,
  ): Promise<void> {
    this.placeCard(dragState.card, targetContainer, targetStatus);

    try {
      const response = await fetch(this.getStatusUrl(dragState.taskId), {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
        },
        body: JSON.stringify({ column: targetStatus }),
      });

      if (!response.ok) {
        throw new Error("Unable to update task status.");
      }
    } catch (error) {
      this.restoreTaskPosition(dragState);
      console.error(error);
    } finally {
      this.refreshAllColumns();
    }
  }

  private placeCard(
    card: HTMLElement,
    targetContainer: HTMLElement,
    targetStatus: EditableDashboardColumn,
  ): void {
    targetContainer.appendChild(card);
    card.dataset.currentStatus = targetStatus;
    card.dataset.taskStatus = targetStatus;
    this.updateStatusIndicator(card, targetStatus);
    this.refreshAllColumns();
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

  private refreshColumn(status: DashboardColumn): void {
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

  private isValidStatusChange(
    originalStatus: DashboardColumn,
    targetStatus: DashboardColumn,
  ): targetStatus is EditableDashboardColumn {
    if (!editableColumns.includes(targetStatus as EditableDashboardColumn)) {
      return false;
    }

    if (originalStatus === targetStatus) {
      return false;
    }

    return originalStatus !== "overdue" || targetStatus === "completed";
  }

  private updateStatusIndicator(card: HTMLElement, status: DashboardColumn): void {
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

  private getColumnStatus(column: HTMLElement): DashboardColumn | null {
    return this.toDashboardColumn(column.dataset.column);
  }

  private getCardStatus(card: HTMLElement): DashboardColumn | null {
    return this.toDashboardColumn(card.dataset.currentStatus);
  }

  private getColumnContainer(status: DashboardColumn): HTMLElement | null {
    return this.querySelector<HTMLElement>(`[data-card-container="${status}"]`);
  }

  private toDashboardColumn(value: string | undefined): DashboardColumn | null {
    if (value === "in_progress" || value === "completed" || value === "overdue") {
      return value;
    }

    return null;
  }

  private clearAllDropStates(): void {
    this.querySelectorAll<HTMLElement>(".task-column").forEach((column) => {
      this.clearDropState(column);
    });
  }

  private clearDropState(column: HTMLElement): void {
    column.classList.remove("is-drag-over", "is-drop-blocked");
  }

  private getStatusUrl(taskId: number): string {
    const template = this.rootElement.dataset.statusUrlTemplate;
    return template?.replace("__TASK_ID__", String(taskId)) ?? `/task/${taskId}/status`;
  }

  private getCsrfToken(): string {
    return document.querySelector<HTMLMetaElement>("meta[name='csrf-token']")?.content ?? "";
  }
}
