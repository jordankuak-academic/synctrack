@extends("layouts.hsxxx-layout")

@section("page-title", "Profile")

@section("contents")
  @php
    $initialMode = session("profile_mode", $errors->any() ? "edit" : "view");
  @endphp
  <div id="profile-wrapper" data-initial-mode="{{ $initialMode }}">
    <div class="profile-content">
      <header class="page-header profile-header">
        <h1 class="heading-02">Profile</h1>
        <div class="profile-actions">
          <button class="profile-button profile-button--primary" type="button" data-action="edit">Edit</button>
          <a class="profile-button profile-button--secondary" href="{{ route('profile.show') }}" data-edit-back hidden>Back</a>
          <button class="profile-button profile-button--primary" type="submit" form="profile-form" data-action="save-profile" hidden>Save Changes</button>
        </div>
      </header>

      @if (session("profile_success") || session("profile_error"))
        <div class="profile-notification profile-notification--{{ session('profile_error') ? 'error' : 'success' }}" role="alert" aria-live="polite" data-notification>
          <span>{{ session("profile_error") ?? session("profile_success") }}</span>
          <button type="button" aria-label="Close notification" data-dismiss-notification>&times;</button>
        </div>
      @endif

      <form id="profile-form" method="POST" action="{{ route('profile.update') }}" novalidate>
        @csrf
        @method("PUT")
        <div class="profile-grid">
          <section class="profile-card" aria-labelledby="login-info-title">
            <h2 id="login-info-title" class="heading-04">Login Info</h2>
            <div class="login-fields">
              <div class="profile-field">
                <span class="profile-label">Email Address</span>
                <div class="profile-value">{{ $user->email }}</div>
                <input name="email" type="hidden" value="{{ $user->email }}">
                @error("email") <p class="profile-error">{{ $message }}</p> @enderror
              </div>
              <div class="profile-field">
                <span class="profile-label">Password</span>
                <div class="profile-value" aria-label="Password is hidden">************</div>
              </div>
            </div>
          </section>

          <section class="profile-card" aria-labelledby="personal-info-title">
            <h2 id="personal-info-title" class="heading-04">Personal Info</h2>
            <div class="personal-fields">
              @foreach (["username" => ["Username", "username"], "fullname" => ["Fullname", "name"], "nric" => ["NRIC", "off"], "contact" => ["Contact", "tel"]] as $field => [$label, $autocomplete])
                <div class="profile-field">
                  <label for="{{ $field }}">{{ $label }}</label>
                  <div class="profile-value" data-view-value="{{ $field }}">{{ $user->{$field} }}</div>
                  <input id="{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field, $user->{$field}) }}" autocomplete="{{ $autocomplete }}" data-edit-input hidden>
                  @error($field) <p class="profile-error">{{ $message }}</p> @enderror
                </div>
              @endforeach
            </div>
          </section>
        </div>
      </form>

      <section class="profile-card security-card" aria-labelledby="security-title">
        <div>
          <h2 id="security-title" class="heading-04">Security</h2>
          <p>Keep your account secure by updating your password regularly.</p>
        </div>
        <button class="profile-button profile-button--primary" type="button" data-action="password">Change Password</button>
      </section>

      <div class="password-modal" data-password-panel hidden>
        <section class="profile-card password-panel" role="dialog" aria-modal="true" aria-labelledby="password-title">
          <header class="password-modal-header">
            <h2 id="password-title" class="heading-04">Change Password</h2>
            <button type="button" aria-label="Close change password" data-action="cancel-password">&times;</button>
          </header>
          <form method="POST" action="{{ route('profile.password.update') }}" data-password-form novalidate>
            @csrf
            @method("PUT")
          <div class="password-modal-content">
            <p class="password-description">Enter and confirm your new password.</p>
            <div class="password-fields">
            <div class="profile-field password-input">
              <label for="new_password">New Password</label>
              <input id="new_password" name="new_password" type="password" required minlength="8" autocomplete="new-password">
              <button type="button" aria-label="Show password" aria-pressed="false" data-password-toggle data-target="new_password">
                <x-lucide-eye class="icon-eye" aria-hidden="true" />
                <x-lucide-eye-off class="icon-eye-off" aria-hidden="true" />
              </button>
              @error("new_password") <p class="profile-error">{{ $message }}</p> @enderror
            </div>
            <div class="profile-field password-input">
              <label for="new_password_confirmation">Re-enter New Password</label>
              <input id="new_password_confirmation" name="new_password_confirmation" type="password" required minlength="8" autocomplete="new-password">
              <button type="button" aria-label="Show password" aria-pressed="false" data-password-toggle data-target="new_password_confirmation">
                <x-lucide-eye class="icon-eye" aria-hidden="true" />
                <x-lucide-eye-off class="icon-eye-off" aria-hidden="true" />
              </button>
            </div>
          </div>
          </div>
          <div class="password-actions">
            <button class="profile-button profile-button--secondary" type="button" data-action="cancel-password">Back</button>
            <button class="profile-button profile-button--primary" type="submit">Save Password</button>
          </div>
          </form>
        </section>
      </div>
    </div>
  </div>
@endsection



