# SyncTrack Contribution Guidelines

We appreciate the contributions of all team members throughout the development of SyncTrack. To maintain a consistent development workflow, ensure project stability, and support long-term maintenance and future enhancements, all contributors are required to read, understand, and follow the guidelines outlined in this document before participating in any development activities.

This guide establishes the project's development standards, collaboration workflow, and best practices to ensure code quality, improve team collaboration, and maintain consistency across all contributions.

<br>

## 1.0 Prerequisites

Before cloning the project repository, ensure that your local development environment meets the minimum requirements listed below. Verifying these prerequisites in advance helps ensure a smooth setup process and minimizes potential compatibility issues during development.

| Environment  | Required Version      | Remarks                                                                      |
| ------------ | --------------------- | ---------------------------------------------------------------------------- |
| **Node.js**  | **v24.11.1** or later | Use the latest LTS or stable release.                                        |
| **npm**      | **v11.10.1** or later | Included with Node.js; update separately if required.                        |
| **PHP**      | **v8.4.15** or later  | Required to ensure compatibility with Laravel 13.x.                          |
| **Composer** | **v2.9.2** or later   | Dependency manager for PHP packages.                                         |
| **Laravel**  | **v13.18.1**          | Use the approved project version. Major version must remain within **13.x**. |
| **Git**      | Latest stable release | Version control system used for project collaboration.                       |
| **MySQL**    | **v8.0** or later     | Latest stable release is recommended.                                        |

> [!IMPORTANT] 
> 
> This project is built and maintained on **Laravel 13.x**. All contributors must use the same major version to ensure compatibility across the development environment. Using a different major version—especially a newer release—may introduce breaking changes, deprecated features, or behavioral differences that are incompatible with the current codebase.

<br>

## 2.0 Getting Started

After confirming that your local development environment satisfies all project requirements, clone the repository to your local machine by following the steps below.

### Clone Repository

```bash
git clone https://github.com/jordankuak-academic/synctrack.git
cd synctrack
```

### Install Dependencies

Install all required backend and frontend dependencies by running the commands below:

```bash
composer install
npm install
```

### Configure Environment Variables

Create a local environment configuration file based on the provided template:

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

### Run Database Migrations

Run the following command to create and update the database schema:

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

## 3.0 Development Guidelines

To maintain code consistency, ensure project stability, and minimize unnecessary conflicts, all contributors are required to follow the guidelines below throughout the development process.

### ✅ Follow the Front-end / Back-end Development Boundaries

This project follows a **front-end/back-end separation architecture**. Front-end developers are responsible for user interface implementation, while back-end developers are responsible for APIs, business logic, and data processing.

If your work requires changes to APIs, database structures, data formats, or any functionality owned by another developer, **do not modify the code directly**. Instead, submit a request through the developer collaboration channel and notify the responsible developer using the following format:

- Name: 
- Role: 
- Issue:

### ❌ Do Not Modify the Project Structure Without Approval

Contributors must **not** make structural changes to the project without prior discussion and approval. This includes, but is not limited to:

- Renaming files or directories
- Creating new files or directories
- Moving existing files
- Deleting project files or directories

Any structural changes must first be discussed through the developer collaboration channel before implementation.

### ❌ Do Not Modify Configuration or Dependencies

Contributors must **not** modify project configuration or dependency-related files without prior approval. This includes:

- Environment configuration files
- Build and tooling configuration
- Dependency management files
- Framework configuration files

Installing additional libraries, packages, or third-party dependencies that have not been approved by the team is also prohibited.

The directories and files that contributors are permitted to modify are described in the following section.

<br>

## 4.0 Code and Project Standards

To maintain a consistent codebase and ensure the project's long-term maintainability and scalability, all contributors must follow the coding standards described below.

### Front-end Development (HTML, Blade, SCSS, TypeScript)

#### General Rules

1. Use **2 spaces** for indentation in all front-end source files.
2. Contributors may only modify files within the `resources` directory unless otherwise authorized.
3. Some files within the `resources` directory are protected and must not be modified.

#### TypeScript

##### File Naming

* Every page script must follow the naming convention:

  ```text
  [page-name].ts
  ```

* File names must:

  * Use lowercase letters only.
  * Separate words with hyphens (`kebab-case`).

  **Example**

  ```text
  dashboard.ts
  user-profile.ts
  task-details.ts
  ```

##### Class Structure

Each page script must:

* Be implemented as a class.
* Extend the project's base controller class.
* Implement a protected `initialize()` method for page initialization (such as event listeners and component setup).
* Export the class as the default export.

##### Naming Conventions

| Element             | Convention           |
| ------------------- | -------------------- |
| Classes             | PascalCase           |
| Interfaces          | PascalCase           |
| Types               | PascalCase           |
| Enums               | PascalCase           |
| Generic Types       | PascalCase           |
| Variables           | camelCase            |
| Functions           | camelCase            |
| Function Parameters | camelCase            |
| Constants           | SCREAMING_SNAKE_CASE |

##### Protected Files

The following files are part of the project's core infrastructure and **must not** be modified:

* `resources/scripts/utils/controller.ts`
* `resources/scripts/types/controller.ts`
* `resources/scripts/app.ts` *(Application entry point)*
* `resources/scripts/vite-env.d.ts` *(Vite type declarations)*

#### SCSS

##### Styling Structure

* Use SCSS nesting appropriately to keep styles organized and improve maintainability.

##### Page Wrapper

Each page-specific stylesheet must use a unique wrapper ID as its root selector.

Example:

```scss
#dashboard-wrapper {
  ...
}
```

This prevents style collisions between different pages.

##### Import Rules

Use the modern `@use` directive instead of `@import`.

`@import` has been deprecated in Sass and should only be used when importing external CSS resources such as Google Fonts.

##### Protected Files

The following files are managed globally and must not be modified:

* `resources/scss/abstracts/_bases.scss`
* `resources/scss/abstracts/_global.scss`
* `resources/scss/abstracts/_variables.scss`
* `resources/scss/app.scss` *(SCSS entry point)*

#### HTML / Blade

##### Page Wrapper

Every page must be wrapped inside a root container using the following naming convention:

```html
<div id="[page-name]-wrapper">
    ...
</div>
```

##### Layout Structure

Each Blade page must:

* Use `@extends` to inherit the designated layout.
* Define the page title through the provided `page-title` section.
* Implement the page content within the `contents` section.

### Back-end Development (PHP)

#### General Rules

1. Contributors may only modify the designated front-end-related directory within the `app` directory unless otherwise authorized.
2. Some files are protected and may not be modified.

#### Brace Style

Opening braces must follow the standard Laravel/PHP convention.

Correct:

```php
class UserController {
    ...
}
```

Incorrect:

```php
class UserController
{
    ...
}
```

#### Naming Conventions

| Element             | Convention |
| ------------------- | ---------- |
| Classes             | PascalCase |
| Custom Types        | PascalCase |
| Variables           | snake_case |
| Functions           | camelCase  |
| Function Parameters | camelCase  |

<br>

## 5.0 Git Version Control and Submission

To maintain project stability and ensure a consistent development workflow, this project uses Git for version control. All contributors are required to use Git for source code management and submit their changes to the project's GitHub repository.

### Github Branch Strategy

| Branch Name      | Purpose                                                           |
| ---------------- | ----------------------------------------------------------------- |
| `main`           | Main branch for stable code                                       |
| `develop`        | Development branch for iteration development                      |
| `feature-[name]` | Feature branch for specific features creation                     |
| `bugfix-[name]`  | Bugfix branch for specific bug fixes in respective feature branch |
| `hotfix-[name]`  | Hotfix branch for immediate bug fixes for main branch             |

#### Branch Naming Strategy

- Use all lowercase letters
- Separate words with hyphens (-)
- No special characters allowed.

### Commit Guidelines

This project follows the [Conventional Commits](https://www.conventionalcommits.org/zh-hans/v1.0.0/) specification to maintain a consistent commit history, improve readability, and simplify change tracking.

#### Commit Message Format

```text
<type>(<scope>): <description>
```

##### Types

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

##### Scope

The scope is used to describe the module or component whose impact the submission will have, for example:
* `api`
* `ui`
* `auth`
* `database`
* `config`

Can use `*` to represent the impact is unclear.

##### Description

- Use imperative sentences, present tense, such as "add" instead of "added" or "adds"
- First letter lowercase
- No period at the end
- No more than 50 characters

##### Example Commit Messages

```text
feat(api): add new API endpoint
```

```text
fix(auth): fix login bug
```

### Pull Request Specification

#### Title Format

Make it same with the commit message format.

```text
<type>(<scope>): <description>
```

#### Description Template

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
// Example: Closes #123

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

### Git Submission Workflow

1. Pull the latest code from the `develop` branch

```bash
git checkout develop
git pull origin develop
```

2. Create feature branches

```bash
git checkout -b feature/your-feature-name
```

3. Submit changes (following commit guidelines)

```bash
git add .
git commit -m "feat(xxx): xxx"
```

4. Push to the remote repository

```bash
git push origin feature/your-feature-name
```

5. Create a pull request on the remote repository

#### Synchronize Upstream Branches

If there are conflicts in a Pull Request, the upstream branch needs to be synchronized:

```bash
git checkout feature/your-feature-name
git fetch origin
git rebase origin/develop
# Solve conflicts and continue rebase
git rebase --continue
git push --force-with-lease
```