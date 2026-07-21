@extends("layouts.hsxxx-layout")

@section("page-title", "Dashboard")

@section("contents")
  @php
    $cards = [
      [
        "label" => "Owned Projects",
        "value" => $summary["owned_projects"],
        "meta" => "Created by you",
        "icon" => "folder-kanban",
        "tone" => "primary",
        "href" => route("project"),
      ],
      [
        "label" => "Member Projects",
        "value" => $summary["joined_projects"],
        "meta" => "Shared or assigned to you",
        "icon" => "users",
        "tone" => "neutral",
        "href" => route("project"),
      ],
      [
        "label" => "Completed Projects",
        "value" => $summary["completed_projects"],
        "meta" => "Open Project page",
        "icon" => "circle-check-big",
        "tone" => "success",
        "href" => route("project"),
      ],
      [
        "label" => "In Progress Projects",
        "value" => $summary["progressed_projects"],
        "meta" => "Open Project page",
        "icon" => "activity",
        "tone" => "warning",
        "href" => route("project"),
      ]
    ];
  @endphp
  
  <div id="dashboard-wrapper">
    <header class="page-header dashboard-header">
      <div>
        <h1 class="heading-02">Dashboard</h1>
      </div>
    </header>
    
    <div class="dashboard-content">
      <div class="analysis-card-container">
        @foreach ($cards as $card)
          <a class="analysis-card card-{{ $card["tone"] }}" href="{{ $card["href"] }}">
            <div class="card-icon">
              <x-dynamic-component :component="'lucide-'.$card['icon']" />
            </div>
            <span class="card-summary">{{ $card["label"] }}</span>
            <strong class="card-value">{{ $card["value"] }}</strong>
            <span class="card-meta">{{ $card["meta"] }}</span>
          </a>
        @endforeach
      </div>
      
      <div class="analysis-graph-panel-container">
        <div class="panel-header">
          <div class="panel-title">
            <h2 class="heading-04">Project Task Tracker</h2>
            <p class="body-s">Track member project contribution value</p>
          </div>
          
          <div class="panel-tool">
            <div class="filter">
              <label class="label-m" for="project-selector">Project</label>
              <select id="project-selector">
                @forelse ($analysis as $detail)
                  <option value="{{ $detail["project_id"] }}">{{ $detail["project_name"] }}</option>
                @empty
                  <option value="">No projects available</option>
                @endforelse
              </select>
            </div>
          </div>
        </div>
        
        <div class="panel-content">
          <div id="graph-container" aria-live="polite"></div>
          <div class="graph-empty-state">
            <x-dynamic-component class="graph-empty-icon" :component="'lucide-chart-bar-stacked'" />
            <p class="graph-empty heading-01">No Data Available</p>
          </div>
        </div>
      </div>
    </div>
    
    <script id="dashboard-analysis-data" type="application/json">@json($analysis)</script>
  </div>
@endsection

