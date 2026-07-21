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
        "value" => $summary["member_projects"],
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
        <h1 class="heading-02">Dashboard</h1>
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

      <section class="dashboard-panel graph-panel" aria-labelledby="member-task-graph-title">
        <div class="panel-heading graph-heading">
          <div>
            <h2 id="member-task-graph-title" class="heading-04">Member Task Count</h2>
            <p class="body-s">Line graph showing task and subtask assignments for the selected project.</p>
          </div>

          <label class="project-select-field" for="dashboard-project-select">
            <span>Project</span>
            <select id="dashboard-project-select" data-project-stat-select @disabled(empty($projectMemberTaskStats))>
              @forelse ($projectMemberTaskStats as $project)
                <option value="{{ $project["project_id"] }}" data-members='@json($project["members"], JSON_HEX_APOS)'>{{ $project["project_name"] }}</option>
              @empty
                <option>No project available</option>
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