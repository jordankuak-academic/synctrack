<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(["user_id", "project_id", "duty_position"])]
class Member extends Model {
    use SoftDeletes;
    
    protected function casts(): array {
        return [
            "id" => "integer",
            "user_id" => "integer",
            "project_id" => "integer",
            "duty_position" => "string",
        ];
    }
    
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, "user_id");
    }
    
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class, "project_id");
    }
}
