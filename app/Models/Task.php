<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(["project_id", "assignee_id", "title", "due_date", "priority", "status"])]
class Task extends Model {
    use SoftDeletes;
    
    protected function casts(): array {
        return [
            "id" => "integer",
            "project_id" => "integer",
            "assignee_id" => "integer",
            "title" => "string",
            "due_date" => "date",
            "priority" => "string",
            "status" => "string",
        ];
    }
    
    public function assignee(): BelongsTo {
        return $this->belongsTo(User::class, "assignee_id");
    }
    
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class, "project_id");
    }
    
    public function subTasks(): HasMany {
        return $this->hasMany(SubTask::class, "task_id");
    }
}
