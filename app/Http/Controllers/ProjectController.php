<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SubTask;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller {
    use ApiResponse;

    /**
     * [GET|PAGE] Project Index Page.
     *
     * @return View The View Link To The Project Index Page.
     */
    public function index() {
        $current_user = Auth::user();

        $projectQuery = Project::with(["tasks", "tasks.subTasks", "members.user", "creator"]);

        if (!$this->userIsAdmin($current_user)) {
            $projectQuery->where(function($query) use ($current_user) {
                $query->where("creator_id", $current_user->id)
                    ->orWhereHas("members", fn($query) => $query->where("user_id", $current_user->id))
                    ->orWhereHas("tasks", fn($query) => $query->where("assignee_id", $current_user->id))
                    ->orWhereHas("tasks.subTasks", fn($query) => $query->where("assignee_id", $current_user->id));
            });
        }

        $projects = $projectQuery
            ->get()
            ->map(function($project) use ($current_user) {
                $members = $project->members
                    ->filter(fn($member) => $member->user !== null)
                    ->map(fn($member) => [
                        "id" => $member->user_id,
                        "membership_id" => $member->id,
                        "username" => $member->user->username,
                        "email" => $member->user->email,
                        "role" => $member->duty_position ?? "Member",
                        "is_owner" => false
                    ])
                    ->prepend([
                        "id" => $project->creator->id,
                        "membership_id" => null,
                        "username" => $project->creator->username,
                        "email" => $project->creator->email,
                        "role" => "Owner",
                        "is_owner" => true,
                    ])
                    ->values();

                $canManageProject = $this->canManageProject($current_user, $project);
                $ownershipLabel = $project->creator_id === $current_user?->id ? "Your Project" : "Member Project";

                unset($project->members, $project->creator);

                $project->members = $members;
                $project->can_manage = $canManageProject;
                $project->ownership_label = $ownershipLabel;
                return $project;
            })
            ->pipe(function($projects) {
                [$completed_projects, $on_progress_projects] = $projects
                    ->partition(fn($project) => $project->tasks->isNotEmpty() && $project->tasks->every(fn($task) => $task->status === "completed" && $task->subTasks->every(fn($sub_task) => $sub_task->status === "completed")))
                    ->all();

                return [
                    "completed" => $completed_projects->values()->toArray(),
                    "on_progress" => $on_progress_projects->values()->toArray(),
                ];
            });

        return view("pages.project", compact("projects"));
    }

    /**
     * [POST|API] Project Create API Endpoint.
     *
     * @param Request $request The Request Object.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function store(Request $request) {
        $current_user = Auth::user();

        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:255",
        ]);

        Project::create([
            "creator_id" => $current_user->id,
            "title" => $validated["title"],
            "description" => $validated["description"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Project Created Successfully!"));
    }

    /**
     * [PUT|API] Project Update API Endpoint.
     *
     * @param Request $request The Request Object.
     * @param string $id The Project ID.
     * @return RedirectResponse The Redirect Response To The Project View Page.
     */
    public function update(Request $request, string $id) {
        $project = Project::findOrFail($id);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $project)) {
            return $this->unauthorizedRedirect();
        }

        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:255",
        ]);

        $project->update([
            "title" => $validated["title"],
            "description" => $validated["description"],
        ]);

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Project Updated Successfully!"));
    }

    /**
     * [DELETE|API] Project Destroy API Endpoint.
     *
     * @param string $id The Project ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function destroy(string $id) {
        $project = Project::findOrFail($id);
        $current_user = Auth::user();

        if (!$this->canManageProject($current_user, $project)) {
            return $this->unauthorizedRedirect();
        }

        DB::transaction(function() use ($project) {
            $project->load("tasks");

            SubTask::whereHas("task", function($query) use ($project) {
                $query->where("project_id", $project->id);
            })->delete();

            $project->members()->delete();
            $project->tasks()->delete();
            $project->delete();
        });

        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Project Voided Successfully!"));
    }

    private function unauthorizedRedirect(): RedirectResponse {
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->errorResponse("You do not have access to this action!"));
    }
}