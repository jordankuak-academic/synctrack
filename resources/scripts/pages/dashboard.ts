import Controller from "@/utils/Controller";

type MemberTaskStat = {
  name: string;
  task_count: number;
};

type ProjectTaskStat = {
  project_id: string;
  project_name: string;
  total_tasks: number;
  completed_tasks: number;
  members: MemberTaskStat[];
};

export default class DashboardController extends Controller {
  private projectStats: ProjectTaskStat[] = [];

  protected initialize(): void {
    this.projectStats = this.readProjectStats();
    this.bindProjectSelector();
    this.renderSelectedProjectGraph();
  }

  private bindProjectSelector(): void {
    this.querySelector<HTMLSelectElement>("[data-project-stat-select]")?.addEventListener("change", () => {
      this.renderSelectedProjectGraph();
    });
  }

  private renderSelectedProjectGraph(): void {
    const selector = this.querySelector<HTMLSelectElement>("[data-project-stat-select]");
    const selectedOption = selector?.selectedOptions.item(0) ?? null;
    const selectedProjectId = this.normalizeProjectId(selectedOption?.value ?? selector?.value ?? this.projectStats[0]?.project_id ?? "");
    const project = this.projectStats.find((item) => item.project_id === selectedProjectId)
      ?? this.getProjectStatFromOption(selectedOption, selectedProjectId)
      ?? null;

    this.renderLineGraph(project);
  }
  private getProjectStatFromOption(option: HTMLOptionElement | null, projectId: string): ProjectTaskStat | null {
    if (option == null || option.dataset.members == null) {
      return null;
    }

    try {
      const parsed: unknown = JSON.parse(option.dataset.members);
      const members = this.normalizeMembers(parsed);

      return {
        project_id: projectId,
        project_name: option.textContent?.trim() ?? "Untitled project",
        total_tasks: members.reduce((total, member) => total + member.task_count, 0),
        completed_tasks: 0,
        members,
      };
    } catch (error) {
      console.error(error);
      return null;
    }
  }
  private renderLineGraph(project: ProjectTaskStat | null): void {
    const graphShell = this.querySelector<HTMLElement>(".graph-shell");
    const graph = this.querySelector<HTMLElement>("[data-member-task-graph]");
    const empty = this.querySelector<HTMLElement>("[data-graph-empty]");

    if (graph == null || empty == null) {
      return;
    }

    graphShell?.classList.remove("is-empty");
    graph.hidden = false;
    graph.removeAttribute("hidden");
    graph.classList.remove("is-empty");
    graph.style.removeProperty("display");
    empty.hidden = true;
    graph.replaceChildren();

    const members = project?.members ?? [];
    const hasData = members.length > 0 && members.some((member) => member.task_count > 0);

    if (graphShell != null && project != null) {
      graphShell.dataset.activeProjectId = project.project_id;
    }

    if (!hasData) {
      graphShell?.classList.add("is-empty");
      empty.hidden = false;
      return;
    }

    const width = 760;
    const height = 320;
    const padding = { top: 28, right: 28, bottom: 78, left: 48 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const maxValue = Math.max(...members.map((member) => member.task_count), 1);
    const points = members.map((member, index) => {
      const x = padding.left + (members.length === 1 ? chartWidth / 2 : (chartWidth / (members.length - 1)) * index);
      const y = padding.top + chartHeight - (member.task_count / maxValue) * chartHeight;

      return { ...member, x, y };
    });

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
    svg.setAttribute("class", "member-task-svg");
    svg.setAttribute("width", String(width));
    svg.setAttribute("height", String(height));
    svg.setAttribute("preserveAspectRatio", "xMidYMid meet");
    svg.setAttribute("aria-hidden", "true");

    const gridLines = 4;
    for (let index = 0; index <= gridLines; index += 1) {
      const value = Math.round((maxValue / gridLines) * index);
      const y = padding.top + chartHeight - (value / maxValue) * chartHeight;

      svg.appendChild(this.svgElement("line", {
        x1: String(padding.left),
        y1: String(y),
        x2: String(width - padding.right),
        y2: String(y),
        class: "graph-grid-line",
      }));
      const label = this.svgElement("text", {
        x: String(padding.left - 12),
        y: String(y + 4),
        class: "graph-axis-label",
        "text-anchor": "end",
      });
      label.textContent = String(value);
      svg.appendChild(label);
    }

    const yAxisTitle = this.svgElement("text", {
      x: "16",
      y: String(padding.top + chartHeight / 2),
      class: "graph-axis-title",
      "text-anchor": "middle",
      transform: `rotate(-90 16 ${padding.top + chartHeight / 2})`,
    });
    yAxisTitle.textContent = "Tasks";
    svg.appendChild(yAxisTitle);

    svg.appendChild(this.svgElement("polyline", {
      points: points.map((point) => `${point.x},${point.y}`).join(" "),
      class: "graph-line",
    }));

    points.forEach((point) => {
      svg.appendChild(this.svgElement("circle", {
        cx: String(point.x),
        cy: String(point.y),
        r: "5",
        class: "graph-point",
      }));

      const valueLabel = this.svgElement("text", {
        x: String(point.x),
        y: String(point.y - 12),
        class: "graph-value-label",
        "text-anchor": "middle",
      });
      valueLabel.textContent = String(point.task_count);
      svg.appendChild(valueLabel);

      const nameLabel = this.svgElement("text", {
        x: String(point.x),
        y: String(height - 34),
        class: "graph-member-label",
        "text-anchor": "middle",
      });
      nameLabel.textContent = this.truncateLabel(point.name);
      svg.appendChild(nameLabel);
    });

    graph.appendChild(svg);
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

      return {
        name: String(row.name ?? "Unassigned"),
        task_count: Number(row.task_count ?? 0),
      };
    });
  }
  private normalizeProjectId(value: unknown): string {
    return String(value ?? "").trim();
  }

  private svgElement(name: string, attributes: Record<string, string>): SVGElement {
    const element = document.createElementNS("http://www.w3.org/2000/svg", name);

    Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));

    return element;
  }

  private truncateLabel(value: string): string {
    return value.length > 14 ? `${value.slice(0, 12)}...` : value;
  }
}
