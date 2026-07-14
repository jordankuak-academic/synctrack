<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(["task_id", "assignee_id", "title", "due_date", "priority", "status"])]
class SubTask extends Model {
    use SoftDeletes;
    
    protected function casts(): array {
        return [
            "id" => "integer",
            "task_id" => "integer",
            "assignee_id" => "integer",
            "title" => "string",
            "due_date" => "datetime",
            "priority" => "string",
            "status" => "string",
        ];
    }
    
    public function task(): BelongsTo {
        return $this->belongsTo(Task::class, "task_id");
    }
    
    public function assignee(): BelongsTo {
        return $this->belongsTo(User::class, "assignee_id");
    }
}
