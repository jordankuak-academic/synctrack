<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\Task;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            "project_id" => "required|integer|exists:projects,id",
            "assignee_id" => "nullable|integer|exists:users,id",
            "title" => "required|string|max:255",
            "due_date" => "nullable|datetime",
            "priority" => "nullable|in:low,medium,high",
            "status" => "required|in:draft,in_progress,completed",
        ]);
        
        Task::create([
            "project_id" => $validated["project_id"],
            "assignee_id" => $validated["assignee_id"],
            "title" => $validated["title"],
            "due_date" => $validated["due_date"],
            "priority" => $validated["priority"],
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
        $validated = $request->validate([
            "project_id" => "prohibited",
            "assignee_id" => "nullable|integer|exists:users,id",
            "title" => "required|string|max:255",
            "due_date" => "nullable|datetime",
            "priority" => "nullable|in:low,medium,high",
            "status" => "required|in:draft,in_progress,completed",
        ]);
        
        $task = Task::findOrFail($id);
        $task->update([
            "assignee_id" => $validated["assignee_id"],
            "title" => $validated["title"],
            "due_date" => $validated["due_date"],
            "priority" => $validated["priority"],
            "status" => $validated["status"],
        ]);
        
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Task Updated Successfully!"));
    }
    
    /**
     * [DELETE|API] Task Delete API Endpoint.
     * 
     * @param string $id The Task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function destroy(string $id) {
        $task = Task::findOrFail($id);
        
        DB::transaction(function() use ($task) {
            $task->load("subTasks");
            
            $task->subTasks()->delete();
            $task->delete();
        });
        
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Task Deleted Successfully!"));
    }
}