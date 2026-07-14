<?php
namespace App\Utilities\Validators;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Throwable;

trait HasSoftDelete {
    protected function validateSoftDeleteModel(Model $model): bool {
        try {
            $traits = class_uses_recursive($model);
            return \in_array(SoftDeletes::class, $traits, true);
        } catch (Throwable $exception) {
            throw new Exception("Error Occurred While Validating Soft Delete Model. Exception: {$exception->getMessage()}");
        }
    }
    
    protected function validateSoftDeleteColumn(Model $model): bool {
        try {
            return Schema::hasColumn($model->getTable(), "deleted_at");
        } catch (Throwable $exception) {
            throw new Exception("Error Occurred While Validating Soft Delete Column. Exception: {$exception->getMessage()}");
        }
    }
}
