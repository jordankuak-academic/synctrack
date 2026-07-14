<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\Member;
use App\Models\User;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller {
    use ApiResponse;
    
    /**
     * [POST|API] Member Add Into Project API Endpoint.
     * 
     * @param Request $request The Request Object.
     * @return RedirectResponse The Redirect Response To The Project View Page.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            "email" => "required|string|email|max:255|exists:users,email",
            "project_id" => "required|integer|exists:projects,id",
        ]);
        
        $user = User::where("email", $validated["email"])->first();
        
        Member::firstOrCreate([
            "user_id" => $user->id,
            "project_id" => $validated["project_id"],
        ]);
        
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Member Added Successfully!"));
    }
    
    /**
     * [DELETE|API] Member Remove From Project API Endpoint.
     * 
     * @param string $id The Member ID.
     * @return RedirectResponse The Redirect Response To The Project View Page.
     */
    public function destroy(string $id) {
        $member = Member::with("project")->findOrFail($id);
        $project = $member->project;
        $creator_id = $project->creator_id;
        
        if ($member->user_id === $creator_id) {
            return redirect()
                ->action([ProjectController::class, "index"])
                ->with("response", $this->errorResponse("Creator Cannot Be Removed!"));
        }
        
        $member->delete();
        
        return redirect()
            ->action([ProjectController::class, "index"])
            ->with("response", $this->successResponse("Member Removed Successfully!"));
    }
}