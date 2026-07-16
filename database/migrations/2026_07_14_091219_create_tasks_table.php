<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("tasks", function (Blueprint $table) {
            $table->id();
            $table->foreignId("project_id")->constrained("projects")->onDelete("cascade");
            $table->foreignId("assignee_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("title");
            $table->date("due_date")->nullable();
            $table->enum("priority", ["low", "medium", "high"])->default("medium")->nullable();
            $table->enum("status", ["draft", "in_progress", "completed"])->default("draft");
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists("tasks");
    }
};
