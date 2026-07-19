<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("fullname");
            $table->string("username");
            $table->string("email")->unique();
            $table->string("password");
            $table->string("nric")->unique();
            $table->string("contact")->unique();
            $table->boolean("is_admin")->default(false);
            $table->timestamp("email_verified_at")->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists("users");
    }
};
