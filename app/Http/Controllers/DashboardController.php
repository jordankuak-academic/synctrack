<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index(): View {
        $summary = $this->getDashboardSummary();
        $analysis = $this->getProjectAnalysis();
        
        return view("pages.dashboard", compact("summary", "analysis"));
    }
    
    private function getDashboardSummary(): array {
        $current_user = Auth::user();
        
        $owned_projects = Project::where("creator_id", $current_user->id)->count();
        $joined_projects = Project::with("members")
            ->where("creator_id", "!=", $current_user->id)
            ->whereHas("members", function ($query) use ($current_user) {
                $query->where("user_id", $current_user->id);
            })
            ->count();
        $completed_projects = Project::where(function ($query) use ($current_user) {
            $query->where("creator_id", $current_user->id)
                ->orWhereHas("members", function ($query) use ($current_user) {
                    $query->where("user_id", $current_user->id);
                });
        })
        ->with(["tasks.subTasks"])
        ->withCount(["tasks" => function ($query) {
            $query->withCount("subTasks");
        }])
        ->get()
        ->filter(function ($project) {
            foreach ($project->tasks as $task) {
                $is_completed = false;
                
                if ($task->subTasks->count() > 0) {
                    $completed_subtasks = $task->subTasks->where("status", "completed")->count();
                    $is_completed = $completed_subtasks == $task->subTasks->count();
                } else {
                    $is_completed = $task->status == "completed";
                }
                
                return $is_completed;
            }
        })
        ->count();
        $progressed_projects = Project::where(function ($query) use ($current_user) {
            $query->where("creator_id", $current_user->id)
                ->orWhereHas("members", function ($query) use ($current_user) {
                    $query->where("user_id", $current_user->id);
                });
        })
        ->with(["tasks.subTasks"])
        ->withCount(["tasks" => function ($query) {
            $query->withCount("subTasks");
        }])
        ->get()
        ->filter(function ($project) {
            if ($project->tasks->count() == 0) {
                return true;
            }
            
            foreach ($project->tasks as $task) {
                $is_completed = false;
                
                if ($task->subTasks->count() > 0) {
                    $completed_subtasks = $task->subTasks->where("status", "completed")->count();
                    $is_completed = $completed_subtasks == $task->subTasks->count();
                } else {
                    $is_completed = $task->status == "completed";
                }
                
                return !$is_completed;
            }
        })
        ->count();
            
        return [
            "owned_projects" => $owned_projects,
            "joined_projects" => $joined_projects,
            "completed_projects" => $completed_projects,
            "progressed_projects" => $progressed_projects,
        ];
    }
    
    private function getProjectAnalysis(): array {
        $current_user = Auth::user();
        $owned_projects = Project::with([
            "members.user", 
            "tasks" => function ($query) {
                $query->with([
                    "assignee",
                    "subTasks.assignee"
                ]);
            }
        ])->where("creator_id", $current_user->id)->get();
        
        $result = [];
        
        foreach ($owned_projects as $project) {
            $assignments = [];
            
            foreach ($project->tasks as $task) {
                $has_subtasks = $task->subTasks->isNotEmpty();
                
                if (!$has_subtasks && $task->assignee_id) {
                    $key = $task->assignee->id;
                    if (!isset($assignments[$key])) {
                        $assignments[$key] = [
                            "user" => $task->assignee,
                            "tasks" => [],
                        ];
                    }
                    $assignments[$key]["tasks"][] = $task;
                }
                
                foreach ($task->subTasks as $subtask) {
                    if ($subtask->assignee_id) {
                        $key = $subtask->assignee->id;
                        if (!isset($assignments[$key])) {
                            $assignments[$key] = [
                                "user" => $subtask->assignee,
                                "tasks" => [],
                            ];
                        }
                        $assignments[$key]["tasks"][] = $subtask;
                    }
                }
            }
            
            foreach ($project->members as $member) {
                if (!isset($assignments[$member->user_id])) {
                    $assignments[$member->user_id] = [
                        "user" => $member->user,
                        "tasks" => [],
                    ];
                }
            }
            
            $member_data = [];
            
            foreach ($assignments as $data) {
                $statistics = [
                    "total_count" => 0,
                    "pending" => 0,
                    "completed" => 0,
                    "overdue" => 0
                ];
                
                foreach ($data["tasks"] as $item) {
                    $statistics["total_count"]++;
                    
                    $status = $item->status ?? "in_progress";
                    $is_overdue = false;
                    
                    if ($item->due_date && $status != "completed") {
                        $is_overdue = now()->gt($item->due_date);
                    }
                    
                    if ($status == "completed") {
                        $statistics["completed"]++;
                    } elseif ($status != "completed" && $is_overdue) {
                        $statistics["overdue"]++;
                    } else {
                        $statistics["pending"]++;
                    }
                }
                
                $member_name = $data["user"]->username ?? "Unknown";
                $member_data[$member_name] = $statistics;
            }
            
            $result[] = [
                "project_id" => $project->id,
                "project_name" => $project->title,
                "members" => $member_data,
            ];
        }
        return $result;
    }
}


