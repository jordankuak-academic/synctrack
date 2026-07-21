<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\Project;
use App\Models\Task;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller {
    use ApiResponse;

    /**
     * [POST|API] Task Create API Endpoint.
     *
     * @param Request $request The Request Object.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function store(Request $request) {
        $request->merge([
            "priority" => strtolower((string) ($request->input("priority") ?: "low")),
        ]);

        $validated = $request->validate([
            "project_id" => "required|integer|exists:projects,id",
            "assignee_id" => "nullable|integer|exists:users,id",
            "title" => "required|string|max:255",
            "due_date" => "nullable|date",
            "priority" => "required|in:low,medium,high",
            "status" => "required|in:draft,in_progress,completed",
        ]);
        $project = Project::findOrFail($validated["project_id"]);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $project)) {
            return $this->unauthorizedRedirect();
        }

        Task::create([
            "project_id" => $validated["project_id"],
            "assignee_id" => $validated["assignee_id"],
            "title" => $validated["title"],
            "due_date" => $validated["due_date"],
            "priority" => $validated["priority"] ?? "low",
            "status" => $validated["status"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Task Created Successfully!"));
    }

    /**
     * [PUT|API] Task Update API Endpoint.
     *
     * @param Request $request The Request Object.
     * @param string $id The Task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function update(Request $request, string $id) {
        if ($request->has("priority")) {
            $request->merge([
                "priority" => strtolower((string) ($request->input("priority") ?: "low")),
            ]);
        }

        $task = Task::with("project")->findOrFail($id);
        $current_user = Auth::user();

        if ($this->canManageProject($current_user, $task->project)) {
            if ($this->isStatusOnlyRequest($request)) {
                return $this->updateTaskStatus($request, $task);
            }

            $validated = $request->validate([
                "project_id" => "prohibited",
                "assignee_id" => "nullable|integer|exists:users,id",
                "title" => "required|string|max:255",
                "due_date" => "nullable|date",
                "priority" => "required|in:low,medium,high",
                "status" => "required|in:draft,in_progress,completed",
            ]);

            $task->update([
                "assignee_id" => $validated["assignee_id"],
                "title" => $validated["title"],
                "due_date" => $validated["due_date"],
                "priority" => $validated["priority"] ?? "low",
                "status" => $validated["status"],
            ]);

            return redirect()
                ->action([ProjectController::class, "index"])
                ->with("response", $this->successResponse("Task Updated Successfully!"));
        }

        if ($task->assignee_id !== $current_user?->id || $this->hasProtectedTaskChanges($request, $task)) {
            return $this->unauthorizedRedirect();
        }

        return $this->updateTaskStatus($request, $task);
    }

    private function updateTaskStatus(Request $request, Task $task): RedirectResponse {
        $validated = $request->validate([
            "status" => "required|in:draft,in_progress,completed",
        ]);

        $task->update([
            "status" => $validated["status"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Task Status Updated Successfully!"));
    }

    /**
     * [DELETE|API] Task Delete API Endpoint.
     *
     * @param string $id The Task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function destroy(string $id) {
        $task = Task::with("project")->findOrFail($id);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $task->project)) {
            return $this->unauthorizedRedirect();
        }

        DB::transaction(function() use ($task) {
            $task->load("subTasks");

            $task->subTasks()->delete();
            $task->delete();
        });

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Task Deleted Successfully!"));
    }
    private function isStatusOnlyRequest(Request $request): bool {
        $editableFields = collect($request->except(["_token", "_method"]))->keys();

        return $editableFields->count() === 1 && $editableFields->first() === "status";
    }
    private function hasProtectedTaskChanges(Request $request, Task $task): bool {
        if ($request->has("project_id")) {
            return true;
        }

        $comparisons = [
            "assignee_id" => fn($value) => $this->nullableInteger($value) !== $task->assignee_id,
            "title" => fn($value) => trim((string) $value) !== $task->title,
            "due_date" => fn($value) => $this->normalizeDate($value) !== $this->normalizeDate($task->due_date),
            "priority" => fn($value) => $this->nullableString($value) !== ($task->priority ?? ""),
        ];

        foreach ($comparisons as $field => $hasChanged) {
            if ($request->has($field) && $hasChanged($request->input($field))) {
                return true;
            }
        }

        return false;
    }

    private function nullableInteger($value): ?int {
        return $value === null || $value === "" ? null : (int) $value;
    }

    private function nullableString($value): string {
        return $value === null ? "" : (string) $value;
    }

    private function normalizeDate($value): string {
        if ($value === null || $value === "") {
            return "";
        }

        return is_string($value) ? substr($value, 0, 10) : $value->format("Y-m-d");
    }
    private function unauthorizedRedirect(): RedirectResponse {
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->errorResponse("You do not have access to this action!"));
    }
}