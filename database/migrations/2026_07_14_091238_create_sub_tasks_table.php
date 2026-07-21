<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("sub_tasks", function (Blueprint $table) {
            $table->id();
            $table->foreignId("task_id")->constrained("tasks");
            $table->foreignId("assignee_id")->nullable()->constrained("users");
            $table->string("title");
            $table->date("due_date")->nullable();
            $table->enum("priority", ["low", "medium", "high"])->default("medium")->nullable();
            $table->enum("status", ["draft", "in_progress", "completed"])->default("draft");
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists("sub_tasks");
    }
};
