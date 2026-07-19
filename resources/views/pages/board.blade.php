@extends("layouts.hsxxx-layout")

@section("page-title", "Task Board")

@section("contents")
  @php
    $board_columns = [
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
  
  <div id="board-wrapper">
    <section class="dashboard-content">
      <header class="dashboard-header">
        <h1 class="heading-01">Task List</h1>
      </header>
      
      <section class="task-list" aria-label="Board task status columns">
        @foreach ($board_columns as $status => $column_config)
          @php
            $column_items = collect($tasks[$status] ?? []);
          @endphp
          
          <section class="task-column {{ $status === "overdue" ? "task-column--system" : "" }}" id="board-column-{{ $status }}">
            <header class="task-column-header">
              <h2 class="heading-04">{{ $column_config["title"] }}</h2>
              <span class="task-count" id="board-count-{{ $status }}">{{ $column_items->count() }}</span>
            </header>
            
            <div class="task-card-container" id="board-card-container-{{ $status }}" @if ($column_items->isEmpty()) hidden @endif>
              @foreach ($column_items as $task)
                @php
                  $priority = $task["priority"] ?? "medium";
                  $is_subtask = filled($task["parent_title"] ?? null);
                  $update_route = $is_subtask ? route("subtask.update", ["id" => $task["id"]]) : route("task.update", ["id" => $task["id"]]);
                @endphp
                
                <article class="task-card" draggable="true">
                  <form class="task-update-form" action="{{ $update_route }}" method="post">
                    @csrf
                    @method("PUT")
                    <input type="hidden" name="assignee_id" value="{{ $task["assignee_id"] }}">
                    <input type="hidden" name="title" value="{{ $task["title"] }}">
                    <input type="hidden" name="due_date" value="{{ $task["due_date"]?->toDateString() }}">
                    <input type="hidden" name="priority" value="{{ $priority }}">
                    <input type="hidden" name="status" value="{{ $task["status"] }}">
                  </form>
                  
                  <span class="task-status-indicator task-status-indicator--{{ $status }}"></span>
                  
                  <div class="task-card-content">
                    <div class="task-card-meta">
                      <span class="task-type-badge">{{ $is_subtask ? "Subtask" : "Task" }}</span>
                      @if (filled($task["project_title"] ?? null))
                        <span class="body-s">{{ $task["project_title"] }}</span>
                      @endif
                      @if ($is_subtask)
                        <span class="body-s">{{ $task["parent_title"] }}</span>
                      @endif
                    </div>
                    <p class="body-s">{{ $task["title"] }}</p>
                  </div>
                  
                  <span class="priority-badge priority-badge--{{ $priority }}">{{ ucfirst($priority) }}</span>
                </article>
              @endforeach
            </div>
            
            <div class="empty-state body-l" id="board-empty-{{ $status }}" @if ($column_items->isNotEmpty()) hidden @endif>{{ $column_config["empty"] }}</div>
          </section>
        @endforeach
      </section>
      
      <section class="dashboard-future-content" aria-hidden="true"></section>
    </section>
  </div>
@endsection