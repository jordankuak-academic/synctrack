<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller {
    public function index(): View {
        $current_user = Auth::user();
        
        $tasks = Task::with(["subTasks"])
            ->where(function($query) use ($current_user) {
                $query->where("assignee_id", $current_user->id)
                    ->orWhereHas("subTasks", fn($query) => $query->where("assignee_id", $current_user->id));
            })
            ->get()
            ->toArray();
        
        return view("pages.dashboard", compact("tasks"));
    }
}