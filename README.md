# Project Name

A Laravel application running in Docker with MySQL, structured using the **Model-View-Controller (MVC)** pattern.

---

## Table of Contents

### Getting Started
- [Prerequisites](#prerequisites)
- [First-Time Setup](#first-time-setup)
- [Configuring Email (Mailpit)](#configuring-email-mailpit)

### Daily Development
- [Running the Container](#running-the-container)
- [Stopping the Container](#stopping-the-container)
- [Syncing with Git](#syncing-with-git)

### Tools & Interfaces
- [phpMyAdmin](#phpmyadmin)
- [Recommended VSCode Setup](#recommended-vscode-setup)

### Container Management
- [Resetting the Container](#resetting-the-container)
- [Resetting the MySQL Database](#resetting-the-mysql-database)

### Code Reference
- [MVC Architecture Overview](#mvc-architecture-overview)
- [Where to Put Your Code](#where-to-put-your-code)

---

## Prerequisites

| Tool | Download |
|------|----------|
| Git | https://git-scm.com/downloads |
| Docker Desktop | https://www.docker.com/products/docker-desktop |

**Windows:** Docker Desktop requires WSL 2 - the installer will prompt you to enable it.

**Mac:** Install Docker Desktop for your chip (Apple Silicon or Intel) - check under Apple menu → About This Mac.

Make sure Docker Desktop is **running** before executing any commands below.

---

## First-Time Setup

```bash
git clone https://github.com/Camryn2223/csci-486-capstone.git
cd csci-486-capstone
docker compose up --build -d
npm install
npm run build
```

Wait for `ready to handle connections` in the logs (`docker compose logs app -f`), then open [http://localhost:8080](http://localhost:8080).

For active development, run `npm run dev` in a separate terminal instead of `npm run build` to watch for changes automatically.

---

## Configuring Email (Mailpit)

This project uses **Mailpit** for local email testing. It runs entirely inside Docker and catches any emails your application sends without delivering them to real inboxes.

1. Ensure your `.env` contains the Mailpit setup (this is the default in `.env.example`):
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```
2. When performing actions that send email (e.g. User Registration verification, sending Invites, Password resets), simply navigate to the Mailpit Web UI to view the emails.
3. Access the Mailpit Web UI at: [http://localhost:8025](http://localhost:8025)

---

## Running the Container

```bash
docker compose up -d        # start in background
docker compose logs -f      # watch live logs
```

App is at [http://localhost:8080](http://localhost:8080).

---

## Stopping the Container

```bash
docker compose down
```

Preserves your database data.

---

## Syncing with Git

```bash
git pull origin main
docker compose exec app php artisan migrate
```

If a teammate added Composer dependencies and you see missing class errors:

```bash
docker compose down && docker compose up --build -d
docker compose exec app composer update
```

If a teammate changed `.env.example`, copy any new keys into your local `.env`:

```bash
git diff HEAD~1 .env.example
```

Never commit `.env` - it is git-ignored. Use `.env.example` with placeholder values to share new variables with the team.

---

## phpMyAdmin

Available at [http://localhost:8081](http://localhost:8081) whenever containers are running. Auto-logs in using your `.env` credentials.

---

## Recommended VSCode Setup

**Extensions:**
- PHP Intelephense (`bmewburn.vscode-intelephense-client`)
- Laravel Extra Intellisense (`amiralizadeh9480.laravel-extra-intellisense`)

**Disable built-in PHP features** - add to VSCode User Settings JSON:
```json
"php.suggest.basic": false,
"php.validate.enable": false
```

**Install PHP and Composer locally** (for IDE tooling only - the app runs in Docker):

| OS | Instructions |
|----|--------------|
| Windows | Download PHP 8.4 Non Thread Safe zip from https://windows.php.net/download, extract to `C:\php`, add `C:\php` to PATH, then install Composer from https://getcomposer.org/download |
| Mac | `brew install php composer` |
| Linux (Ubuntu/Debian) | `sudo apt install php-cli php-mbstring unzip curl && curl -sS https://getcomposer.org/installer \| php && sudo mv composer.phar /usr/local/bin/composer` |
| Linux (Arch) | `sudo pacman -S php composer` |

Enable extensions in `php.ini` - uncomment `extension=fileinfo` and `extension=zip` (Ubuntu/Debian: `sudo apt install php8.4-fileinfo php8.4-zip`).

Then install dependencies locally (restart VSCode first):

```bash
composer install
```

---

## Resetting the Container

```bash
docker compose down
docker compose up --build -d
```

Rebuilds the image from scratch. Database is preserved.

---

## Resetting the MySQL Database

> ⚠️ Permanently deletes all local data.

```bash
docker compose down -v
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

---

## MVC Architecture Overview

```
Request → Router → Controller → Model → Controller → View → Response
```

**Model** - data shape, relationships, business logic. Lives in `app/Models/`. Never touches HTTP or HTML.

**View** - Blade templates in `resources/views/`. Never queries the database.

**Controller** - receives a request, calls models, returns a view. Lives in `app/Http/Controllers/`. Never contains raw SQL or HTML.

**Router** - maps URLs to controller methods. Lives in `routes/web.php`.

---

## Where to Put Your Code

### Models - `app/Models/`
```bash
docker compose exec app php artisan make:model Post -m
```
The `-m` flag creates a matching migration. Use Eloquent methods - no raw SQL.

### Concerns (Model Traits) - `app/Models/Concerns/`
Traits that split role-specific logic off a model. Create manually - no Artisan command.

### Enums - `app/Enums/`
PHP enums for typed, reusable constants. The `Permission` enum at `app/Enums/Permission.php` is the **single source of truth** for all organization-scoped permissions. The permissions table migration, the `PermissionSeeder`, and the gate definitions in `AppServiceProvider` all read from it automatically.

To add a new permission, add a case to `app/Enums/Permission.php`:

```php
case NewPermission = 'new_permission';
```

That's the only file you need to change. The migration column, seeder insert, and gate definition are all handled automatically.

### Controllers - `app/Http/Controllers/`
One file per resource, at most 7 standard CRUD methods:

| Method | Route | What it does |
|--------|-------|--------------|
| `index` | `GET /posts` | List all records |
| `create` | `GET /posts/create` | Show the create form |
| `store` | `POST /posts` | Save a new record |
| `show` | `GET /posts/{id}` | Show one record |
| `edit` | `GET /posts/{id}/edit` | Show the edit form |
| `update` | `PUT /posts/{id}` | Save changes to a record |
| `destroy` | `DELETE /posts/{id}` | Delete a record |

```bash
docker compose exec app php artisan make:controller PostController --resource
```

### Policies - `app/Policies/`
One file per resource. Defines who can perform each action. Laravel auto-discovers them by naming convention.

```bash
docker compose exec app php artisan make:policy ApplicationPolicy --model=Application
```

Policies call `Gate::allows()` to check permissions:

```php
public function view(User $user, Application $application): bool
{
    return Gate::allows('review-applications', $application->jobPosition->organization);
}
```

In a controller: `$this->authorize('view', $application);`

In Blade: `@can('view', $application) ... @endcan`

### Gates - `app/Providers/AppServiceProvider.php`
Gates are auto-generated from the `Permission` enum - one gate per permission, named by converting underscores to hyphens (`create_positions` → `create-positions`). Every gate passes if the user is the organization's chairman or holds that permission in the organization. Adding a case to the enum is all that's needed - `AppServiceProvider` never needs to be edited.

### Views - `resources/views/`
One subfolder per resource mirroring the controller. Never query the database in a view.

### Routes - `routes/web.php`
```php
Route::resource('posts', PostController::class);
```
Use `routes/web.php` for HTML, `routes/api.php` for JSON.

### Migrations - `database/migrations/`
Every schema change must be a migration. Never edit the database directly.

```bash
docker compose exec app php artisan make:migration add_field_to_table
docker compose exec app php artisan migrate
```

### Seeders - `database/seeders/`
```bash
docker compose exec app php artisan db:seed
```

---

## Artisan Quick Reference

All commands run inside the container: `docker compose exec app php artisan <command>`

| Command | What it does |
|---------|--------------|
| `make:model Post -m` | Create a model + migration |
| `make:controller PostController --resource` | Create a resourceful controller |
| `make:policy ApplicationPolicy --model=Application` | Create a policy with stubbed methods |
| `make:migration add_field_to_table` | Create a migration |
| `make:seeder PostSeeder` | Create a seeder |
| `migrate` | Run pending migrations |
| `migrate:rollback` | Undo the last batch of migrations |
| `db:seed` | Run seeders |
| `route:list` | List all registered routes |
| `tinker` | Open an interactive Laravel shell |