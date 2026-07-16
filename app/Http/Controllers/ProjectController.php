<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SubTask;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller {
    use ApiResponse;
    
    /**
     * [GET|PAGE] Project Index Page.
     * 
     * @return View The View Link To The Project Index Page.
     */
    public function index() {
        $current_user = Auth::user();
        
        $projects = Project::with(["tasks", "tasks.subTasks", "members.user", "creator"])
            ->where(function($query) use ($current_user) {
                $query->where("creator_id", $current_user->id)
                    ->orWhereHas("tasks", fn($query) => $query->where("assignee_id", $current_user->id))
                    ->orWhereHas("tasks.subTasks", fn($query) => $query->where("assignee_id", $current_user->id));
            })
            ->get()
            ->map(function($project) {
                $members = $project->members
                    ->filter(fn($member) => $member->user !== null)
                    ->map(fn($member) => [
                        "id" => $member->user_id,
                        "username" => $member->user->username,
                        "email" => $member->user->email,
                        "is_owner" => false
                    ])
                    ->prepend([
                        "id" => $project->creator->id,
                        "username" => $project->creator->username,
                        "email" => $project->creator->email,
                        "is_owner" => true,
                    ])
                    ->values();
                
                unset($project->members, $project->creator);
                
                $project->members = $members;
                return $project;
            })
            ->toArray();
            
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
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:255",
        ]);
        
        $project = Project::findOrFail($id);
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
            ->with("response", $this->successResponse("Project Deleted Successfully!"));
    }
}