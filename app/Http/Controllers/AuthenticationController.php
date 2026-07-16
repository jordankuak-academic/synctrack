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

class AuthenticationController extends Controller {
    use ApiResponse, SystemSecurity;
    
    /**
     * [POST|API] Authenticate The User Login Credential.
     * 
     * @param Request $request The request body contain with login credential data.
     * @return RedirectResponse Redirect to the authenticated main page if success.
     */
    public function login(Request $request) {
        $email = $request->input("email");
        $ip_key = "user_ip|{$request->ip()}";
        $user_key = "login_user|{$email}|{$request->ip()}";
        
        // Step 01 - Check If Ip Is On Blacklisted.
        if ($this->isBlacklisted($request->ip())) {
            return back()
                ->with("response", $this->errorResponse("Your IP Is Blacklisted."))
                ->withInput($email);
        }
        
        // Step 02 - Check If The User Is Attempts Error 5 Times, Throttle It.
        if (RateLimiter::tooManyAttempts($user_key, 5)) {
            $seconds = RateLimiter::availableIn($user_key);
            return back()
                ->with("response", $this->errorResponse("Too Many Login Attempts. Please Try Again In {$seconds} Seconds."))
                ->withInput($email);
        }
        
        // Step 03 - Validate The Request Data.
        $validated = $request->validate([
            "email" => "required|string|email",
            "password" => "required|string|min:8",
            "is_remember" => "required|boolean",
        ]);
        
        $credentials = [
            "email" => $validated["email"],
            "password" => $validated["password"]
        ];
        
        // Step 04 - Authenticate The User Login.
        if (Auth::attempt($credentials, $validated["is_remember"])) {
            RateLimiter::clear($user_key);
            RateLimiter::clear($ip_key);
            $request->session()->regenerate();
            return redirect()
                ->intended(route("dashboard"))
                ->with("response", $this->successResponse("Login Successfully. Welcome Back!"));
        }
        
        RateLimiter::hit($user_key, 60);
        RateLimiter::hit($ip_key, 60);
        
        // Step 05 - Check If The Ip Is Attempts Error 30 Times, Apply Blacklist.
        if (RateLimiter::tooManyAttempts($ip_key, 30)) {
            $this->applyBlacklist();
            return back()
                ->with("response", $this->errorResponse("Your IP Is Blacklisted."))
                ->withInput($email);
        }
        
        return back()
            ->with("response", $this->errorResponse("Email or Password Is Incorrect."))
            ->withInput($validated["email"]);
    }
    
    /**
     * [POST|API] Validate User Register Details.
     * 
     * @param Request $request The request body contain with registration information.
     * @return RedirectResponse Redirect to the authenticated main page if success.
     */
    public function register(Request $request) {
        $validated = $request->validate([
            "fullname" => "required|string|max:255",
            "username" => "required|string|max:255",
            "identifier" => "nullable|string|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed",
            "nric" => "required|string|unique:users",
            "contact" => "required|string|unique:users,contact",
        ]);
        
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