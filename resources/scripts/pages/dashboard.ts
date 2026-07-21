import type { ChartConfigurationCustomTypesPerDataset } from "chart.js";
import Chart from "chart.js/auto";
import type { DashboardMemberSeries, DashboardProjectAnalysis } from "@/types/Dashboard";
import Controller from "@/utils/Controller";

export default class DashboardController extends Controller {
  private chart: Chart | null = null;
  
  protected initialize(): void { 
    this.bindEvents();
    this.renderChart(); 
  }
  
  private bindEvents(): void {
    this.querySelector<HTMLSelectElement>("#project-selector")?.addEventListener("change", () => this.renderChart());
  }
  
  private renderChart(): void {
    const container = this.querySelector<HTMLElement>("#graph-container");
    const emptyState = this.querySelector<HTMLElement>(".graph-empty-state");
    const project = this.getSelectedProject();
    const series = project == null ? [] : this.buildSeries(project);
    const hasData = series.some((item) => item.total > 0);
    
    if (!container || !emptyState) {
      return;
    }
    
    if (!hasData) {
      this.destroyChart();
      container.replaceChildren();
      container.hidden = true;
      emptyState.hidden = false;
      return;
    }
    
    const canvas = document.createElement("canvas");
    container.replaceChildren(canvas);
    container.hidden = false;
    emptyState.hidden = true;
    this.destroyChart();
    this.chart = new Chart(canvas, this.buildChartConfig(series));
  }
  
  private getSelectedProject(): DashboardProjectAnalysis | null {
    const projects = this.readAnalysis();
    const selector = this.querySelector<HTMLSelectElement>("#project-selector");
    const projectId = Number(selector?.value ?? "");
    return projects.find((project) => project.project_id === projectId) ?? projects[0] ?? null;
  }
  
  private readAnalysis(): DashboardProjectAnalysis[] {
    const script = this.querySelector<HTMLScriptElement>("#dashboard-analysis-data");
    if (!script?.textContent) {
      return [];
    }
    try {
      return JSON.parse(script.textContent) as DashboardProjectAnalysis[];
    } catch {
      return [];
    }
  }
  
  private buildSeries(project: DashboardProjectAnalysis): DashboardMemberSeries[] {
    return Object.entries(project.members)
      .map(([name, stats]) => ({ name, in_progress: stats.pending, completed: stats.completed, overdue: stats.overdue, total: stats.total_count }))
      .sort((left, right) => right.total - left.total || left.name.localeCompare(right.name));
  }
  
  private buildChartConfig(series: DashboardMemberSeries[]): ChartConfigurationCustomTypesPerDataset<"bar" | "line", number[], string> {
    const colors = this.getChartColors();
    return {
      data: {
        labels: series.map((item) => item.name),
        datasets: [
          { type: "bar", label: "In Progress", data: series.map((item) => item.in_progress), backgroundColor: colors.progress, borderRadius: 8, order: 2 },
          { type: "bar", label: "Completed", data: series.map((item) => item.completed), backgroundColor: colors.completed, borderRadius: 8, order: 2 },
          { type: "bar", label: "Overdue", data: series.map((item) => item.overdue), backgroundColor: colors.overdue, borderRadius: 8, order: 2 },
          { type: "line", label: "Total Tasks", data: series.map((item) => item.total), borderColor: colors.total, backgroundColor: colors.total, pointBackgroundColor: colors.total, pointBorderColor: colors.total, pointBorderWidth: 2, tension: 0.32, pointRadius: 4, pointHoverRadius: 5, borderWidth: 2, order: -1, clip: false },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: "index", intersect: false },
        plugins: { legend: { position: "top", align: "end", labels: { usePointStyle: true, boxWidth: 10, color: colors.text } } },
        scales: {
          x: { stacked: false, grid: { display: false }, ticks: { color: colors.text } },
          y: { beginAtZero: true, ticks: { precision: 0, color: colors.text }, grid: { color: colors.grid } },
        },
      },
    };
  }
  
  private getChartColors(): Record<string, string> {
    const panel = this.querySelector<HTMLElement>(".analysis-graph-panel-container");
    const styles = panel == null ? null : window.getComputedStyle(panel);
    return {
      progress: styles?.getPropertyValue("--chart-progress").trim() || "#4866E8",
      completed: styles?.getPropertyValue("--chart-completed").trim() || "#3DBA55",
      overdue: styles?.getPropertyValue("--chart-overdue").trim() || "#E85C63",
      total: styles?.getPropertyValue("--chart-total").trim() || "#4C5262",
      text: styles?.getPropertyValue("--chart-text").trim() || "#4C5262",
      grid: styles?.getPropertyValue("--chart-grid").trim() || "#E9EBEF", 
    };
  }
  
  private destroyChart(): void {
    this.chart?.destroy();
    this.chart = null;
  }
}
