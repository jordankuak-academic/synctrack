@extends("layouts.xxxxx-layout")

@section("page-title", "Login")

@section("contents")
  <div id="login-wrapper">
    <div class="login-card login-card--split">
      <div class="left-section">
        <div class="logo-wrapper"></div>
        <div class="heading-02 application-title">SyncTrack</div>
      </div>
      <div class="right-section">
        <div class="navigation-tab">
          <div class="tab-item active">
            <span class="sign-in-text">Sign In</span>
          </div>
          <div class="tab-item">
            <span class="sign-up-text">Sign Up</span>
          </div>
        </div>
        <div class="navigation-content">
          <div class="sign-in-wrapper active">
            <h1 class="heading-01 form-title">Sign In</h1>
            
            <form action="{{ route("login.submit") }}" method="post">
              @csrf
              
              <div class="form-group">
                <label for="email" class="email-label">Email</label>
                <input type="email" name="email" placeholder="you@example.com" value="{{ old("email") }}" required>
              </div>
              <div class="form-group">
                <label for="login-password" class="password-label">Password</label>
                <div class="password-field">
                  <input id="login-password" type="password" name="password" placeholder="*******************" required>
                  <button type="button" class="password-toggle" data-password-toggle data-target="login-password" aria-label="Show password" aria-pressed="false">
                    <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                    <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                  </button>
                </div>
              </div>
              
              <input type="hidden" name="is_remember" value="0">
              
              <div class="form-group">
                <button type="submit" class="sign-in-btn">Sign In</button>
              </div>
            </form>
          </div>
          <div class="sign-up-wrapper">
            <h1 class="heading-01 form-title">Sign Up</h1>
            
            <div class="form-divider"></div>
            
            <form action="{{ route("register.submit") }}" method="post">
              @csrf
              <input type="hidden" name="identifier" value="">
              
              <div class="sign-up-step active">
                <p class="step-title">Step 1 : Create Your Account</p>
                
                <div class="form-group">
                  <label for="register-email">Email</label>
                  <input id="register-email" type="email" name="email" placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                  <label for="register-username">Username</label>
                  <input id="register-username" type="text" name="username" placeholder="Enter Your Username" required>
                </div>
                
                <div class="form-row">
                  <div class="form-group">
                    <label for="register-password">Password</label>
                    <div class="password-field">
                      <input id="register-password" type="password" name="password" placeholder="*******************" required>
                      <button type="button" class="password-toggle" data-password-toggle data-target="register-password" aria-label="Show password" aria-pressed="false">
                        <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                        <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                      </button>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label for="register-password-confirmation">Confirm Password</label>
                    <div class="password-field">
                      <input id="register-password-confirmation" type="password" name="password_confirmation" placeholder="*******************" required>
                      <button type="button" class="password-toggle" data-password-toggle data-target="register-password-confirmation" aria-label="Show password" aria-pressed="false">
                        <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                        <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                      </button>
                    </div>
                  </div>
                </div>
                
                <button type="button" class="step-btn">Next</button>
              </div>
              
              <div class="sign-up-step">
                <p class="step-title">Step 2 : Personal Information</p>
                
                <div class="form-group">
                  <label for="register-fullname">Full Name</label>
                  <input id="register-fullname" type="text" name="fullname" placeholder="Enter Your Full Name" required>
                </div>
                
                <div class="form-group">
                  <label for="register-nric">NRIC</label>
                  <input id="register-nric" type="text" name="nric" placeholder="Enter Your NRIC" required>
                </div>
                
                <div class="form-group">
                  <label for="register-contact">Contact Number</label>
                  <input id="register-contact" type="text" name="contact" placeholder="Enter Your Contact Number" required>
                </div>
                
                <button type="submit" class="step-btn submit-btn">Sign Up</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
