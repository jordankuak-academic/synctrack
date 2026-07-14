<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("members", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("project_id")->constrained("projects")->onDelete("cascade");
            $table->string("duty_position")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists("members");
    }
};
