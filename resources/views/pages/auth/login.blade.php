@extends("layouts.xxxxx-layout")

@section("page-title", "Login")

@section("contents")
  <div id="login-wrapper">
    <h1>Login</h1>
    <p>Please sign in with your registered account.</p>

    @if ($errors->any())
      <div>
        <p>Unable to login. Please check the information below:</p>
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route("login.submit") }}" method="post">
      @csrf

      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old("email") }}" autocomplete="email" required>
      </div>

      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>

      <input type="hidden" name="is_remember" value="0">

      <div>
        <input type="checkbox" id="is_remember" name="is_remember" value="1" {{ old("is_remember") ? "checked" : "" }}>
        <label for="is_remember">Remember me</label>
      </div>

      <div>
        <button type="submit">Login</button>
      </div>
  </div>
@endsection