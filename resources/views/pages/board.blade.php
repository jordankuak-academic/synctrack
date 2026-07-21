@extends("layouts.hsxxx-layout")

@section("page-title", "Task Board")

@section("contents")
  @php
    $boardColumns = [
      "in_progress" => [
        "title" => "In Progress",
        "empty" => "No in-progress task available",
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
    $calendarSelectedDate = now()->toDateString();
    $calendarMonthStart = now()->startOfMonth();
    $calendarGridStart = $calendarMonthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $calendarDates = collect(range(0, 41))->map(fn($dayOffset) => $calendarGridStart->copy()->addDays($dayOffset));
    $todayDate = now()->toDateString();
    
  @endphp
  
  <div id="board-wrapper">
    <section class="board-content">
      <header class="page-header board-header">
        <div>
          <h1 class="heading-02">Task Board</h1>
        </div>
        
        <div class="task-view-switcher" aria-label="Task view selector">
          <button type="button" class="is-active" data-task-view="list" aria-pressed="true">
            <span class="task-view-list">List</span>
          </button>
          <button type="button" data-task-view="calendar" aria-pressed="false">
            <span class="task-view-calendar">Calendar</span>
          </button>
        </div>
      </header>
      <section id="task-list-view" class="board-task-list" aria-label="Board task status columns">
        @foreach ($boardColumns as $column => $columnConfig)
          @php
            $columnItems = collect($tasks[$column] ?? []);
          @endphp
          
          <section class="task-column {{ $column === "overdue" ? "task-column--system" : "" }}" data-column="{{ $column }}">
            <header class="task-column-header">
              <h2>{{ $columnConfig["title"] }}</h2>
              <span class="task-count" data-count-for="{{ $column }}">{{ $columnItems->count() }}</span>
            </header>
            
            <div class="task-card-container" data-card-container="{{ $column }}" @if ($columnItems->isEmpty()) hidden @endif>
              @foreach ($columnItems as $item)
                @php
                  $priority = $item["priority"] ?? "";
                  $priorityClass = $priority !== "" ? $priority : "none";
                  $priorityLabel = $priority !== "" ? ucfirst($priority) : "None";
                  $updateUrl = $item["updateUrl"];
                @endphp
                
                <article
                  class="task-card"
                  draggable="true"
                  data-item-id="{{ $item["id"] }}"
                  data-item-type="{{ $item["type"] }}"
                  data-task-key="{{ $item["type"] }}-{{ $item["id"] }}"
                  data-current-status="{{ $column }}"
                  data-original-status="{{ $column }}"
                  data-task-status="{{ $item["status"] }}"
                  data-title="{{ $item["title"] }}"
                  data-project-name="{{ $item["project_title"] }}"
                  data-parent-task="{{ $item["parent_title"] ?? "None" }}"
                  data-start-date="{{ $item["createdDate"] }}"
                  data-assignee-id="{{ $item["assignee_id"] }}"
                  data-due-date="{{ $item["dueDate"] }}"
                  data-priority="{{ $priority }}"
                  data-update-url="{{ $updateUrl }}"
                  tabindex="0"
                  role="button"
                  aria-label="Open details for {{ $item["title"] }}"
                >
                  <span class="task-status-indicator task-status-indicator--{{ $column }}"></span>
                  <div class="task-card-content">
                    <div class="task-card-meta">
                      <span class="task-type-badge">{{ $item["type"] === "subtask" ? "Subtask" : "Task" }}</span>
                      @if ($item["due_date"] !== null)
                        <span>{{ $item["due_date"]->format("M d") }}</span>
                      @endif
                    </div>
                    <h3>{{ $item["title"] }}</h3>
                    @if ($item["project_title"])
                      <p class="task-project">{{ $item["project_title"] }}</p>
                    @endif
                    @if ($item["parent_title"])
                      <p class="task-parent">{{ $item["parent_title"] }}</p>
                    @endif
                  </div>
                  <span class="priority-badge priority-badge--{{ $priorityClass }}">{{ $priorityLabel }}</span>
                </article>
              @endforeach
            </div>
            
            <div class="empty-state" data-empty-state="{{ $column }}" @if ($columnItems->isNotEmpty()) hidden @endif>{{ $columnConfig["empty"] }}</div>
          </section>
        @endforeach
      </section>
      
      <section id="task-calendar-view" class="task-calendar-view" aria-label="Task calendar" hidden>
        <div class="calendar-panel">
          <header class="calendar-header">
            <div class="calendar-actions">
              <button type="button" class="calendar-nav-button" data-calendar-previous aria-label="Previous month">&lt;</button>
              <button type="button" class="calendar-today-button" data-calendar-today>Today</button>
            </div>
            <h2 data-calendar-title>Calendar</h2>
            <button type="button" class="calendar-nav-button" data-calendar-next aria-label="Next month">&gt;</button>
          </header>
          
          <div class="calendar-weekdays" aria-hidden="true">
            <span>Sun</span>
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
          </div>
          
          <div
            class="calendar-grid"
            data-calendar-grid
            data-calendar-year="{{ $calendarMonthStart->year }}"
            data-calendar-month="{{ $calendarMonthStart->month - 1 }}"
            data-selected-date="{{ $calendarSelectedDate }}"
          >
            @foreach ($calendarDates as $calendarDate)
              @php
                $dateKey = $calendarDate->toDateString();
                $dayTasks = collect($calendarTasks)->filter(fn($item) => ($item["dueDate"] ?? $item["createdDate"] ?? null) === $dateKey)->values();
              @endphp
              
              <div
                class="calendar-day {{ !$calendarDate->isSameMonth($calendarMonthStart) ? "is-other-month" : "" }} {{ $dateKey === $todayDate ? "is-today" : "" }}"
                data-date="{{ $dateKey }}"
              >
                <div class="calendar-day-header">
                  <span>{{ $calendarDate->day }}</span>
                </div>
                <div class="calendar-task-list">
                  @foreach ($dayTasks as $item)
                    @php
                      $priority = $item["priority"] ?? "";
                  $priorityClass = $priority !== "" ? $priority : "none";
                  $priorityLabel = $priority !== "" ? ucfirst($priority) : "None";
                      $taskDate = $item["dueDate"] ?? $item["createdDate"];
                      $displayStatus = $item["status"] === "completed" ? "completed" : (($item["dueDate"] ?? null) !== null && $item["dueDate"] < $todayDate ? "overdue" : "in_progress");
                    @endphp
                    
                    <button
                      type="button"
                      class="calendar-task calendar-task--{{ $displayStatus }} calendar-task--priority-{{ $priorityClass }}"
                      data-calendar-task
                      data-task-key="{{ $item["type"] }}-{{ $item["id"] }}"
                      data-item-id="{{ $item["id"] }}"
                      data-item-type="{{ $item["type"] }}"
                      data-title="{{ $item["title"] }}"
                      data-project-name="{{ $item["project"] }}"
                      data-parent-task="{{ $item["parent"] ?? "None" }}"
                      data-start-date="{{ $item["createdDate"] }}"
                      data-due-date="{{ $item["dueDate"] }}"
                      data-task-date="{{ $taskDate }}"
                      data-priority="{{ $priority }}"
                      data-status="{{ $item["status"] }}"
                      data-assignee-id="{{ $item["assignee_id"] }}"
                      data-update-url="{{ $item["updateUrl"] }}"
                      title="{{ $item["title"] }}"
                      aria-label="Open details for {{ $item["title"] }}"
                    >
                      {{ $item["title"] }}
                    </button>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </section>
    </section>
    
    <script id="task-calendar-data" type="application/json">@json($calendarTasks)</script>
    
    <div class="task-detail-modal" data-task-detail-modal hidden>
      <div class="task-detail-backdrop" data-task-detail-close></div>
      <section class="task-detail-dialog" role="dialog" aria-modal="true" aria-labelledby="task-detail-title">
        <header class="task-detail-header">
          <div>
            <span class="task-detail-type" data-detail-type>Task</span>
            <h2 id="task-detail-title" data-detail-title>Task details</h2>
          </div>
          <button type="button" class="task-detail-close" data-task-detail-close aria-label="Close task details">&times;</button>
        </header>
        
        <div class="task-detail-body">
          <div class="task-detail-field">
            <span>Project Name</span>
            <p data-detail-project>-</p>
          </div>
          <div class="task-detail-field">
            <span>Parent Task</span>
            <p data-detail-parent>-</p>
          </div>
          <div class="task-detail-field">
            <span>Start Date</span>
            <p data-detail-start-date>-</p>
          </div>
          <div class="task-detail-field">
            <span>Due Date</span>
            <p data-detail-due-date>-</p>
          </div>
          <div class="task-detail-field">
            <span>Priority</span>
            <p data-detail-priority>-</p>
          </div>
          <div class="task-detail-field">
            <span>Status</span>
            <select class="task-detail-status-select" data-detail-status-control aria-label="Change task status">
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
