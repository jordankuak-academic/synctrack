<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class BoardController extends Controller {
    public function index(): View {
        $current_user = Auth::user();
        $tasksRawData = Task::with(["project", "assignee"])
            ->where("assignee_id", $current_user->id)
            ->whereDoesntHave("subTasks")
            ->get()
            ->map(fn($task) => $this->formatTaskData($task, "task"));

        $subTasksRawData = SubTask::with(["task.project", "assignee"])
            ->where("assignee_id", $current_user->id)
            ->get()
            ->map(fn($subtask) => $this->formatTaskData($subtask, "subtask"));

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $boardItems = $tasksRawData
            ->concat($subTasksRawData)
            ->sortBy([
                fn($item) => $priorityOrder[$item["priority"] ?? ""] ?? 4,
                fn($item) => $item["due_date"]?->timestamp ?? PHP_INT_MAX,
                fn($item) => $item["id"],
            ])
            ->values();

        $tasks = [
            "in_progress" => $boardItems->filter(fn($item) => $item["status"] !== "completed" && ($item["due_date"] === null || !$item["due_date"]->lt(today()->startOfDay())))->values(),
            "completed" => $boardItems->filter(fn($item) => $item["status"] === "completed")->values(),
            "overdue" => $boardItems->filter(fn($item) => $item["status"] !== "completed" && $item["due_date"] !== null && $item["due_date"]->lt(today()->startOfDay()))->values(),
        ];

        $calendarTasks = $boardItems
            ->map(fn($item) => [
                "id" => $item["id"],
                "type" => $item["type"],
                "title" => $item["title"],
                "description" => $item["description"],
                "project" => $item["project"],
                "parent" => $item["parent"],
                "createdDate" => $item["createdDate"],
                "dueDate" => $item["dueDate"],
                "status" => $item["status"],
                "priority" => $item["priority"] ?? "medium",
                "assignee_id" => $item["assignee_id"],
                "updateUrl" => $item["updateUrl"],
            ])
            ->values()
            ->all();

        return view("pages.board", compact("tasks", "calendarTasks"));
    }

    private function formatTaskData($item, string $type): array {
        $isSubtask = $type === 'subtask';
        $parent = $isSubtask ? $item->task : null;
        $project = $isSubtask ? $parent?->project : $item->project;
        return [
            "id" => $item->id,
            "type" => $type,
            "title" => $item->title,
            "description" => $project?->description,
            "assignee_id" => $item->assignee_id,
            "assignee_name" => $item->assignee?->username,
            "priority" => $item->priority ?? "medium",
            "status" => $item->status,
            "due_date" => $item->due_date,
            "dueDate" => $item->due_date?->toDateString(),
            "created_at" => $item->created_at,
            "createdDate" => $item->created_at?->toDateString(),
            "updated_at" => $item->updated_at,
            "project_title" => $project?->title,
            "project" => $project?->title,
            "project_description" => $project?->description,
            "parent_title" => $isSubtask ? $parent?->title : null,
            "parent" => $isSubtask ? $parent?->title : null,
            "updateUrl" => $isSubtask
                ? route("subtask.update", ["id" => $item->id])
                : route("task.update", ["id" => $item->id]),
        ];
    }
}
