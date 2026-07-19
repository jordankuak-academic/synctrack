<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller {
    public function index(): View {
        $current_user = Auth::user();

        $tasksRawData = Task::with(["project"])
            ->where("assignee_id", $current_user->id)
            ->whereDoesntHave("subTasks")
            ->where(function ($query) {
                $query->where("status", "!=", "completed")
                    ->orWhere("updated_at", ">=", today());
            })
            ->get()
            ->map(fn($task) => $this->formatTaskData($task, "task"));

        $subTasksRawData = SubTask::with(["task.project"])
            ->where("assignee_id", $current_user->id)
            ->where(function ($query) {
                $query->where("status", "!=", "completed")
                    ->orWhere("updated_at", ">=", today());
            })
            ->get()
            ->map(fn($subtask) => $this->formatTaskData($subtask, "subtask"));

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $dashboardItems = $tasksRawData
            ->concat($subTasksRawData)
            ->sortBy([
                fn($item) => $priorityOrder[$item["priority"]] ?? 4,
                fn($item) => $item["due_date"] ?? PHP_INT_MAX,
                fn($item) => $item["id"],
            ])
            ->values();

        $tasks = [
            "in_progress" => $dashboardItems->filter(fn($item) => $item["status"] !== "completed" && ($item["due_date"] === null || !$item["due_date"]->isPast()))->values(),
            "completed" => $dashboardItems->filter(fn($item) => $item["status"] === "completed" && $item["updated_at"]->gte(today()))->values(),
            "overdue" => $dashboardItems->filter(fn($item) => $item["status"] !== "completed" && $item["due_date"] !== null && $item["due_date"]->isPast())->values(),
        ];

        return view("pages.dashboard", compact("tasks"));
    }

    private function formatTaskData($item, string $type): array {
        $isSubtask = $type === 'subtask';
        $parent = $isSubtask ? $item->task : null;
        $project = $isSubtask ? $parent?->project : $item->project;
        return [
            "id" => $item->id,
            "type" => $type,
            "title" => $item->title,
            "assignee_id" => $item->assignee_id,
            "priority" => $item->priority,
            "status" => $item->status,
            "due_date" => $item->due_date,
            "updated_at" => $item->updated_at,
            "project_title" => $project?->title,
            "parent_title" => $isSubtask ? $parent?->title : null,
        ];
    }
}