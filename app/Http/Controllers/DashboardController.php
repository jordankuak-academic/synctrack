<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller {
    public function index(): View {
        $current_user = Auth::user();
        
        $tasksRawData = Task::with(["project"])
            ->where("assignee_id", $current_user->id)
            ->whereDoesntHave("subTasks")
            ->where(function($query) {
                $query->where("status", "!=", "completed")
                    ->orWhere("updated_at", ">=", today());
            })
            ->get()
            ->map(function($task) {
                return [
                    "id" => $task->id,
                    "type" => "task",
                    "title" => $task->title,
                    "assignee_id" => $task->assignee_id,
                    "priority" => $task->priority,
                    "status" => $task->status,
                    "due_date" => $task->due_date,
                    "updated_at" => $task->updated_at,
                    "project_title" => $task->project?->title,
                    "parent_title" => null,
                ];
            });
        
        $subTasksRawData = SubTask::with(["task.project"])
            ->where("assignee_id", $current_user->id)
            ->where(function($query) {
                $query->where("status", "!=", "completed")
                    ->orWhere("updated_at", ">=", today());
            })
            ->get()
            ->map(function($subtask) {
                return [
                    "id" => $subtask->id,
                    "type" => "subtask",
                    "title" => $subtask->title,
                    "assignee_id" => $subtask->assignee_id,
                    "priority" => $subtask->priority,
                    "status" => $subtask->status,
                    "due_date" => $subtask->due_date,
                    "updated_at" => $subtask->updated_at,
                    "project_title" => $subtask->task?->project?->title,
                    "parent_title" => $subtask->task?->title,
                ];
            });
            
        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $dashboardItems = $tasksRawData
            ->concat($subTasksRawData)
            ->sortBy(fn($item) => $priorityOrder[$item["priority"]] ?? 4)
            ->values();

        $tasks = [
            "in_progress" => $dashboardItems->filter(fn($item) => $item["status"] !== "completed" && ($item["due_date"] === null || !$item["due_date"]->isPast()))->values(),
            "completed" => $dashboardItems->filter(fn($item) => $item["status"] === "completed" && $item["updated_at"]->gte(today()))->values(),
            "overdue" => $dashboardItems->filter(fn($item) => $item["status"] !== "completed" && $item["due_date"] !== null && $item["due_date"]->isPast())->values(),
        ];

        return view("pages.dashboard", compact("tasks"));
    }
}