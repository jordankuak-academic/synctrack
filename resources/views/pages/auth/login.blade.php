@extends("layouts.xxxxx-layout")

@section("page-title", "Login")

@section("contents")
  @php
    $login = session("login_input", []);
    $sign_up = session("register_input", []);
  @endphp
  
  <div id="login-wrapper">
    <div class="login-card">
      <div class="left">
        <div class="left-brand">
          <img class="left-logo" src="{{ asset("images/system-logo.png") }}" alt="SyncTrack logo">
          <div class="left-copy">
            <h1 class="heading-01">SyncTrack</h1>
            <div class="left-copy-body">
              <p class="heading-04">Manage projects. Track tasks. Collaborate better.</p>
            </div>
          </div>
          <img class="left-banner" src="{{ asset("images/login-banner.png") }}" alt="SyncTrack collaboration illustration">
        </div>
      </div>
      
      <div class="right">
        <div class="nav-container">
          <div class="nav-item active" data-tab="login">
            <span class="label-m">Login</span>
          </div>
          <div class="nav-item" data-tab="sign-up">
            <span class="label-m">Sign Up</span>
          </div>
        </div>
        
        <div class="nav-content" data-content="login">
          <section class="login-container active">
            <h2 class="heading-01">Welcome Back</h2>
            
            <form action="{{ route("login.submit") }}" method="post">
              @csrf
              <div class="form-group">
                <label for="email" class="heading-04">Email</label>
                <input type="email" name="email" class="email-input" placeholder="you@example.com" value="{{ $login["email"] ?? "" }}" required>
              </div>
              
              <div class="form-group">
                <label for="password" class="heading-04">Password</label>
                <input id="login-password" type="password" name="password" class="password-input" placeholder="**********" required>
                <button type="button" id="login-password-toggle" class="password-toggle-btn" aria-label="Show password" aria-pressed="false">
                  <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                  <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                </button>
              </div>
              
              <div class="form-footer">
                <div class="form-action">
                  <button class="st-btn st-btn-primary st-btn-block">Sign In</button>
                </div>
              </div>
            </form>
          </section>
          
          <section class="sign-up-container" data-step="1">
            <h2 class="heading-01">Sign Up</h2>
            
            <form action="{{ route("register.submit") }}" method="post">
              @csrf
              <div id="step-1" class="sign-up-part active">
                <p class="step-title body-l">Step 1 of 2</p>
                
                <div class="form-group">
                  <label for="email" class="heading-04">Email</label>
                  <input type="email" name="email" class="email-input" placeholder="you@example.com" value="{{ $sign_up["email"] ?? "" }}" required>
                </div>
                
                <div class="form-group">
                  <label for="username" class="heading-04">Username</label>
                  <input type="text" name="username" class="username-input" placeholder="Enter Your Username" value="{{ $sign_up["username"] ?? "" }}" required>
                </div>
                
                <div class="row-group">
                  <div class="form-group">
                    <label for="password" class="heading-04">Password</label>
                    <input id="sign-up-password" type="password" name="password" class="password-input" placeholder="**********" required>
                    <button type="button" id="sign-up-password-toggle" class="password-toggle-btn" aria-label="Show password" aria-pressed="false">
                      <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                      <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                    </button>
                  </div>
                  
                  <div class="form-group">
                    <label for="confirm-password" class="heading-04">Confirm Password</label>
                    <input id="sign-up-confirm-password" type="password" name="confirm-password" class="confirm-password-input" placeholder="**********" required>
                    <button type="button" id="sign-up-confirm-password-toggle" class="password-toggle-btn" aria-label="Show password" aria-pressed="false">
                      <x-dynamic-component class="toggle-icon icon-eye" :component="'lucide-eye'" />
                      <x-dynamic-component class="toggle-icon icon-eye-off" :component="'lucide-eye-off'" />
                    </button>
                  </div>
                </div>
                
                <div class="form-footer">
                  <div class="form-action">
                    <button type="button" id="next-btn" class="st-btn st-btn-primary st-btn-block">Next</button>
                  </div>
                </div>
              </div>
              
              <div id="step-2" class="sign-up-part">
                <p class="step-title body-l">Step 2 of 2</p>
                
                <div class="form-group">
                  <label for="fullname" class="heading-04">Full Name</label>
                  <input type="text" name="fullname" class="fullname-input" placeholder="Enter Your Full Name" value="{{ $sign_up["fullname"] ?? "" }}" required>
                </div>
                
                <div class="form-group">
                  <label for="nric" class="heading-04">NRIC</label>
                  <input type="text" name="nric" class="nric-input" placeholder="Enter Your NRIC" value="{{ $sign_up["nric"] ?? "" }}" required>
                </div>
                
                <div class="form-group">
                  <label for="contact" class="heading-04">Contact Number</label>
                  <input type="text" name="contact" class="contact-input" placeholder="Enter Your Contact Number" value="{{ $sign_up["contact"] ?? "" }}" required>
                </div>
                
                <div class="form-footer">
                  <div class="row-group">
                    <div class="form-action">
                      <button type="button" id="back-btn" class="st-btn st-btn-secondary st-btn-block">Back</button>
                    </div>
                    
                    <div class="form-action">
                      <button type="submit" class="st-btn st-btn-primary st-btn-block">Create Account</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </div>
@endsection
