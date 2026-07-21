export interface DashboardMemberStats {
  total_count: number;
  pending: number;
  completed: number;
  overdue: number;
}

export interface DashboardProjectAnalysis {
  project_id: number;
  project_name: string;
  members: Record<string, DashboardMemberStats>;
}

export interface DashboardMemberSeries {
  name: string;
  in_progress: number;
  completed: number;
  overdue: number;
  total: number;
}