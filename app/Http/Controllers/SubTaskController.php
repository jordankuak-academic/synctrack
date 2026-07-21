<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\SubTask;
use App\Models\Task;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubTaskController extends Controller {
    use ApiResponse;

    /**
     * [POST|API] Sub Task Create API Endpoint.
     *
     * @param Request $request The Request Object.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            "task_id" => "required|integer|exists:tasks,id",
            "assignee_id" => "nullable|integer|exists:users,id",
            "title" => "required|string|max:255",
            "due_date" => "nullable|date",
            "priority" => "nullable|in:low,medium,high",
            "status" => "required|in:draft,in_progress,completed",
        ]);

        $task = Task::with("project")->findOrFail($validated["task_id"]);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $task->project)) {
            return $this->unauthorizedRedirect();
        }

        SubTask::create([
            "task_id" => $validated["task_id"],
            "assignee_id" => $validated["assignee_id"],
            "title" => $validated["title"],
            "due_date" => $validated["due_date"],
            "priority" => $validated["priority"],
            "status" => $validated["status"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Sub-task Created Successfully"));
    }

    /**
     * [PUT|API] Sub Task Update API Endpoint.
     *
     * @param Request $request The Request Object.
     * @param string $id The Sub-task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function update(Request $request, string $id) {
        $subtask = SubTask::with("task.project")->findOrFail($id);
        $current_user = Auth::user();
        $project = $subtask->task->project;

        if ($this->canManageProject($current_user, $project)) {
            if ($this->isStatusOnlyRequest($request)) {
                return $this->updateSubTaskStatus($request, $subtask);
            }

            $validated = $request->validate([
                "task_id" => "prohibited",
                "assignee_id" => "nullable|integer|exists:users,id",
                "title" => "required|string|max:255",
                "due_date" => "nullable|date",
                "priority" => "nullable|in:low,medium,high",
                "status" => "required|in:draft,in_progress,completed",
            ]);

            $subtask->update([
                "assignee_id" => $validated["assignee_id"],
                "title" => $validated["title"],
                "due_date" => $validated["due_date"],
                "priority" => $validated["priority"],
                "status" => $validated["status"],
            ]);

            return redirect()
                ->action([ProjectController::class, "index"])
                ->with("response", $this->successResponse("Sub-task Updated Successfully"));
        }

        if ($subtask->assignee_id !== $current_user?->id || $this->hasProtectedSubTaskChanges($request, $subtask)) {
            return $this->unauthorizedRedirect();
        }

        return $this->updateSubTaskStatus($request, $subtask);
    }

    private function updateSubTaskStatus(Request $request, SubTask $subtask): RedirectResponse {
        $validated = $request->validate([
            "status" => "required|in:draft,in_progress,completed",
        ]);

        $subtask->update([
            "status" => $validated["status"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Sub-task Status Updated Successfully"));
    }

    /**
     * [DELETE|API] Sub Task Delete API Endpoint.
     *
     * @param string $id The Sub-task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function destroy(string $id) {
        $subtask = SubTask::with("task.project")->findOrFail($id);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $subtask->task->project)) {
            return $this->unauthorizedRedirect();
        }

        $subtask->delete();

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Sub-task Deleted Successfully"));
    }
    private function isStatusOnlyRequest(Request $request): bool {
        $editableFields = collect($request->except(["_token", "_method"]))->keys();

        return $editableFields->count() === 1 && $editableFields->first() === "status";
    }
    private function hasProtectedSubTaskChanges(Request $request, SubTask $subtask): bool {
        if ($request->has("task_id")) {
            return true;
        }

        $comparisons = [
            "assignee_id" => fn($value) => $this->nullableInteger($value) !== $subtask->assignee_id,
            "title" => fn($value) => trim((string) $value) !== $subtask->title,
            "due_date" => fn($value) => $this->normalizeDate($value) !== $this->normalizeDate($subtask->due_date),
            "priority" => fn($value) => $this->nullableString($value) !== ($subtask->priority ?? ""),
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