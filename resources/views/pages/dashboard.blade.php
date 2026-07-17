@extends("layouts.hsxxx-layout")

@section("page-title", "Dashboard")

@section("contents")
  <div
    id="dashboard-wrapper"
    data-status-url-template="{{ route("task.status", ["id" => "__TASK_ID__"]) }}"
  >
    <section class="dashboard-content">
      <header class="dashboard-header">
        <h1>Task List</h1>
      </header>
      
      <section class="task-list" aria-label="Dashboard task status columns">
        <!-- In Progress Column -->
        <section class="task-column" data-column="in_progress">
          <header class="task-column-header">
            <h2>In Progress</h2>
            <span class="task-count" data-count-for="in_progress">{{ $tasks['in_progress']->count() }}</span>
          </header>
          
          <div class="task-card-container" data-card-container="in_progress" @if ($tasks['in_progress']->isEmpty()) hidden @endif>
            @foreach ($tasks['in_progress'] as $task)
              @php
                $priority = $task->priority ?? "unknown";
                $subTaskCount = $task->subTasks->count();
              @endphp
              
              <article
                class="task-card"
                draggable="true"
                data-task-id="{{ $task->id }}"
                data-current-status="in_progress"
                data-task-status="{{ $task->status }}"
                data-priority="{{ $priority }}"
                data-due-date="{{ $task->due_date?->toDateString() }}"
              >
                <span class="task-status-indicator task-status-indicator--in_progress"></span>
                <div class="task-card-content">
                  <h3>{{ $task->title }}</h3>
                  @if ($subTaskCount > 0)
                    <p class="task-subtasks">{{ $subTaskCount }} {{ \Illuminate\Support\Str::plural("subtask", $subTaskCount) }}</p>
                    <ul class="subtask-list">
                      @foreach ($task->subTasks as $subTask)
                        <li
                          class="subtask-item"
                          data-subtask-id="{{ $subTask->id }}"
                          data-subtask-status="{{ $subTask->status }}"
                          data-subtask-priority="{{ $subTask->priority ?? "unknown" }}"
                        >
                          {{ $subTask->title }}
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </div>
                <span class="priority-badge priority-badge--{{ $priority }}">{{ ucfirst($priority) }}</span>
              </article>
            @endforeach
          </div>
          
          <div class="empty-state" data-empty-state="in_progress" @if ($tasks['in_progress']->isNotEmpty()) hidden @endif>No in progress task available</div>
        </section>

        <!-- Completed Column -->
        <section class="task-column" data-column="completed">
          <header class="task-column-header">
            <h2>Completed</h2>
            <span class="task-count" data-count-for="completed">{{ $tasks['completed']->count() }}</span>
          </header>
          
          <div class="task-card-container" data-card-container="completed" @if ($tasks['completed']->isEmpty()) hidden @endif>
            @foreach ($tasks['completed'] as $task)
              @php
                $priority = $task->priority ?? "unknown";
                $subTaskCount = $task->subTasks->count();
              @endphp
              
              <article
                class="task-card"
                draggable="true"
                data-task-id="{{ $task->id }}"
                data-current-status="completed"
                data-task-status="{{ $task->status }}"
                data-priority="{{ $priority }}"
                data-due-date="{{ $task->due_date?->toDateString() }}"
              >
                <span class="task-status-indicator task-status-indicator--completed"></span>
                <div class="task-card-content">
                  <h3>{{ $task->title }}</h3>
                  @if ($subTaskCount > 0)
                    <p class="task-subtasks">{{ $subTaskCount }} {{ \Illuminate\Support\Str::plural("subtask", $subTaskCount) }}</p>
                    <ul class="subtask-list">
                      @foreach ($task->subTasks as $subTask)
                        <li
                          class="subtask-item"
                          data-subtask-id="{{ $subTask->id }}"
                          data-subtask-status="{{ $subTask->status }}"
                          data-subtask-priority="{{ $subTask->priority ?? "unknown" }}"
                        >
                          {{ $subTask->title }}
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </div>
                <span class="priority-badge priority-badge--{{ $priority }}">{{ ucfirst($priority) }}</span>
              </article>
            @endforeach
          </div>
          
          <div class="empty-state" data-empty-state="completed" @if ($tasks['completed']->isNotEmpty()) hidden @endif>No completed task available</div>
        </section>

        <!-- Overdue Column -->
        <section class="task-column task-column--system" data-column="overdue">
          <header class="task-column-header">
            <h2>Overdue</h2>
            <span class="task-count" data-count-for="overdue">{{ $tasks['overdue']->count() }}</span>
          </header>
          
          <div class="task-card-container" data-card-container="overdue" @if ($tasks['overdue']->isEmpty()) hidden @endif>
            @foreach ($tasks['overdue'] as $task)
              @php
                $priority = $task->priority ?? "unknown";
                $subTaskCount = $task->subTasks->count();
              @endphp
              
              <article
                class="task-card"
                draggable="true"
                data-task-id="{{ $task->id }}"
                data-current-status="overdue"
                data-task-status="{{ $task->status }}"
                data-priority="{{ $priority }}"
                data-due-date="{{ $task->due_date?->toDateString() }}"
              >
                <span class="task-status-indicator task-status-indicator--overdue"></span>
                <div class="task-card-content">
                  <h3>{{ $task->title }}</h3>
                  @if ($subTaskCount > 0)
                    <p class="task-subtasks">{{ $subTaskCount }} {{ \Illuminate\Support\Str::plural("subtask", $subTaskCount) }}</p>
                    <ul class="subtask-list">
                      @foreach ($task->subTasks as $subTask)
                        <li
                          class="subtask-item"
                          data-subtask-id="{{ $subTask->id }}"
                          data-subtask-status="{{ $subTask->status }}"
                          data-subtask-priority="{{ $subTask->priority ?? "unknown" }}"
                        >
                          {{ $subTask->title }}
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </div>
                <span class="priority-badge priority-badge--{{ $priority }}">{{ ucfirst($priority) }}</span>
              </article>
            @endforeach
          </div>
          
          <div class="empty-state" data-empty-state="overdue" @if ($tasks['overdue']->isNotEmpty()) hidden @endif>No overdue task available</div>
        </section>
      </section>
      
      <section class="dashboard-future-content" aria-label="Future dashboard content"></section>
    </section>
  </div>
@endsection
