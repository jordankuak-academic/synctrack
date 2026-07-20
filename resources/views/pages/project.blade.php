@extends("layouts.hsxxx-layout")

@section("page-title", "Project")

@section("contents")
  @php
    $completedProjects = collect($projects['completed'] ?? []);
    $onProgressProjects = collect($projects['on_progress'] ?? []);
    $allProjects = $completedProjects->concat($onProgressProjects)->values();
    $projectCount = $completedProjects->count() + $onProgressProjects->count();
  @endphp
  
  <div id="project-wrapper">
    <header class="page-header project-header">
      <h1 class="heading-01">Project List</h1>
      
      <div id="header-default-actions" class="header-actions active">
        <button id="create-project-btn" class="btn primary-btn">Create Project</button>
      </div>
      <div id="header-create-actions" class="header-actions">
        <button id="cancel-project-btn" class="btn secondary-btn">Cancel</button>
        <button id="save-project-btn" class="btn primary-btn">Create</button>
      </div>
      <div id="header-project-detail-actions" class="header-actions">
        <button id="delete-project-btn" class="btn danger-btn">Delete</button>
        <button id="edit-project-btn" class="btn primary-btn">Edit</button>
      </div>
      <div id="header-project-edit-actions" class="header-actions">
        <button id="cancel-project-edit-btn" class="btn secondary-btn">Cancel</button>
        <button id="save-project-edit-btn" class="btn primary-btn">Save</button>
      </div>
    </header>
    <div class="project-body">
      <aside class="project-list-card">
        <div class="card-header">
          <h2 class="heading-04">Projects</h2>
          <span class="project-count helper-text">{{ $projectCount }}</span>
        </div>
        
        <div class="card-content">
          <div id="project-empty-list" class="project-empty active{{ $projectCount > 0 ? ' has-projects' : '' }}">
            <div class="project-status-group expanded">
              <button type="button" class="project-status-heading" data-project-group="on-progress" aria-expanded="true">
                <span class="status-chevron" aria-hidden="true"></span>
                <span class="body-l">On Progress</span>
                <span id="on-progress-count" class="status-count helper-text">{{ $onProgressProjects->count() }}</span>
              </button>
              <div id="on-progress-projects" class="status-project-list">
                @forelse ($onProgressProjects as $project)
                  <button type="button" class="project-item" data-index="{{ $completedProjects->count() + $loop->index }}" data-project-id="{{ $project['id'] }}">
                    <span class="project-name body-s">{{ $project['title'] }}</span>
                  </button>
                @empty
                  <span class="project-group-empty helper-text">No project available.</span>
                @endforelse
              </div>
            </div>
            
            <div class="project-status-divider" />
            
            <div class="project-status-group">
              <button type="button" class="project-status-heading" data-project-group="completed" aria-expanded="false">
                <span class="status-chevron" aria-hidden="true"></span>
                <span class="body-l">Completed</span>
                <span id="completed-count" class="status-count helper-text">{{ $completedProjects->count() }}</span>
              </button>
              <div id="completed-projects" class="status-project-list">
                @forelse ($completedProjects as $project)
                  <button type="button" class="project-item" data-index="{{ $loop->index }}" data-project-id="{{ $project['id'] }}">
                    <span class="project-name body-s">{{ $project['title'] }}</span>
                  </button>
                @empty
                  <span class="project-group-empty helper-text">No completed project.</span>
                @endforelse
              </div>
            </div>
          </div>
          <div id="project-item-list" class="project-items"></div>
        </div>
      </aside>
      
      <section class="project-content-card">
        <div id="state-empty" class="content-state active">
          <div class="card-header">
            <h2 class="heading-04">{{ $projectCount === 0 ? 'No Project Available' : 'Select a Project' }}</h2>
          </div>
          <div class="card-body empty-center">
            <p class="body-l">{{ $projectCount === 0 ? 'No project available.' : 'Select a project from the left to view tasks.' }}</p>
          </div>
        </div>
        
        <div id="state-create-project" class="content-state">
          <div class="card-header">
            <h2 class="heading-04">Create Project</h2>
          </div>
          <div class="card-body">
            <form id="project-form" action="{{ route('project.store') }}" method="POST" autocomplete="off">
              @csrf
              <div class="form-group">
                <label class="label-m" for="project-name">Project Name</label>
                <input id="project-name" name="title" type="text" maxlength="100" required placeholder="Project Name">
              </div>
              
              <div class="form-group">
                <label class="label-m" for="project-description">Description</label>
                <textarea id="project-description" name="description" rows="5" maxlength="255" required
                  placeholder="Description of project"></textarea>
              </div>
            </form>
          </div>
        </div>
        
        <div id="state-project" class="content-state">
          <div class="card-header">
            <h2 id="task-project-title" class="heading-04">Project Name</h2>
            <button type="button" class="task-settings-button" aria-label="Task settings">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.94 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.57 15 1.7 1.7 0 0 0 3 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.57 1.7 1.7 0 0 0 10 3h4v.08a1.7 1.7 0 0 0 1.06 1.52 1.7 1.7 0 0 0 1.88-.34L17 4.2 19.83 7l-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 21 10h.08v4H21a1.7 1.7 0 0 0-1.6 1Z" />
              </svg>
            </button>
          </div>
          
          <div class="task-body">
            <div class="task-header">
              <div class="status-column helper-text">Status</div>
              <div class="task-name-column helper-text">Task Name</div>
              <div class="assigned-column helper-text">Assigned</div>
              <div class="date-column helper-text">Due Date</div>
              <div class="priority-column helper-text">Priority</div>
              <div class="action-column helper-text">Action</div>
            </div>
            
            <div id="project-task-panels">
              @foreach ($allProjects as $project)
                <div class="project-task-panel" data-project-id="{{ $project['id'] }}" data-project-title="{{ $project['title'] }}" data-project-description="{{ $project['description'] ?? '' }}" hidden>
                  <div class="task-list" data-task-list>
                    @foreach ($project['tasks'] ?? [] as $task)
                      @php
                        $subTasks = $task['sub_tasks'] ?? [];
                        $taskStatus = ['draft' => 'assigned', 'in_progress' => 'in-progress', 'completed' => 'done'][$task['status'] ?? 'draft'] ?? 'assigned';
                        $taskStatusLabel = ['assigned' => 'Draft', 'in-progress' => 'In Progress', 'done' => 'Completed'][$taskStatus];
                        $taskPriority = ucfirst($task['priority'] ?? '');
                        $assignee = collect($project['members'] ?? [])->firstWhere('id', $task['assignee_id'] ?? null);
                        $completedSubTasks = collect($subTasks)->where('status', 'completed')->count();
                      @endphp
                      <div class="task-row" data-task-id="{{ $task['id'] }}" data-task-title="{{ $task['title'] }}" data-assignee-id="{{ $task['assignee_id'] ?? '' }}" data-assignee-name="{{ $assignee['username'] ?? '' }}" data-due-date="{{ !empty($task['due_date']) ? substr($task['due_date'], 0, 10) : '' }}" data-priority="{{ $taskPriority }}" data-status="{{ $taskStatus }}">
                        <div class="status-column">
                          @if (count($subTasks) > 0)
                            <button type="button" class="task-expand" data-task-id="{{ $task['id'] }}" aria-label="Expand {{ $task['title'] }}">&#8250;</button>
                          @else
                            <button type="button" class="task-status {{ $taskStatus }}" data-task-id="{{ $task['id'] }}" aria-label="{{ $task['title'] }}: {{ $taskStatusLabel }}">
                              @if ($taskStatus === 'assigned')
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10M7 21h10M8 3c0 4 2 5 4 6-2 1-4 2-4 6M16 3c0 4-2 5-4 6 2 1 4 2 4 6" /></svg>
                              @elseif ($taskStatus === 'done')
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-8" /></svg>
                              @endif
                            </button>
                          @endif
                        </div>
                        
                        <div class="task-name-column body-s">
                          <input class="task-inline-input task-name-input" type="text" maxlength="100" value="{{ $task['title'] }}" placeholder="Task Name" data-task-id="{{ $task['id'] }}" aria-label="Edit task name">
                          @if (count($subTasks) > 0)
                            <span class="subtask-count">{{ $completedSubTasks }}/{{ count($subTasks) }}</span>
                          @endif
                        </div>
                        
                        <div class="assigned-column helper-text">
                          <button type="button" class="member-picker" data-task-id="{{ $task['id'] }}" data-member-id="{{ $task['assignee_id'] ?? '' }}" data-value="{{ $assignee['username'] ?? '' }}">{{ count($subTasks) > 0 ? '...' : ($assignee['username'] ?? 'Member') }}</button>
                        </div>
                        
                        <div class="date-column helper-text">
                          <input class="task-inline-input task-date-input{{ !empty($task['due_date']) ? ' has-value' : '' }}" type="date" value="{{ !empty($task['due_date']) ? substr($task['due_date'], 0, 10) : '' }}" data-task-id="{{ $task['id'] }}" aria-label="Edit due date for {{ $task['title'] }}">
                        </div>
                        
                        <div class="priority-column">
                          @if (count($subTasks) > 0)
                            <span class="main-task-summary">...</span>
                          @else
                            <select class="task-priority-select {{ $taskPriority ? strtolower($taskPriority) : 'default' }}" data-task-id="{{ $task['id'] }}" aria-label="Priority for {{ $task['title'] }}">
                              <option value="" @selected($taskPriority === '')>Priority</option>
                              @foreach (['Low', 'Medium', 'High'] as $priority)
                                <option value="{{ $priority }}" @selected($taskPriority === $priority)>{{ $priority }}</option>
                              @endforeach
                            </select>
                          @endif
                        </div>
                        
                        <div class="action-column">
                          <button type="button" class="task-delete" data-task-id="{{ $task['id'] }}" aria-label="Delete {{ $task['title'] }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                              <path d="M3 6h18M9 6V4h6v2m-8 0 1 14h8l1-14M10 10v6m4-6v6" />
                            </svg>
                          </button>
                        </div>
                      </div>
                      
                      @foreach ($subTasks as $subTask)
                        @php
                          $subTaskStatus = ['draft' => 'assigned', 'in_progress' => 'in-progress', 'completed' => 'done'][$subTask['status'] ?? 'draft'] ?? 'assigned';
                          $subTaskStatusLabel = ['assigned' => 'Draft', 'in-progress' => 'In Progress', 'done' => 'Completed'][$subTaskStatus];
                          $subTaskPriority = ucfirst($subTask['priority'] ?? '');
                          $subTaskAssignee = collect($project['members'] ?? [])->firstWhere('id', $subTask['assignee_id'] ?? null);
                        @endphp
                        <div class="task-row subtask-row" data-parent-task-id="{{ $task['id'] }}" data-task-id="{{ $subTask['id'] }}" data-task-title="{{ $subTask['title'] }}" data-assignee-id="{{ $subTask['assignee_id'] ?? '' }}" data-assignee-name="{{ $subTaskAssignee['username'] ?? '' }}" data-due-date="{{ !empty($subTask['due_date']) ? substr($subTask['due_date'], 0, 10) : '' }}" data-priority="{{ $subTaskPriority }}" data-status="{{ $subTaskStatus }}" hidden>
                          <div class="status-column">
                            <button type="button" class="task-status {{ $subTaskStatus }}" data-task-id="{{ $subTask['id'] }}" aria-label="{{ $subTask['title'] }}: {{ $subTaskStatusLabel }}">
                              @if ($subTaskStatus === 'assigned')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                  <path d="M7 3h10M7 21h10M8 3c0 4 2 5 4 6-2 1-4 2-4 6M16 3c0 4-2 5-4 6 2 1 4 2 4 6" />
                                </svg>
                              @elseif ($subTaskStatus === 'done')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                  <path d="m6 12 4 4 8-8" />
                                </svg>
                              @endif
                            </button>
                          </div>
                          
                          <div class="task-name-column body-s">
                            <input class="task-inline-input task-name-input" type="text" maxlength="100" value="{{ $subTask['title'] }}" placeholder="Task Name" data-task-id="{{ $subTask['id'] }}" aria-label="Edit task name">
                          </div>
                          
                          <div class="assigned-column helper-text">
                            <button type="button" class="member-picker" data-task-id="{{ $subTask['id'] }}" data-member-id="{{ $subTask['assignee_id'] ?? '' }}" data-value="{{ $subTaskAssignee['username'] ?? '' }}">{{ $subTaskAssignee['username'] ?? 'Member' }}</button>
                          </div>
                          
                          <div class="date-column helper-text">
                            <input class="task-inline-input task-date-input{{ !empty($subTask['due_date']) ? ' has-value' : '' }}" type="date" value="{{ !empty($subTask['due_date']) ? substr($subTask['due_date'], 0, 10) : '' }}" data-task-id="{{ $subTask['id'] }}" aria-label="Edit due date for {{ $subTask['title'] }}">
                          </div>
                          
                          <div class="priority-column">
                            <select class="task-priority-select {{ $subTaskPriority ? strtolower($subTaskPriority) : 'default' }}" data-task-id="{{ $subTask['id'] }}" aria-label="Priority for {{ $subTask['title'] }}">
                              <option value="" @selected($subTaskPriority === '')>Priority</option>
                              @foreach (['Low', 'Medium', 'High'] as $priority)
                                <option value="{{ $priority }}" @selected($subTaskPriority === $priority)>{{ $priority }}</option>
                              @endforeach
                            </select>
                          </div>
                          
                          <div class="action-column">
                            <button type="button" class="task-delete" data-task-id="{{ $subTask['id'] }}" aria-label="Delete {{ $subTask['title'] }}">
                              <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 6h18M9 6V4h6v2m-8 0 1 14h8l1-14M10 10v6m4-6v6" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      @endforeach
                      
                      <button type="button" class="create-subtask-btn hover-subtask-btn"
                        data-parent-task-id="{{ $task['id'] }}">
                        <span>+</span>
                        <span>Create SubTask</span>
                      </button>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
            
            <button type="button" id="create-task-btn" class="create-task-button">
              <span>+</span>
              <span class="body-s">Create Task</span>
            </button>
          </div>
        </div>
        
        <div id="state-project-detail" class="content-state">
          <div class="card-header">
            <h2 class="heading-04">Project Detail</h2>
            <button type="button" id="back-to-tasks-btn" class="detail-back-button" aria-label="Back to tasks">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 6h12M7 12h12M7 18h12M3 6h.01M3 12h.01M3 18h.01" /></svg>
            </button>
          </div>
          
          <div id="project-detail-panels">
            @foreach ($allProjects as $project)
              <div class="project-detail-body project-detail-panel" data-project-id="{{ $project['id'] }}" hidden>
                <div class="detail-field">
                  <label class="label-m" for="detail-project-name-{{ $project['id'] }}">Project Name</label>
                  <input id="detail-project-name-{{ $project['id'] }}" data-detail-project-name type="text" maxlength="100" value="{{ $project['title'] }}" disabled>
                </div>
                
                <div class="detail-field">
                  <label class="label-m" for="detail-project-description-{{ $project['id'] }}">Description</label>
                  <textarea id="detail-project-description-{{ $project['id'] }}" data-detail-project-description rows="5" disabled>{{ $project['description'] ?? '' }}</textarea>
                </div>
                
                <section class="detail-members">
                  <h3 class="label-m">Members</h3>
                  
                  <div class="detail-member-table detail-member-head helper-text">
                    <span>No</span>
                    <span>Name</span>
                    <span>Role</span>
                    <span>Email</span>
                  </div>
                  
                  <div class="detail-member-list" data-detail-member-list>
                    @forelse ($project['members'] ?? [] as $member)
                      <div class="detail-member-row body-s" data-member-id="{{ $member['id'] }}" data-membership-id="{{ $member['membership_id'] ?? '' }}" data-member-name="{{ $member['username'] }}" data-member-role="{{ $member['role'] ?? (($member['is_owner'] ?? false) ? 'Owner' : 'Member') }}" data-member-email="{{ $member['email'] }}">
                        <span>{{ $loop->iteration }}</span>
                        <span>{{ $member['username'] }}</span>
                        <span>{{ $member['role'] ?? (($member['is_owner'] ?? false) ? 'Owner' : 'Member') }}</span>
                        <span>{{ $member['email'] }}</span>
                        
                        @if (!empty($member['membership_id']))
                          <button type="button" class="detail-member-delete" data-membership-id="{{ $member['membership_id'] }}" aria-label="Delete {{ $member['username'] }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                              <path d="M3 6h18M9 6V4h6v2m-8 0 1 14h8l1-14M10 10v6m4-6v6" />
                            </svg>
                          </button>
                        @endif
                      </div>
                    @empty
                      <div class="detail-member-empty helper-text">No members have been added.</div>
                    @endforelse
                  </div>
                  
                  <button type="button" class="detail-add-member body-s">
                    <span aria-hidden="true">+</span>
                    <span>Add Other Member</span>
                  </button>
                </section>
              </div>
            @endforeach
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
