@extends('layouts.hsxxx-layout')
@section('page-title', 'Project')
@section('contents')
{{-- Project page --}}
<div
    id="project-wrapper"
    data-projects="{{ json_encode($projects) }}">
    {{-- Project header --}}
    <div class="project-header">
        <h1 class="heading-01">
            Project List
        </h1>
        <div
            id="header-default-actions"
            class="header-actions active">
            <button
                id="create-project-btn"
                class="btn btn-primary">
                Create Project
            </button>
        </div>
        <div
            id="header-create-actions"
            class="header-actions">
            <button
                id="cancel-project-btn"
                class="btn btn-secondary">
                Cancel
            </button>
            <button
                id="save-project-btn"
                class="btn btn-primary">
                Create
            </button>
        </div>
        <div
            id="header-project-detail-actions"
            class="header-actions">
            <button id="delete-project-btn" class="btn btn-danger">Delete</button>
            <button id="edit-project-btn" class="btn btn-primary">Edit</button>
        </div>
        <div
            id="header-project-edit-actions"
            class="header-actions">
            <button id="cancel-project-edit-btn" class="btn btn-secondary">Cancel</button>
            <button id="save-project-edit-btn" class="btn btn-primary">Save</button>
        </div>
    </div>

    {{-- Project list and content --}}
    <div class="project-body">
        <aside class="project-list-card">
            <div class="card-header">
                <h2 class="heading-04">
                    Projects
                </h2>
                <span
                    class="project-count helper-text">
                    0
                </span>
            </div>
            <div class="card-content">
                <div
                    id="project-empty-list"
                    class="project-empty active">
                    <p class="body-l">
                        No project available.
                    </p>
                </div>
                <div
                    id="project-item-list"
                    class="project-items">
                </div>
            </div>
        </aside>
        <section class="project-content-card">
            <div
                id="state-empty"
                class="content-state active">
                <div class="card-header">
                    <h2 class="heading-04">
                        No Project Available
                    </h2>
                </div>
                <div class="card-body empty-center">
                    <p class="body-l">
                        No project available.
                    </p>
                </div>
            </div>
            <div
                id="state-create-project"
                class="content-state">
                <div class="card-header">
                    <h2 class="heading-04">
                        Create Project
                    </h2>
                </div>
                <div class="card-body">
                    <form
                        id="project-form"
                        action="{{ route('project.store') }}"
                        method="POST"
                        autocomplete="off">
                        @csrf
                        <div class="form-group">
                            <label
                                class="label-m"
                                for="project-name">
                                Project Name
                            </label>
                            <input
                                id="project-name"
                                name="title"
                                type="text"
                                maxlength="100"
                                required
                                placeholder="Enter project name">
                        </div>
                        <div class="form-group">
                            <label
                                class="label-m"
                                for="project-description">
                                Description
                            </label>
                            <textarea
                                id="project-description"
                                name="description"
                                rows="5"
                                maxlength="255"
                                required
                                placeholder="Enter project description"></textarea>
                        </div>
                    </form>
                </div>
            </div>
            <div
                id="state-project"
                class="content-state">
                <div class="card-header">
                    <h2
                        id="task-project-title"
                        class="heading-04">
                        Project Name
                    </h2>
                    <button
                        type="button"
                        class="task-settings-button"
                        aria-label="Task settings">
                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.94 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.57 15 1.7 1.7 0 0 0 3 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.57 1.7 1.7 0 0 0 10 3h4v.08a1.7 1.7 0 0 0 1.06 1.52 1.7 1.7 0 0 0 1.88-.34L17 4.2 19.83 7l-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 21 10h.08v4H21a1.7 1.7 0 0 0-1.6 1Z" />
                        </svg>
                    </button>
                </div>
                <div class="task-body">
                    <div class="task-header">
                        <div class="status-column helper-text">
                            Status
                        </div>
                        <div class="task-name-column helper-text">
                            Task Name
                        </div>
                        <div class="assigned-column helper-text">
                            Assigned
                        </div>
                        <div class="date-column helper-text">
                            Due Date
                        </div>
                        <div class="priority-column helper-text">
                            Priority
                        </div>
                        <div class="action-column helper-text">
                            Action
                        </div>
                    </div>
                    <div
                        id="task-list"
                        class="task-list">
                        <div
                            id="task-empty"
                            class="task-empty">
                            <p class="body-l">
                                No task available.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        id="create-task-btn"
                        class="create-task-button">
                        <span>+</span>
                        <span class="body-s">
                            Create Task
                        </span>
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
                <div class="project-detail-body">
                    <div class="detail-field">
                        <label class="label-m" for="detail-project-name">Project Name</label>
                        <input id="detail-project-name" type="text" maxlength="100" disabled>
                    </div>
                    <div class="detail-field">
                        <label class="label-m" for="detail-project-description">Description</label>
                        <textarea id="detail-project-description" rows="5" disabled></textarea>
                    </div>
                    <section class="detail-members">
                        <h3 class="label-m">Members</h3>
                        <div class="detail-member-table detail-member-head helper-text">
                            <span>No</span><span>Member Name</span><span>Email</span>
                        </div>
                        <div id="detail-member-list" class="detail-member-list"></div>
                    </section>
                </div>
            </div>
            <div
                id="toast-success"
                class="project-toast success">
                <span class="toast-icon">
                    ✓
                </span>
                <div class="toast-content">
                    <div class="toast-title label-m">
                        Success
                    </div>
                    <div class="toast-message helper-text">
                        Operation completed successfully.
                    </div>
                </div>
            </div>
            <div
                id="toast-error"
                class="project-toast error">
                <span class="toast-icon">
                    ✕
                </span>
                <div class="toast-content">
                    <div class="toast-title label-m">
                        Failed
                    </div>
                    <div class="toast-message helper-text">
                        Please complete all required fields.
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
