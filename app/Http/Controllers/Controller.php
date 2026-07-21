<?php
namespace App\Http\Controllers;

use App\Models\Project;

abstract class Controller {
    protected function userIsAdmin($user): bool {
        return (bool) ($user?->is_admin ?? false);
    }

    protected function canManageProject($user, Project $project): bool {
        return $this->userIsAdmin($user) || $project->creator_id === $user?->id;
    }
}