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
        "href" => null,
      ],
      [
        "label" => "Member Projects",
        "value" => $summary["member_projects"],
        "meta" => "Shared or assigned to you",
        "icon" => "users",
        "tone" => "neutral",
        "href" => null,
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
        "value" => $summary["in_progress_projects"],
        "meta" => "Open Project page",
        "icon" => "activity",
        "tone" => "warning",
        "href" => route("project"),
      ],
    ];
  @endphp

  <div id="dashboard-wrapper">
    <header class="page-header dashboard-header">
      <div>
        <h1 class="heading-01">Dashboard</h1>
        <p class="body-s">Project overview and member task distribution.</p>
      </div>
    </header>

    <section class="dashboard-content" aria-label="Project analysis dashboard">
      <section class="dashboard-summary-grid" aria-label="Project summaries">
        @foreach ($cards as $card)
          @if ($card["href"])
            <a class="dashboard-summary-card dashboard-summary-card--{{ $card["tone"] }}" href="{{ $card["href"] }}">
          @else
            <article class="dashboard-summary-card dashboard-summary-card--{{ $card["tone"] }}">
          @endif
              <span class="summary-icon" aria-hidden="true">
                <x-dynamic-component :component="'lucide-' . $card['icon']" />
              </span>
              <span class="summary-label">{{ $card["label"] }}</span>
              <strong class="summary-value">{{ $card["value"] }}</strong>
              <span class="summary-meta">{{ $card["meta"] }}</span>
          @if ($card["href"])
            </a>
          @else
            </article>
          @endif
        @endforeach
      </section>

      <section class="dashboard-overview-grid">
        <article class="dashboard-panel project-count-panel">
          <div class="panel-heading">
            <div>
              <h2 class="heading-04">Project Count Summary</h2>
              <p class="body-s">Projects are scoped to work you own, joined, or are assigned to.</p>
            </div>
            <strong>{{ $summary["total_projects"] }}</strong>
          </div>

          <div class="project-ratio-list">
            <div>
              <span>Owned Projects</span>
              <strong>{{ $summary["owned_projects"] }}</strong>
            </div>
            <div>
              <span>Member Projects</span>
              <strong>{{ $summary["member_projects"] }}</strong>
            </div>
          </div>
        </article>

        <article class="dashboard-panel project-status-panel">
          <div class="panel-heading">
            <div>
              <h2 class="heading-04">Project Status Summary</h2>
              <p class="body-s">Completed projects have all tasks and subtasks marked completed.</p>
            </div>
          </div>

          <div class="status-summary-list">
            <a href="{{ route("project") }}" class="status-summary-card status-summary-card--completed">
              <span>Completed</span>
              <strong>{{ $summary["completed_projects"] }}</strong>
            </a>
            <a href="{{ route("project") }}" class="status-summary-card status-summary-card--progress">
              <span>In Progress</span>
              <strong>{{ $summary["in_progress_projects"] }}</strong>
            </a>
          </div>
        </article>
      </section>

      <section class="dashboard-panel graph-panel" aria-labelledby="member-task-graph-title">
        <div class="panel-heading graph-heading">
          <div>
            <h2 id="member-task-graph-title" class="heading-04">Member Task Count</h2>
            <p class="body-s">Line graph showing parent task assignments for the selected project.</p>
          </div>

          <label class="project-select-field" for="dashboard-project-select">
            <span>Project</span>
            <select id="dashboard-project-select" data-project-stat-select @disabled(empty($projectMemberTaskStats))>
              @forelse ($projectMemberTaskStats as $project)
                <option value="{{ $project["project_id"] }}">{{ $project["project_name"] }}</option>
              @empty
                <option>No project data</option>
              @endforelse
            </select>
          </label>
        </div>

        <div class="graph-shell">
          <div class="line-graph" data-member-task-graph role="img" aria-label="Member task count line graph"></div>
          <p class="graph-empty" data-graph-empty hidden>No task data available for this project.</p>
        </div>
      </section>
    </section>

    <script id="dashboard-project-task-stats" type="application/json">@json($projectMemberTaskStats)</script>
  </div>
@endsection