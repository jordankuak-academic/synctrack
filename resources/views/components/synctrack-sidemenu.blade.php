<div class="sidemenu-wrapper">
  @php
    // Visit "https://lucide.dev/icons/" for more icons.
    
    $items = [
      [
        "route_link" => "/project",
        "route_name" => "project",
        "menu_title" => "Project",
        "lucide_icon" => "chart-no-axes-gantt"
      ],
    ];
  @endphp
  
  <nav class="sidemenu">
    @foreach ($items as $item)
      <a href="{{ url($item["route_link"]) }}" class="nav-item {{ request()->routeIs($item["route_name"]) ? "active" : "" }}">
        <x-dynamic-component class="menu-icon" :component="'lucide-' . $item['lucide_icon']" />
        <span class="menu-title">{{ $item["menu_title"] }}</span>
      </a>
    @endforeach
  </nav>
</div>