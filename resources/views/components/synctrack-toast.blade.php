@php
  $response = session("response");
  $has_toast = is_array($response) && filled($response["message"] ?? null);
  $is_success = (bool) ($response["status"] ?? false);
@endphp

@if($has_toast)
  <div class="toast-wrapper {{ $is_success ? "is-success" : "is-error" }}" role="{{ $is_success ? "status" : "alert" }}" aria-live="polite" aria-atomic="true">
    <span class="toast-message">{{ $response["message"] }}</span>
  </div>
@endif