import Controller from "@/utils/Controller";

type DateFilterMode = "month" | "week" | "today" | "range";

type MemberTaskItem = {
  task_id: number;
  task_type: "task" | "subtask";
  date: string;
};

type MemberTaskStat = {
  member_id: number | null;
  name: string;
  task_count: number;
  tasks: MemberTaskItem[];
};

type ProjectTaskStat = {
  project_id: string;
  project_name: string;
  total_tasks: number;
  completed_tasks: number;
  members: MemberTaskStat[];
};

type GraphMemberPoint = {
  name: string;
  task_count: number;
  x: number;
  y: number;
};

type DateRange = {
  start: Date;
  end: Date;
};

export default class DashboardController extends Controller {
  private projectStats: ProjectTaskStat[] = [];

  protected initialize(): void {
    this.projectStats = this.readProjectStats();
    this.initializeDateInputs();
    this.bindGraphControls();
    this.updateDateFilterFields();
    this.renderSelectedProjectGraph();
  }

  private initializeDateInputs(): void {
    const today = this.toDateInputValue(new Date());
    const startDate = this.querySelector<HTMLInputElement>("[data-start-date]");
    const endDate = this.querySelector<HTMLInputElement>("[data-end-date]");

    if (startDate && !startDate.value) {
      startDate.value = today;
    }
    if (endDate && !endDate.value) {
      endDate.value = today;
    }
  }

  private bindGraphControls(): void {
    this.querySelector<HTMLSelectElement>("[data-project-stat-select]")?.addEventListener("change", () => {
      this.renderSelectedProjectGraph();
    });
    this.querySelector<HTMLSelectElement>("[data-date-filter]")?.addEventListener("change", () => {
      this.updateDateFilterFields();
      this.renderSelectedProjectGraph();
    });
    this.querySelectorAll<HTMLInputElement>("[data-start-date], [data-end-date]").forEach((input) => {
      input.addEventListener("change", () => this.renderSelectedProjectGraph());
    });
  }

  private updateDateFilterFields(): void {
    const mode = this.currentDateFilterMode();
    this.toggleField("[data-range-start-field]", mode !== "range");
    this.toggleField("[data-range-end-field]", mode !== "range");
  }

  private toggleField(selector: string, hidden: boolean): void {
    const field = this.querySelector<HTMLElement>(selector);
    if (field) {
      field.hidden = hidden;
    }
  }

  private renderSelectedProjectGraph(): void {
    const selector = this.querySelector<HTMLSelectElement>("[data-project-stat-select]");
    const selectedOption = selector?.selectedOptions.item(0) ?? null;
    const selectedProjectId = this.normalizeProjectId(selectedOption?.value ?? selector?.value ?? this.projectStats[0]?.project_id ?? "");
    const project = this.projectStats.find((item) => item.project_id === selectedProjectId)
      ?? this.getProjectStatFromOption(selectedOption, selectedProjectId)
      ?? null;
    const dateRange = this.currentDateRange();

    this.renderLineGraph(project, dateRange);
  }

  private getProjectStatFromOption(option: HTMLOptionElement | null, projectId: string): ProjectTaskStat | null {
    if (option == null || option.dataset.members == null || projectId.length === 0) {
      return null;
    }

    try {
      const parsed: unknown = JSON.parse(option.dataset.members);
      const members = this.normalizeMembers(parsed);

      return {
        project_id: projectId,
        project_name: option.textContent?.trim() ?? "Untitled project",
        total_tasks: members.reduce((total, member) => total + member.tasks.length, 0),
        completed_tasks: 0,
        members,
      };
    } catch (error) {
      console.error(error);
      return null;
    }
  }

  private renderLineGraph(project: ProjectTaskStat | null, dateRange: DateRange | null): void {
    const graphShell = this.querySelector<HTMLElement>(".graph-shell");
    const graph = this.querySelector<HTMLElement>("[data-member-task-graph]");
    const empty = this.querySelector<HTMLElement>("[data-graph-empty]");

    if (graph == null || empty == null) {
      return;
    }

    graphShell?.classList.remove("is-empty");
    graph.hidden = false;
    graph.removeAttribute("hidden");
    graph.replaceChildren();
    empty.hidden = true;

    if (graphShell != null && project != null) {
      graphShell.dataset.activeProjectId = project.project_id;
    }

    const members = this.filteredMembers(project, dateRange);
    const hasData = members.length > 0 && members.some((member) => member.task_count > 0);

    if (!hasData) {
      graphShell?.classList.add("is-empty");
      empty.hidden = false;
      return;
    }

    const width = 680;
    const height = 236;
    const padding = { top: 24, right: 24, bottom: 54, left: 48 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const maxValue = Math.max(...members.map((member) => member.task_count), 1);
    const yAxisMax = this.niceAxisMax(maxValue);
    const ticks = this.buildTicks(yAxisMax);
    const points = members.map((member, index): GraphMemberPoint => {
      const x = padding.left + (members.length === 1 ? chartWidth / 2 : (chartWidth / (members.length - 1)) * index);
      const y = padding.top + chartHeight - (member.task_count / yAxisMax) * chartHeight;

      return { ...member, x, y };
    });

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
    svg.setAttribute("class", "member-task-svg");
    svg.setAttribute("preserveAspectRatio", "xMidYMid meet");
    svg.setAttribute("aria-hidden", "true");

    ticks.forEach((value) => {
      const y = padding.top + chartHeight - (value / yAxisMax) * chartHeight;

      svg.appendChild(this.svgElement("line", {
        x1: String(padding.left),
        y1: String(y),
        x2: String(width - padding.right),
        y2: String(y),
        class: "graph-grid-line",
      }));
      const label = this.svgElement("text", {
        x: String(padding.left - 10),
        y: String(y + 4),
        class: "graph-axis-label",
        "text-anchor": "end",
      });
      label.textContent = String(value);
      svg.appendChild(label);
    });

    const yAxisTitle = this.svgElement("text", {
      x: "15",
      y: String(padding.top + chartHeight / 2),
      class: "graph-axis-title",
      "text-anchor": "middle",
      transform: `rotate(-90 15 ${padding.top + chartHeight / 2})`,
    });
    yAxisTitle.textContent = "Tasks";
    svg.appendChild(yAxisTitle);

    if (points.length > 1) {
      svg.appendChild(this.svgElement("polyline", {
        points: points.map((point) => `${point.x},${point.y}`).join(" "),
        class: "graph-line",
      }));
    }

    points.forEach((point) => {
      svg.appendChild(this.svgElement("circle", {
        cx: String(point.x),
        cy: String(point.y),
        r: "4",
        class: "graph-point",
      }));

      const valueLabel = this.svgElement("text", {
        x: String(point.x),
        y: String(point.y - 10),
        class: "graph-value-label",
        "text-anchor": "middle",
      });
      valueLabel.textContent = String(point.task_count);
      svg.appendChild(valueLabel);

      const nameLabel = this.svgElement("text", {
        x: String(point.x),
        y: String(height - 22),
        class: "graph-member-label",
        "text-anchor": "middle",
      });
      nameLabel.textContent = this.truncateLabel(point.name);
      svg.appendChild(nameLabel);
    });

    graph.appendChild(svg);
  }

  private filteredMembers(project: ProjectTaskStat | null, dateRange: DateRange | null): MemberTaskStat[] {
    if (project == null || dateRange == null) {
      return [];
    }

    return project.members.map((member) => {
      const tasks = member.tasks.filter((task) => this.taskIsWithinRange(task, dateRange));

      return {
        ...member,
        task_count: tasks.length,
        tasks,
      };
    });
  }

  private taskIsWithinRange(task: MemberTaskItem, dateRange: DateRange): boolean {
    const taskDate = this.parseDateInput(task.date);
    if (taskDate == null) {
      return false;
    }

    return taskDate >= dateRange.start && taskDate <= dateRange.end;
  }

  private currentDateFilterMode(): DateFilterMode {
    const value = this.querySelector<HTMLSelectElement>("[data-date-filter]")?.value;
    return value === "month" || value === "week" || value === "today" || value === "range" ? value : "month";
  }

  private currentDateRange(): DateRange | null {
    const today = this.startOfDay(new Date());
    const mode = this.currentDateFilterMode();

    if (mode === "today") {
      return { start: today, end: today };
    }

    if (mode === "week") {
      const day = today.getDay() === 0 ? 7 : today.getDay();
      const start = this.addDays(today, 1 - day);
      const end = this.addDays(start, 6);
      return { start, end };
    }

    if (mode === "month") {
      return {
        start: new Date(today.getFullYear(), today.getMonth(), 1),
        end: new Date(today.getFullYear(), today.getMonth() + 1, 0),
      };
    }

    const start = this.parseDateInput(this.querySelector<HTMLInputElement>("[data-start-date]")?.value ?? "");
    const end = this.parseDateInput(this.querySelector<HTMLInputElement>("[data-end-date]")?.value ?? "");
    if (start == null || end == null) {
      return null;
    }

    return start <= end ? { start, end } : { start: end, end: start };
  }

  private niceAxisMax(maxValue: number): number {
    if (maxValue <= 4) {
      return Math.max(Math.ceil(maxValue), 1);
    }

    const roughStep = maxValue / 4;
    const magnitude = 10 ** Math.floor(Math.log10(roughStep));
    const normalized = roughStep / magnitude;
    const niceStep = normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10;

    return niceStep * magnitude * 4;
  }

  private buildTicks(maxValue: number): number[] {
    if (maxValue <= 4) {
      return Array.from({ length: maxValue + 1 }, (_, index) => index);
    }

    const step = maxValue / 4;
    return Array.from({ length: 5 }, (_, index) => Math.round(step * index));
  }

  private readProjectStats(): ProjectTaskStat[] {
    const dataElement = this.querySelector<HTMLScriptElement>("#dashboard-project-task-stats");

    if (dataElement == null) {
      return [];
    }

    try {
      const parsed: unknown = JSON.parse(dataElement.textContent ?? "[]");

      if (!Array.isArray(parsed)) {
        return [];
      }

      return parsed
        .map((item) => this.normalizeProjectStat(item))
        .filter((item): item is ProjectTaskStat => item != null);
    } catch (error) {
      console.error(error);
      return [];
    }
  }

  private normalizeProjectStat(value: unknown): ProjectTaskStat | null {
    if (typeof value !== "object" || value == null) {
      return null;
    }

    const project = value as Record<string, unknown>;
    const projectId = this.normalizeProjectId(project.project_id);
    const members = Array.isArray(project.members) ? project.members : [];

    if (projectId.length === 0) {
      return null;
    }

    return {
      project_id: projectId,
      project_name: String(project.project_name ?? "Untitled project"),
      total_tasks: Number(project.total_tasks ?? 0),
      completed_tasks: Number(project.completed_tasks ?? 0),
      members: this.normalizeMembers(members),
    };
  }

  private normalizeMembers(value: unknown): MemberTaskStat[] {
    if (!Array.isArray(value)) {
      return [];
    }

    return value.map((member) => {
      const row = member as Record<string, unknown>;
      const tasks = Array.isArray(row.tasks) ? row.tasks : [];

      return {
        member_id: row.member_id == null ? null : Number(row.member_id),
        name: String(row.name ?? "Unassigned"),
        task_count: Number(row.task_count ?? 0),
        tasks: this.normalizeTasks(tasks),
      };
    });
  }

  private normalizeTasks(value: unknown[]): MemberTaskItem[] {
    return value.map((task) => {
      const row = task as Record<string, unknown>;
      const taskType = row.task_type === "subtask" ? "subtask" : "task";

      return {
        task_id: Number(row.task_id ?? 0),
        task_type: taskType,
        date: String(row.date ?? ""),
      };
    });
  }

  private normalizeProjectId(value: unknown): string {
    return String(value ?? "").trim();
  }

  private parseDateInput(value: string): Date | null {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
    if (match == null) {
      return null;
    }

    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
  }

  private startOfDay(value: Date): Date {
    return new Date(value.getFullYear(), value.getMonth(), value.getDate());
  }

  private addDays(value: Date, days: number): Date {
    const next = new Date(value);
    next.setDate(next.getDate() + days);
    return next;
  }

  private toDateInputValue(value: Date): string {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const date = String(value.getDate()).padStart(2, "0");
    return `${year}-${month}-${date}`;
  }

  private svgElement(name: string, attributes: Record<string, string>): SVGElement {
    const element = document.createElementNS("http://www.w3.org/2000/svg", name);

    Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));

    return element;
  }

  private truncateLabel(value: string): string {
    return value.length > 12 ? `${value.slice(0, 10)}...` : value;
  }
}
