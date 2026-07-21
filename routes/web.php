<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get("/", fn(): RedirectResponse => redirect()->route("login"));

Route::get("/login", fn(): View => view("pages.auth.login"))->name("login");
Route::post("/login", [AuthenticationController::class, "login"])->name("login.submit");
Route::post("/register", [AuthenticationController::class, "register"])->name("register.submit");

Route::middleware("auth")->group(function() {
    Route::post("/logout", [AuthenticationController::class, "logout"])->name("logout.submit");

    Route::get("/project", [ProjectController::class, "index"])->name("project");
    Route::post("/project", [ProjectController::class, "store"])->name("project.store");
    Route::put("/project/{id}", [ProjectController::class, "update"])->name("project.update");
    Route::delete("/project/{id}", [ProjectController::class, "destroy"])->name("project.destroy");

    Route::post("/member", [MemberController::class, "store"])->name("member.store");
    Route::delete("/member/{id}", [MemberController::class, "destroy"])->name("member.destroy");

    Route::post("/task", [TaskController::class, "store"])->name("task.store");
    Route::put("/task/{id}", [TaskController::class, "update"])->name("task.update");
    Route::delete("/task/{id}", [TaskController::class, "destroy"])->name("task.destroy");

    Route::post("/subtask", [SubTaskController::class, "store"])->name("subtask.store");
    Route::put("/subtask/{id}", [SubTaskController::class, "update"])->name("subtask.update");
    Route::delete("/subtask/{id}", [SubTaskController::class, "destroy"])->name("subtask.destroy");

    Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");
    
    Route::get("/board", [BoardController::class, "index"])->name("board");
    Route::get("/profile", [ProfileController::class, "show"])->name("profile.show");
    Route::put("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::put("/profile/password", [ProfileController::class, "updatePassword"])->name("profile.password.update");
});
