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

        $projectQuery = Project::with([
            "creator",
            "members.user",
            "tasks.assignee",
            "tasks.subTasks.assignee",
        ]);

        if (!$this->userIsAdmin($currentUser)) {
            $projectQuery->where(function($query) use ($currentUser) {
                $query->where("creator_id", $currentUser->id)
                    ->orWhereHas("members", fn($query) => $query->where("user_id", $currentUser->id))
                    ->orWhereHas("tasks", fn($query) => $query->where("assignee_id", $currentUser->id))
                    ->orWhereHas("tasks.subTasks", fn($query) => $query->where("assignee_id", $currentUser->id));
            });
        }

        $projects = $projectQuery->get();

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
                $subTasks = $project->tasks->flatMap(fn($task) => $task->subTasks);
                $parentTasks = $project->tasks->filter(fn($task) => $task->subTasks->isEmpty());
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
                    ->merge($parentTasks
                        ->filter(fn($task) => $task->assignee !== null)
                        ->map(fn($task) => [
                            "id" => $task->assignee_id,
                            "name" => $task->assignee->username,
                        ]))
                    ->merge($subTasks
                        ->filter(fn($subTask) => $subTask->assignee !== null)
                        ->map(fn($subTask) => [
                            "id" => $subTask->assignee_id,
                            "name" => $subTask->assignee->username,
                        ]))
                    ->filter(fn($member) => $member["id"] !== null)
                    ->unique("id")
                    ->values();

                $memberStats = $members->map(function($member) use ($parentTasks, $subTasks) {
                    $tasks = $parentTasks
                        ->filter(fn($task) => (int) $task->assignee_id === (int) $member["id"] && $task->assignee !== null)
                        ->toBase()
                        ->map(fn($task) => $this->formatDashboardTask($task, "task"))
                        ->merge($subTasks
                            ->filter(fn($subTask) => (int) $subTask->assignee_id === (int) $member["id"] && $subTask->assignee !== null)
                            ->toBase()
                            ->map(fn($subTask) => $this->formatDashboardTask($subTask, "subtask")))
                        ->values();

                    return [
                        "member_id" => $member["id"],
                        "name" => $member["name"],
                        "task_count" => $tasks->count(),
                        "tasks" => $tasks->all(),
                    ];
                });

                $unassignedTasks = $parentTasks
                    ->filter(fn($task) => $task->assignee_id === null || $task->assignee === null)
                    ->toBase()
                    ->map(fn($task) => $this->formatDashboardTask($task, "task"))
                    ->merge($subTasks
                        ->filter(fn($subTask) => $subTask->assignee_id === null || $subTask->assignee === null)
                        ->toBase()
                        ->map(fn($subTask) => $this->formatDashboardTask($subTask, "subtask")))
                    ->values();

                if ($unassignedTasks->isNotEmpty()) {
                    $memberStats->push([
                        "member_id" => null,
                        "name" => "Unassigned",
                        "task_count" => $unassignedTasks->count(),
                        "tasks" => $unassignedTasks->all(),
                    ]);
                }

                return [
                    "project_id" => $project->id,
                    "project_name" => $project->title,
                    "total_tasks" => $parentTasks->count() + $subTasks->count(),
                    "completed_tasks" => $parentTasks->where("status", "completed")->count() + $subTasks->where("status", "completed")->count(),
                    "members" => $memberStats->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function formatDashboardTask($task, string $type): array {
        return [
            "task_id" => $task->id,
            "task_type" => $type,
            "date" => $task->created_at?->toDateString(),
        ];
    }

    private function isProjectCompleted($project): bool {
        return $project->tasks->isNotEmpty()
            && $project->tasks->every(fn($task) => $task->status === "completed" && $task->subTasks->every(fn($subTask) => $subTask->status === "completed"));
    }
}


