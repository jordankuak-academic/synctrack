<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller {
  public function show(Request $request): View {
    return view("pages.profile", ["user" => $request->user()]);
  }

  public function update(Request $request): RedirectResponse {
    $user = $request->user();
    $fields = ["fullname", "username", "nric", "email", "contact"];
    $values = collect($request->only($fields))
      ->map(fn($value) => is_string($value) ? trim($value) : $value)
      ->all();
    $request->merge($values);
    $validated = $request->validate([
      "fullname" => ["required", "string", "max:255"],
      "username" => ["required", "string", "max:255"],
      "nric" => ["required", "string", "max:255", Rule::unique("users", "nric")->ignore($user->id)],
      "email" => ["required", "email", "max:255", Rule::unique("users", "email")->ignore($user->id)],
      "contact" => ["required", "string", "max:255", Rule::unique("users", "contact")->ignore($user->id)],
    ]);
    $user->update($validated);

    return redirect()->route("profile.show")->with("profile_success", "Profile updated successfully.");
  }

  public function updatePassword(Request $request): RedirectResponse {
    if ($request->input("new_password") !== $request->input("new_password_confirmation")) {
      return redirect()->route("profile.show")
        ->with("profile_error", "Password different. Please try again.")
        ->with("profile_mode", "password");
    }

    $validator = Validator::make($request->all(), [
      "new_password" => ["required", "string", "min:8", "confirmed"],
    ]);
    if ($validator->fails()) {
      return redirect()->route("profile.show")
        ->withErrors($validator)
        ->with("profile_mode", "password");
    }

    $validated = $validator->validated();
    $request->user()->update(["password" => Hash::make($validated["new_password"])]);

    return redirect()->route("profile.show")->with("profile_success", "Password change successful.");
  }
}
