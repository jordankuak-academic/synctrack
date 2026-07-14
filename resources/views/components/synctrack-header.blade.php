<div class="header-wrapper">
  <div class="logo-wrapper">
    <a href="{{ url("/project") }}" class="logo">SyncTrack</a>
  </div>
  
  @php
    $username = auth()->user()->username;
  @endphp
  
  <div class="avatar-wrapper">
    <div class="avatar">{{ strtoupper(substr($username ?? "A", 0, 1)) }}</div>
  </div>
</div>