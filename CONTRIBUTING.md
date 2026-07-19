# SyncTrack Contribution Guidelines

This handbook serves as the authoritative code of conduct and normative reference for all contributors to the SyncTrack project. It is designed to unify team collaboration practices, reduce communication overhead, and ensure the long-term maintainability and scalability of the codebase.

All project contributors—including core team members, external collaborators, and occasional contributors—must read and fully understand the entire contents of this handbook before participating in any development activities (including but not limited to coding, testing, code review, documentation, and release processes), and strictly adhere to it throughout their development work.

<br>

## Prerequisites

Before cloning the project repository, verify that your local development environment meets the minimum requirements listed below. Confirming these prerequisites in advance ensures a smooth setup process and minimizes potential compatibility issues throughout the development lifecycle.

The following table specifies the required versions for each tool and runtime:

| Environment  | Required Version      | Remarks                                                                      |
| ------------ | --------------------- | ---------------------------------------------------------------------------- |
| **Node.js**  | **v24.11.1** or later | Use the latest LTS or stable release.                                        |
| **npm**      | **v11.10.1** or later | Included with Node.js; update separately if required.                        |
| **PHP**      | **v8.4.15** or later  | Required for compatibility with Laravel 13.x.                                |
| **Composer** | **v2.9.2** or later   | Dependency manager for PHP packages.                                         |
| **Laravel**  | **v13.18.1**          | Use the approved project version. Major version must remain within **13.x**. |
| **Git**      | Latest stable release | Version control system used for project collaboration.                       |
| **MySQL**    | **v8.0** or later     | Latest stable release is recommended.                                        | 

> [!IMPORTANT]
>
> This project is built and maintained on Laravel 13.x. All contributors must use the same major version to ensure compatibility across the development environment.
> 
> Using a different major version—particularly a newer release—may introduce:
> - Breaking changes
> - Deprecated features
> - Behavioral differences incompatible with the current codebase
>
> If you are using a newer Laravel version (e.g., 14.x), downgrade to the required version before contributing. Any version upgrades must be proposed, reviewed, and approved via a formal project decision before adoption.

Run the following commands to verify your installed versions:

```bash
node -v
npm -v
php -v
composer -V
php artisan --version
git --version
mysql --version
```

<br>

## Project Installation

After confirming that your local development environment satisfies all project requirements, clone the repository to your local machine by following the steps below:

### Clone Repository

Clone the repository to your local machine using the following command:

```bash
git clone https://github.com/jordankuak-academic/synctrack.git
cd synctrack
```

### Install Dependencies

Install the project required backend and frontend dependencies using the following command:

```bash
composer install
npm install
```

### Configure Environment Variables

Configure the project environment variables by following the steps below:

```bash
cp .env.example .env
```

Next, update the database configuration in the `.env` file to match your local development environment:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=synctrack_db
DB_USERNAME=root
DB_PASSWORD=
```

> [!NOTE] 
> 
> If your local MySQL server requires authentication, update the `DB_USERNAME` and `DB_PASSWORD` values according to your database credentials.

### Generate Application Key

Generate a unique application key for your project by running the command below:

```bash
php artisan key:generate
```

### Migrate Database Migrations

Migrate the database migrations to create the required tables:

```bash
php artisan migrate
```

Run the following command to populate the database with default data, including the initial administrator account:

```bash
php artisan db:seed
```

### Start Development Server

Start the Vite development server to compile front-end assets in real time:

```bash
npm run dev
```

Keep this terminal running.

Open a new terminal window and start the Laravel development server:

```bash
php artisan serve
```

The application will be available at:

```
http://127.0.0.1:8000
```

<br>

## Project Structure And Document Organization

To maintain code consistency, ensure project stability, and minimize unnecessary conflicts, all contributors are required to follow the guidelines below throughout the development process.

### Follow the Front-end / Back-end Development Boundaries

This project follows a **front-end/back-end separation architecture**. Front-end developers are responsible for user interface implementation, while back-end developers are responsible for APIs, business logic, and data processing.

If your work requires changes to APIs, database structures, data formats, or any functionality owned by another developer, **do not modify the code directly**. Instead, submit a request through the developer collaboration channel.

| Role                 | Allowed Workspace                 | Restricted Areas                                                               |
| -------------------- | --------------------------------- | ------------------------------------------------------------------------------ |
| Front‑end Developers | `resources/` (all subdirectories) | `app/`, `config/`, `database/`, `routes/`, `tests/` (all subdirectories)       |
| Back‑end Developers  | `app/` (all subdirectories)       | `resources/`, `config/`, `database/`, `routes/`, `tests/` (all subdirectories) |

> [!WARNING]
> 
> - Front‑end developers must not modify any files outside the `resources/` directory.
> - Back‑end developers must not modify any files outside the `app/` directory.
> - Violations must be reverted during code review and may result in the PR being blocked.

### Do Not Modify the Project Structure Without Approval

Contributors must **not** make structural changes to the project without prior discussion and approval. This includes, but is not limited to:

- Renaming files or directories
- Creating new files or directories
- Moving existing files
- Deleting project files or directories

Any structural changes must first be discussed through the developer collaboration channel before implementation.

### Do Not Modify Configuration or Dependencies

Contributors must **not** modify project configuration or dependency-related files without prior approval. This includes:

- Environment configuration files
- Build and tooling configuration
- Dependency management files
- Framework configuration files

Installing additional libraries, packages, or third-party dependencies that have not been approved by the team is also prohibited.

The directories and files that contributors are permitted to modify are described in the following section.

<br>

## Project Development Code Style

To maintain a consistent codebase and ensure the project's long-term maintainability and scalability, all contributors must follow the coding standards described below.

### General Coding Standards

For frontend developers:

- Use **2 spaces** for indentation in all front-end source files.
- Contributors may only modify files within the `resources/` directory unless otherwise authorized.
- Some files within the `resources/` directory are protected and must not be modified.

For backend developers:

- Use **4 spaces** for indentation in all back-end source files.
- Contributors may only modify files within the `app/` directory unless otherwise authorized.
- Some files within the `app/` directory are protected and must not be modified.

### TypeScript Coding Standards

#### Class Structure

In each page script file must:

- Be implemented as a default export class.
- Extend with the project's base controller class.
- Implement a protected `initialize()` method for page initialization (such as event listeners and component setup).

#### Code Style

- Only functions can be called within the `initialize()` function, for example, `bindEvents()`.
- No other code can be placed within the `initialize()` function.

#### Naming Conventions

| Element             | Convention           |
| ------------------- | -------------------- |
| Classes             | PascalCase           |
| Interfaces          | PascalCase           |
| Types               | PascalCase           |
| Enums               | PascalCase           |
| Generic Types       | PascalCase           |
| Variables           | snake_case           |
| Functions           | camelCase            |
| Function Parameters | snake_case           |
| Constants           | SCREAMING_SNAKE_CASE |

#### Restricted Files

The following files are part of the project's core infrastructure and **must not** be modified:

* `resources/scripts/utils/controller.ts`
* `resources/scripts/types/controller.ts`
* `resources/scripts/app.ts` *(Application entry point)*
* `resources/scripts/vite-env.d.ts` *(Vite type declarations)*

### SCSS Coding Standards

#### Code Style

- Use SCSS nesting appropriately to keep styles organized and prevent style conflicts.
- Use modular SCSS files to break down styles into smaller, more manageable files, for example, `_mixins.scss`, `_functions.scss`, `_animations.scss`, etc.
- Use SCSS variables that defined in `_variables.scss` file, and avoid using hardcoded values.

#### Page Wrapper

Each page-specific stylesheet must use a unique wrapper ID as its root selector. All the styles will implement within this wrapper.

Example:

```scss
#page-wrapper {
  .other-element {
    // Styles for the other element
  }
}
```

#### Import Rules

- Use the modern `@use` directive instead of `@import` has been deprecated in Sass and should only be used when importing external CSS resources such as Google Fonts.

#### Restricted Files

The following files are part of the project's core designed styles and **must not** be modified:

* `resources/scss/abstracts/_bases.scss`
* `resources/scss/abstracts/_global.scss`
* `resources/scss/abstracts/_variables.scss`
* `resources/scss/app.scss` *(SCSS entry point)*

### Blade Coding Standards

#### Code Style

- Use Blade syntax for HTML templates.
- Avoid simply using ID attributes in Blade templates. Unless it is necessary for the layout or functionality of the page, such as button click events.
- Avoid implement the rendering logic in TypeScript but in Blade templates.
- Avoid using unique attributes in Blade templates if it is not necessary.

#### Page Wrapper

Each page-specific Blade template must use a unique wrapper ID as its root selector. All the HTML content will implement within this wrapper.

Example:

```bladeade
<div id="page-wrapper">
  <!-- HTML content for the page -->
</div>
```

#### Layout Structure

- Use `@extends` to inherit the designed layout.
- Define the page title through the provided `page-title` section.
- Implement the page content within the `contents` section.

### PHP Coding Standards

#### Code Style

- The type declarations is required in all function parameters.
- The opening brace `{` must always beside the statement or declaration it is opening.

Example:

```php
class PageController extends BaseController {
    public function index() {}
}
```

#### Naming Conventions

| Element             | Convention |
| ------------------- | ---------- |
| Classes             | PascalCase |
| Custom Types        | PascalCase |
| Variables           | snake_case |
| Functions           | camelCase  |
| Function Parameters | snake_case |

<br>

## Git Collaboration Process Specifications

To maintain project stability and ensure a consistent development workflow, this project uses Git for version control. All contributors are required to use Git for source code management and submit their changes to the project's GitHub repository.

### Branching Strategy

| Branch Name      | Purpose                                                                                               |
| ---------------- | ----------------------------------------------------------------------------------------------------- |
| `main`           | Main branch for stable code                                                                           |
| `develop`        | Development branch for iteration development process                                                  |
| `feature-[name]` | Feature branch for specific features creation                                                         |
| `bugfix-[name]`  | Bugfix branch for specific bug fixes in respective feature branch                                     |
| `hotfix-[name]`  | Hotfix branch for immediate bug fixes for main branch and need to be synchronized with develop branch |

> [!NOTE]
> 
> All the branch names must be in lowercase and use kebab-case naming convention. No any special characters or spaces are allowed.

### Pull Latest Code

1. Change the branch to `develop`.

```bash
git checkout develop
```

2. Pull the latest code from the remote repository `develop` branch.

```bash
git pull origin develop
```

> [!NOTE]
> 
> Make sure always get the latest code from the remote repository before start your any development process. Before create a new branch for submission, make sure you are always on `develop` branch.

### Git Submission

1. Create the branch for submission.

```bash
git checkout -b [branch-name]/[your-branch-short-title]
```

2. Commit the changes to the branch (always reference to commit guidelines).

```bash
git add .
git commit -m "[commit-type]([effect-area]): [commit-message]"
```

Example:

```bash
git add .
git commit -m "feat(database): modify the database schema"
```

3. Push the branch to the remote repository.

```bash
git push origin [branch-name]/[your-branch-short-title]
```

### Synchronize Upstream Branches

If there are conflicts in a Pull Request, the upstream branch needs to be synchronized:

```bash
git checkout feature/your-feature-name
git fetch origin
git rebase origin/develop
# Solve conflicts and continue rebase
git rebase --continue
git push --force-with-lease
```

### Pull Request

- Use commit as the request title.
- In request body, describe all the information about this submission.
- Required to setup one contributor as a reviewer.
- Mark the assignee as yourself.

#### Request Body Template

```text
Summary:
// Provide a brief summary of the changes introduced by this pull request.

Type of Change:
// Select all that apply.
- [ ] New Feature (feat)
- [ ] Bug Fix (fix)
- [ ] Documentation Update (docs)
- [ ] Code Style (style)
- [ ] Refactor (refactor)
- [ ] Performance Optimization (perf)
- [ ] Test Related (test)
- [ ] Build/Tool Change (chore)

Related Issue:
// If applicable, reference the issue number or link.
// Example: Closed #123

Checklist:
// Confirm the following before submitting this pull request.
- [ ] My code has been tested locally.
- [ ] My code follows the project's coding standards.
- [ ] I have updated the relevant documentation, where applicable.
- [ ] My commit messages follow the project's commit guidelines.
- [ ] This pull request does not introduce breaking changes.

Self-Test Description:
// Describe how you tested your changes and summarize the results.
```

> [!NOTE]
> 
> Use `x` to mark the checklist items that you have confirmed.

### Commit Guidelines

This project follows the [Conventional Commits](https://www.conventionalcommits.org/zh-hans/v1.0.0/) specification to maintain a consistent commit history, improve readability, and simplify change tracking.

#### Commit Message

```text
[commit-type]([effect-area]): [commit-message]
```

##### Commit Type

| Type       | Description                                                            |
| ---------- | ---------------------------------------------------------------------- |
| `chore`    | Build process or auxiliary tools change                                |
| `ci`       | CI/CD configuration or script change                                   |
| `docs`     | Documentation change                                                   |
| `feat`     | Add new feature                                                        |
| `fix`      | Bug fixing                                                             |
| `perf`     | Performance optimization                                               |
| `refactor` | Code structure refactor without functional change and not fixing a bug |
| `revert`   | Revert to previous version                                             |
| `style`    | Code style change                                                      |
| `test`     | Add new test or correcting existing test                               |

##### Effect Area

The effect area is used to describe the module or component whose impact the submission will have, for example: `database`, `api`, `frontend`, `backend`, etc.

You can always use `*` to represent the unknown or unclear effect area.

##### Commit Message

- Use imperative sentences, present tense, such as "add" instead of "added" or "adds"
- all the commit messages must be in lowercase
- no more than 50 characters
- use space in message neither use hyphen in between all the words