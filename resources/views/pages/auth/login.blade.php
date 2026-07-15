@extends("layouts.xxxxx-layout")

@section("page-title", "Login")

@section("contents")
  <div id="login-wrapper">
    <section class="login-card" aria-labelledby="login-title">
      <aside class="login-card__brand" aria-label="SyncTrack">
        <div class="login-card__logo" aria-hidden="true"></div>
        <p>SyncTrack</p>
      </aside>

      <div class="login-card__content">
        <div class="login-card__tabs" role="tablist" aria-label="Authentication options">
          <button class="login-card__tab login-card__tab--active" type="button" role="tab" aria-selected="true" aria-controls="login-panel" data-auth-tab="login">Log In</button>
          <button class="login-card__tab" type="button" role="tab" aria-selected="false" aria-controls="signup-panel" data-auth-tab="signup">Sign Up</button>
        </div>  

        <div class="login-card__forms">
          <form id="login-panel" class="login-form" action="{{ route('login.submit') }}" method="POST" role="tabpanel" data-auth-panel="login" autocomplete="off">
            @csrf
            <h1 id="login-title" class="display-l">Log In</h1>

            <div class="auth-form__field">
              <label for="login-email">Email</label>
              <input id="login-email" name="email" type="email" placeholder="you@example.com" autocomplete="off" required>
            </div>

            <div class="auth-form__field">
              <label for="login-password">Password</label>
              <input id="login-password" name="password" type="password" placeholder="*******************" autocomplete="new-password" required>
            </div>

            <input name="is_remember" type="hidden" value="0">
          </form>

          <form id="signup-panel" class="signup-form" action="{{ route('register.submit') }}" method="POST" role="tabpanel" data-auth-panel="signup" hidden>
            @csrf
            <div class="signup-form__step" data-signup-step="1">
              <h1>Sign Up</h1>
              <p class="signup-form__subtitle">Step 1 : Create Your Account</p>

              <div class="auth-form__field">
                <label for="signup-email">Email</label>
                <input id="signup-email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required>
              </div>

              <div class="auth-form__field">
                <label for="signup-username">Username</label>
                <input id="signup-username" name="username" type="text" placeholder="Enter your username" autocomplete="username" required>
              </div>

              <div class="signup-form__passwords">
                <div class="auth-form__field">
                  <label for="signup-password">Password</label>
                  <input id="signup-password" name="password" type="password" placeholder="Enter password" autocomplete="new-password" minlength="8" required>
                </div>

                <div class="auth-form__field">
                  <label for="signup-password-confirmation">Confirm Password</label>
                  <input id="signup-password-confirmation" name="password_confirmation" type="password" placeholder="Confirm password" autocomplete="new-password" minlength="8" required>
                </div>
              </div>
            </div>

            <div class="signup-form__step" data-signup-step="2" hidden>
              <h1>Sign Up</h1>
              <p class="signup-form__subtitle">Step 2 : Personal Information</p>

              <div class="auth-form__field">
                <label for="signup-fullname">Full Name</label>
                <input id="signup-fullname" name="fullname" type="text" placeholder="Enter your full name" autocomplete="name" required>
              </div>

              <div class="auth-form__field">
                <label for="signup-nric">NRIC</label>
                <input id="signup-nric" name="nric" type="text" placeholder="Enter your NRIC" required>
              </div>

              <div class="auth-form__field">
                <label for="signup-contact">Contact Number</label>
                <input id="signup-contact" name="contact" type="tel" placeholder="Enter your contact number" autocomplete="tel" required>
              </div>
            </div>
          </form>
        </div>

        <div class="login-card__submit-area">
          <button class="auth-form__submit" type="submit" form="login-panel" data-auth-submit="login">Log In</button>
          <button class="auth-form__submit" type="button" data-auth-submit="signup" data-signup-submit="1" data-signup-next hidden>Next</button>
          <button class="auth-form__submit" type="submit" form="signup-panel" data-auth-submit="signup" data-signup-submit="2" hidden>Sign Up</button>
        </div>
      </div>
    </section>
  </div>
@endsection