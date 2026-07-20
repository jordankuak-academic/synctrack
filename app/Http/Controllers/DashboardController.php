<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index(): View {
        $currentUser = Auth::user();

        $projects = Project::with([
            "creator",
            "members.user",
            "tasks.assignee",
            "tasks.subTasks.assignee",
        ])
            ->where(function($query) use ($currentUser) {
                $query->where("creator_id", $currentUser->id)
                    ->orWhereHas("members", fn($query) => $query->where("user_id", $currentUser->id))
                    ->orWhereHas("tasks", fn($query) => $query->where("assignee_id", $currentUser->id))
                    ->orWhereHas("tasks.subTasks", fn($query) => $query->where("assignee_id", $currentUser->id));
            })
            ->get();

        $summary = $this->buildProjectSummary($projects, $currentUser->id);
        $projectMemberTaskStats = $this->buildProjectMemberTaskStats($projects);

        return view("pages.dashboard", compact("summary", "projectMemberTaskStats"));
    }

    private function buildProjectSummary(Collection $projects, int $currentUserId): array {
        $ownedProjects = $projects->where("creator_id", $currentUserId)->count();
        $memberProjects = $projects->where("creator_id", "!=", $currentUserId)->count();
        $completedProjects = $projects->filter(fn($project) => $this->isProjectCompleted($project))->count();
        $inProgressProjects = max($projects->count() - $completedProjects, 0);

        return [
            "total_projects" => $projects->count(),
            "owned_projects" => $ownedProjects,
            "member_projects" => $memberProjects,
            "completed_projects" => $completedProjects,
            "in_progress_projects" => $inProgressProjects,
        ];
    }

    private function buildProjectMemberTaskStats(Collection $projects): array {
        return $projects
            ->map(function($project) {
                $members = collect([
                    [
                        "id" => $project->creator?->id,
                        "name" => $project->creator?->username ?? "Owner",
                    ],
                ])
                    ->merge($project->members
                        ->filter(fn($member) => $member->user !== null)
                        ->map(fn($member) => [
                            "id" => $member->user_id,
                            "name" => $member->user->username,
                        ]))
                    ->merge($project->tasks
                        ->filter(fn($task) => $task->assignee !== null)
                        ->map(fn($task) => [
                            "id" => $task->assignee_id,
                            "name" => $task->assignee->username,
                        ]))
                    ->merge($project->tasks
                        ->flatMap(fn($task) => $task->subTasks)
                        ->filter(fn($subTask) => $subTask->assignee !== null)
                        ->map(fn($subTask) => [
                            "id" => $subTask->assignee_id,
                            "name" => $subTask->assignee->username,
                        ]))
                    ->filter(fn($member) => $member["id"] !== null)
                    ->unique("id")
                    ->values();

                $assignedTaskCounts = $project->tasks
                    ->filter(fn($task) => $task->assignee_id !== null && $task->assignee !== null)
                    ->groupBy("assignee_id")
                    ->map(fn($tasks) => $tasks->count());
                $unassignedTaskCount = $project->tasks
                    ->filter(fn($task) => $task->assignee_id === null || $task->assignee === null)
                    ->count();

                return [
                    "project_id" => $project->id,
                    "project_name" => $project->title,
                    "total_tasks" => $project->tasks->count(),
                    "completed_tasks" => $project->tasks->where("status", "completed")->count(),
                    "members" => $members
                        ->map(fn($member) => [
                            "name" => $member["name"],
                            "task_count" => $assignedTaskCounts->get($member["id"], 0),
                        ])
                        ->when($unassignedTaskCount > 0, fn($members) => $members->push([
                            "name" => "Unassigned",
                            "task_count" => $unassignedTaskCount,
                        ]))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function isProjectCompleted($project): bool {
        return $project->tasks->isNotEmpty()
            && $project->tasks->every(fn($task) => $task->status === "completed" && $task->subTasks->every(fn($subTask) => $subTask->status === "completed"));
    }
}