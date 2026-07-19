<div class="st-sidemenu-wrapper">
  @php
    // Visit "https://lucide.dev/icons/" for more icons.
    
    $items = [
      [
        "route_link" => "/dashboard",
        "route_name" => "dashboard",
        "menu_title" => "Dashboard",
        "lucide_icon" => "layout-dashboard"
      ],
      [
        "route_link" => "/board",
        "route_name" => "board",
        "menu_title" => "Task Board",
        "lucide_icon" => "clipboard-list"
      ],
      [
        "route_link" => "/project",
        "route_name" => "project",
        "menu_title" => "Projects",
        "lucide_icon" => "folder-bookmark"
      ],
    ];
  @endphp
  
  <nav class="sidemenu">
    @foreach ($items as $item)
      <a href="{{ url($item["route_link"]) }}" class="nav-item {{ request()->routeIs($item["route_name"]) ? "active" : "" }}">
        <x-dynamic-component class="menu-icon" :component="'lucide-' . $item['lucide_icon']" />
        <span class="menu-title body-s">{{ $item["menu_title"] }}</span>
      </a>
    @endforeach
    
    <form action="{{ route("logout.submit") }}" method="post" class="logout-form">
      @csrf
      <button type="submit" class="logout-menu">
        <x-dynamic-component class="danger-menu" :component="'lucide-log-out'" />
        <span class="logout-title body-s">Logout</span>
      </button>
    </form>
  </nav>
</div>