# SyncTrack - A Team-based Project Task Tracking System

## Overview

SyncTrack is a collaborative project task-tracking system designed to streamline team-based project management. It empowers project administrators and team members to efficiently monitor, assign, and progress tasks within a shared workspace. By providing real-time visibility into individual responsibilities and overall project health, SyncTrack reduces coordination overhead and accelerates delivery timelines.

The platform serves as a centralized hub where:
- Project administrators can oversee all ongoing projects, track team member workloads, and intervene early when bottlenecks arise.
- Team members gain clarity on their assigned tasks, deadlines, and priorities, enabling them to focus on what matters most.

### Core Objectives

The primary goal of SyncTrack is to bridge the gap between task assignment and task execution by offering:
- **Transparency:** Every project member can see who is doing what, and when it is due.
- **Accountability:** Clear ownership of tasks and subtasks ensures responsibility is never ambiguous.
- **Efficiency:** Streamlined workflows reduce the need for constant status-update meetings.

### Core Functionalities

SyncTrack delivers a robust set of essential features to support end-to-end project task management:

| Feature                   | Description                                                                                                                                  |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| User Authentication       | Secure registration and login system to protect user data and personalize the experience.                                                    |
| Project Creation          | Administrators can create new projects, each serving as an isolated workspace for a specific initiative.                                     |
| Task & Subtask Management | Within each project, users can create main tasks and break them down into smaller, actionable subtasks, enabling granular progress tracking. |
| Team Membership           | Project administrators can invite or add existing system users to a project team, simple defining the access and the role in the project.    |
| Task Assignment           | Administrators and project leads can assign tasks and subtasks to specific team members, ensuring clear ownership.                           |

### Advanced Enhancement Features

To further empower project administrators in distinguishing, prioritizing, and visualizing work, SyncTrack includes three advanced capabilities:

| Enhancement           | Purpose                                                                                                            |
| --------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Priority Levels       | Tasks can be labeled with priority levels (e.g., Low, Medium, High), helping teams focus on urgent items first.    |
| Due Dates & Deadlines | Every task and subtask can have an associated deadline, enabling time-based tracking and alerts for overdue items. |
| Project Kanban Board  | A visual board organizes tasks by status, showing project momentum and team workload.                              |

### Target Users

- **Project Administrators / Managers:** Oversee multiple projects, allocate resources, and ensure milestones are met.
- **Team Members:** Execute assigned tasks, update progress, and collaborate with peers.
- **Stakeholders:** Gain high-level visibility into project status without needing to dive into operational details.

### Value Proposition

SyncTrack transforms chaotic task tracking into a structured, visual, and collaborative experience. By combining essential project management utilities with advanced prioritization and visualization tools, it helps teams:
- Reduce miscommunication and duplication of effort.
- Identify risks early through deadline and priority awareness.
- Maintain momentum with clear, shared visibility into progress.
- Adapt quickly to changing project demands without losing oversight.

## Team Members & Collaborator

### Jordan Kuak Kian Meng - Project Lead

- **Student ID:** BAI_A2009F-2605004
- **Email:** jordankuak-academic@gmail.com

| Role                      | Responsibility                                                                                    |
| ------------------------- | ------------------------------------------------------------------------------------------------- |
| Project Lead              | Review code submissions, plan features for each iteration, and prepare file skeletons in advance. |
| Database Administrator    | Design and maintain the database schema, ensuring data integrity and security.                    |
| GitHub Repository Manager | Manage the GitHub repository, coordinate with team members, and ensure code quality.              |

### Cheng Wei Le - Product Experience Architect

- **Student ID:** BAI_A2009F-2605008
- **Email:** cheng.academic123@gmail.com

| Role                         | Responsibility                                                                                                               |
| ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| Product Experience Architect | Integrate meeting function specs into product architecture, enhance UX, optimize feature planning, and refine the prototype. |
| Backend Developer            | Develop the backend logic, improve backend information verification, and ensure smooth operation.                            |
| Project Acceptance Manager   | Conduct final acceptance and confirm compliance with delivery criteria.                                                      |

### Lim Swee Sheng - Project Frontend Developer

- **Student ID:** BAI_A2009F-2605010
- **Email:** limsweesheng@gmail.com

| Role               | Responsibility                                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------------------------- |
| Frontend Developer | Develop the frontend interface, ensure a smooth user experience, and integrate with backend backend services. |
| Project Tester     | Test the frontend interface, ensure functionality and performance.                                            |
| Project Debugger   | Debug any issues or bugs in the frontend code.                                                                |

### Andrew Tan Yan Rui - Project Frontend Developer

- **Student ID:** BAI_A2009F-2605005
- **Email:** yanrui1216@gmail.com

| Role               | Responsibility                                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------------------------- |
| Frontend Developer | Develop the frontend interface, ensure a smooth user experience, and integrate with backend backend services. |
| Project Tester     | Test the frontend interface, ensure functionality and performance.                                            |
| Project Debugger   | Debug any issues or bugs in the frontend code.                                                                |

## Quick Start

Get the project up and running locally in minutes:

### Prerequisites

Ensure you have the following installed:
- Node.js **v24.11.1+**
- PHP **v8.4.15+**
- Composer **v2.9.2+**
- MySQL **v8.0+**

> [!NOTE]
>
> For detailed environment requirements, see [Prerequisites](./CONTRIBUTING.md#prerequisites).

### Installation

```bash
# Clone the repository
git clone https://github.com/jordankuak-academic/synctrack.git
cd synctrack

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
# Edit .env with your database credentials

# Generate key and migrate
php artisan key:generate
php artisan migrate --seed

# Start the development servers
npm run dev          # Terminal 1
php artisan serve    # Terminal 2
```

The application will be available at [http://127.0.0.1:8000](http://127.0.0.1:8000).

> [!NOTE]
>
> For detailed installation instructions, see [Project Installation](./CONTRIBUTING.md#project-installation).

## Iteration Summary

### Iteration 1

Based on the requirements in SE Project Documentation, the requirement is a minimum viable product with the essential task management functions. 

- **Features:** User registration, login, and basic project/task/subtask management features.
- **Enhancements:** Implemented simplified user interface such as "Project" page only.
- **Bug Fixes:** Fixed a few minor issues like accidental modification of project configuration documents (the latest version of iteration 1 in release tag `v1.1.0`).

### Iteration 2

Additional features and refinements, such as improved validation, filtering, labels, priorities, or interface improvements is planned for iteration 2.

- **Features:** Improved validation of login logic, added dashboard page to display the task/subtask for respective users.
- **Enhancements:** User interface improvements, refactor the implementation of "Project" page from TypeScript rendering to PHP Blade template.
- **Bug Fixes:** None (the latest version of iteration 2 in release tag `v2.0.0`).

### Iteration 3

Following the requirements in SE Project Documentation, the requirement is advanced collaboration or reporting features, such as dashboard analytics, activity 
logs, workload summary, or sprint view, together with quality improvements and integration refinement.

- **Features:** Completed user validation for action permissions, added dashboard page to display the analytics details for respective users.
- **Enhancements:** User interface improvements, move old dashboard features to "Tasks" page, and added new features such as User profile page, analytics dashboard, and task/subtask in board or calendar view.
- **Bug Fixes:** None (the latest version of iteration 3 in release tag `v3.1.0`).
