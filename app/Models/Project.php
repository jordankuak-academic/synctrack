<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(["creator_id", "title", "description"])]
class Project extends Model {
    use SoftDeletes;
    
    protected function casts(): array {
        return [
            "id" => "integer",
            "creator_id" => "integer",
            "title" => "string",
            "description" => "string",
        ];
    }
    
    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, "creator_id");
    }
    
    public function members(): HasMany {
        return $this->hasMany(Member::class, "project_id");
    }
    
    public function tasks(): HasMany {
        return $this->hasMany(Task::class, "project_id");
    }
}
