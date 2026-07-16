<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utilities\ResponseFormatters\Php\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller {
    use ApiResponse;
    
    /**
     * [POST|API] Authenticate The User Login Credential.
     * 
     * @param Request $request The request body contain with login credential data.
     * @return RedirectResponse Redirect to the authenticated main page if success.
     */
    public function login(Request $request) {
        $validated = $request->validate([
            "email" => "required|string|email",
            "password" => "required|string|min:8",
            "is_remember" => "required|boolean",
        ]);
        
        $credentials = [
            "email" => $validated["email"],
            "password" => $validated["password"]
        ];
        
        if (Auth::attempt($credentials, $validated["is_remember"])) {
            $request->session()->regenerate();
            return redirect()
                ->intended(route("project"))
                ->with("response", $this->successResponse("Login Successfully. Welcome Back!"));
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
            ->route("project")
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