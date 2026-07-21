<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(["fullname", "username", "email", "password", "nric", "contact", "is_admin"])]
#[Hidden(["password", "remember_token"])]
class User extends Authenticatable {
    use Notifiable, SoftDeletes;
    
    protected function casts(): array {
        return [
            "id" => "integer",
            "fullname" => "string",
            "username" => "string",
            "email" => "string",
            "password" => "hashed",
            "nric" => "string",
            "contact" => "string",
            "is_admin" => "boolean",
        ];
    }
    
    public function projects(): HasMany {
        return $this->hasMany(Project::class, "creator_id");
    }
    
    public function members(): HasMany {
        return $this->hasMany(Member::class, "user_id");
    }
    
    public function tasks(): HasMany {
        return $this->hasMany(Task::class, "assignee_id");
    }
    
    public function subTasks(): HasMany {
        return $this->hasMany(SubTask::class, "assignee_id");
    }
}
