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

        $today = today()->startOfDay();
        $boardItems = $tasksRawData
            ->concat($subTasksRawData)
            ->sort(fn($left, $right) => $this->compareBoardItems($left, $right, $today))
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
                "priority" => $item["priority"] ?? "",
                "assignee_id" => $item["assignee_id"],
                "updateUrl" => $item["updateUrl"],
            ])
            ->values()
            ->all();
        return view("pages.board", compact("tasks", "calendarTasks"));
    }

    private function compareBoardItems(array $left, array $right, $today): int {
        $priorityComparison = $this->priorityRank($left["priority"] ?? null) <=> $this->priorityRank($right["priority"] ?? null);
        if ($priorityComparison !== 0) {
            return $priorityComparison;
        }

        $dueDateComparison = $this->dueDateRank($left["due_date"] ?? null, $today) <=> $this->dueDateRank($right["due_date"] ?? null, $today);
        if ($dueDateComparison !== 0) {
            return $dueDateComparison;
        }

        return ($right["id"] ?? 0) <=> ($left["id"] ?? 0);
    }

    private function priorityRank(?string $priority): int {
        return match (strtolower(trim((string) $priority))) {
            "high" => 1,
            "medium" => 2,
            "low" => 3,
            default => 4,
        };
    }

    private function dueDateRank($dueDate, $today): int {
        if ($dueDate === null) {
            return PHP_INT_MAX;
        }

        return (int) $dueDate->copy()->startOfDay()->diffInDays($today, true);
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
            "priority" => $item->priority ?? "",
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



