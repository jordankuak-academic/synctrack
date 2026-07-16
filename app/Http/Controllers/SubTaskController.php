<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\SubTask;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            "task_id" => "prohibited",
            "assignee_id" => "nullable|integer|exists:users,id",
            "title" => "required|string|max:255",
            "due_date" => "nullable|date",
            "priority" => "nullable|in:low,medium,high",
            "status" => "required|in:draft,in_progress,completed",
        ]);
        
        $subtask = SubTask::findOrFail($id);
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
    
    /**
     * [DELETE|API] Sub Task Delete API Endpoint.
     * 
     * @param string $id The Sub-task ID.
     * @return RedirectResponse The Redirect Response To The Project Index Page.
     */
    public function destroy(string $id) {
        $subtask = SubTask::findOrFail($id);
        $subtask->delete();
        
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Sub-task Deleted Successfully"));
    }
}