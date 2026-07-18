@extends("layouts.hsxxx-layout")

@section("page-title", "Dashboard")

@section("contents")
  @php
    $dashboardColumns = [
      "in_progress" => [
        "title" => "In Progress",
        "empty" => "No in progress task available",
      ],
      "completed" => [
        "title" => "Completed",
        "empty" => "No completed task available",
      ],
      "overdue" => [
        "title" => "Overdue",
        "empty" => "No overdue task available",
      ],
    ];
  @endphp
  
  <div id="dashboard-wrapper">
    <section class="dashboard-content">
      <header class="dashboard-header">
        <h1 class="heading-01">Task List</h1>
      </header>
      
      <section class="task-list" aria-label="Dashboard task status columns">
        @foreach ($dashboardColumns as $column => $columnConfig)
          @php
            $columnItems = collect($tasks[$column] ?? []);
          @endphp
          
          <section class="task-column {{ $column === "overdue" ? "task-column--system" : "" }}" data-column="{{ $column }}">
            <header class="task-column-header">
              <h2 class="heading-04">{{ $columnConfig["title"] }}</h2>
              <span class="task-count" data-count-for="{{ $column }}">{{ $columnItems->count() }}</span>
            </header>
            
            <div class="task-card-container" data-card-container="{{ $column }}" @if ($columnItems->isEmpty()) hidden @endif>
              @foreach ($columnItems as $item)
                @php
                  $priority = $item["priority"] ?? "medium";
                  $updateUrl = $item["type"] === "subtask"
                    ? route("subtask.update", ["id" => $item["id"]])
                    : route("task.update", ["id" => $item["id"]]);
                @endphp
                
                <article
                  class="task-card"
                  draggable="true"
                  data-item-id="{{ $item["id"] }}"
                  data-item-type="{{ $item["type"] }}"
                  data-current-status="{{ $column }}"
                  data-original-status="{{ $column }}"
                  data-task-status="{{ $item["status"] }}"
                  data-title="{{ $item["title"] }}"
                  data-assignee-id="{{ $item["assignee_id"] }}"
                  data-due-date="{{ $item["due_date"]?->toDateString() }}"
                  data-priority="{{ $priority }}"
                  data-update-url="{{ $updateUrl }}"
                >
                  <span class="task-status-indicator task-status-indicator--{{ $column }}"></span>
                  <div class="task-card-content">
                    <div class="task-card-meta">
                    <p class="body-s">{{ $item["title"] }}</p>
                  </div>
                  <span class="priority-badge priority-badge--{{ $priority }}">{{ ucfirst($priority) }}</span>
                </article>
              @endforeach
            </div>
            
            <div class="empty-state body-L" data-empty-state="{{ $column }}" @if ($columnItems->isNotEmpty()) hidden @endif>{{ $columnConfig["empty"] }}</div>
          </section>
        @endforeach
      </section>
      
      <section class="dashboard-future-content" aria-label="Future dashboard content"></section>
    </section>
  </div>
@endsection
