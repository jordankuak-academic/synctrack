<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use App\Utilities\Security\SystemSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller {
    use ApiResponse, SystemSecurity;
    
    /**
     * [POST|API] Authenticate The User Login Credential.
     * 
     * @param Request $request The request body contain with login credential data.
     * @return RedirectResponse Redirect to the authenticated main page if success.
     */
    public function login(Request $request) {
        $ip_key = "user_ip|{$request->ip()}";
        $user_key = "login_user|{$request->input("email")}|{$request->ip()}";
        
        // STEP 01 - CHECK IF IP IS ON BLACKLISTED.
        if ($this->isBlacklisted($request->ip())) {
            return back()
                ->with("response", $this->errorResponse("Your IP Is Blacklisted."))
                ->with("login_input", $request->only(["email"]));
        }
        
        // STEP 02 - CHECK IF THE USER IS ATTEMPTS ERROR 5 TIMES IN 30 SECONDS, THROTTLE IT.
        if (RateLimiter::tooManyAttempts($user_key, 5)) {
            $seconds = RateLimiter::availableIn($user_key);
            return back()
                ->with("response", $this->errorResponse("Too Many Login Attempts. Please Try Again In {$seconds} Seconds."))
                ->with("login_input", $request->only(["email"]));
        }
        
        // STEP 03 - VALIDATE THE REQUEST DATA.
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email",
            "password" => "required|string|min:8",
        ]);
        
        // IF VALIDATION FAILS, RETURN WITH ERROR MESSAGE.
        if ($validator->fails()) {
            return back()
                ->with("response", $this->errorResponse($validator->errors()->first()))
                ->with("login_input", $request->only(["email"]));
        }
        
        $validated = $validator->validated();
        
        $credentials = [
            "email" => $validated["email"],
            "password" => $validated["password"]
        ];
        
        // STEP 04 - AUTHENTICATE THE USER LOGIN.
        if (Auth::attempt($credentials)) {
            RateLimiter::clear($user_key);
            RateLimiter::clear($ip_key);
            $request->session()->regenerate();
            return redirect()
                ->intended(route("dashboard"))
                ->with("response", $this->successResponse("Login Successfully. Welcome Back!"));
        }
        
        RateLimiter::hit($user_key, 30);
        RateLimiter::hit($ip_key, 120);
        
        // STEP 05 - CHECK IF IP IS ATTEMPTS ERROR 10 TIMES IN 120 SECONDS, APPLY BLACKLIST IT.
        if (RateLimiter::tooManyAttempts($ip_key, 10)) {
            $this->applyBlacklist();
            return back()
                ->with("response", $this->errorResponse("Your IP Is Blacklisted."))
                ->with("login_input", $request->only(["email"]));
        }
        
        // IF LOGIN CREDENTIALS ARE INCORRECT, RETURN WITH ERROR MESSAGE.
        return back()
            ->with("response", $this->errorResponse("Email or Password Is Incorrect."))
            ->with("login_input", ["email" => $validated["email"]]);
    }
    
    /**
     * [POST|API] Validate User Register Details.
     * 
     * @param Request $request The request body contain with registration information.
     * @return RedirectResponse Redirect to the authenticated main page if success.
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            "fullname" => "required|string|max:255",
            "username" => "required|string|max:255",
            "identifier" => "nullable|string|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed",
            "nric" => "required|string|unique:users",
            "contact" => "required|string|unique:users,contact",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route("login")
                ->with("response", $this->errorResponse($validator->errors()->first()))
                ->with("register_input", $request->except(["password", "password_confirmation"]))
                ->with("auth_tab", "sign-up");
        }
        
        $validated = $validator->validated();
        
        $user = User::create([
            "fullname" => $validated["fullname"],
            "username" => $validated["username"],
            "identifier" => $validated["identifier"],
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "nric" => $validated["nric"],
            "contact" => $validated["contact"],
        ]);
        
        Auth::login($user);
        
        $request->session()->regenerate();
        
        return redirect()
            ->route("dashboard")
            ->with("response", $this->successResponse("Registration Successful!"));
    }
    
    /**
     * [POST|API] Authenticate The User Logout.
     * 
     * @param Request $request The request object.
     * @return RedirectResponse Redirect to the login page.
     */
    public function logout(Request $request) {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()
            ->route("login")
            ->with("response", $this->successResponse("Logout Successful!"));
    }
}