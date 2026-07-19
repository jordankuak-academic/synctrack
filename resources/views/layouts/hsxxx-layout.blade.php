<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config("app.name", "SyncTrack") }} @hasSection("page-title") - @yield("page-title") @endif</title>
    
    @vite(["resources/styles/app.scss"])
  </head>
  
  <body class="st-hsxxx-layout">
    <x-synctrack-toast />
    
    <header>
      <x-synctrack-header />
    </header>
    
    <aside>
      <x-synctrack-sidemenu />
    </aside>
    
    <main>
      @yield("contents")
    </main>
    
    @vite(["resources/scripts/app.ts"])
  </body>
</html>