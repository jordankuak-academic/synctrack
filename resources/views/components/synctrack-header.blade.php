<div class="st-header-wrapper">
  <div class="logo-wrapper">
    <a href="{{ url("/project") }}" class="logo heading-02">SyncTrack</a>
  </div>
  
  @php
    $username = auth()->user()->username;
  @endphp
  
  <div class="avatar-wrapper">
    <div class="heading-04">{{ strtoupper(substr($username ?? "A", 0, 1)) }}</div>
  </div>
</div>