# Project Name

A Laravel application running in Docker with MySQL, structured using the **Model-View-Controller (MVC)** pattern and using **Vite** for frontend assets.

---

## Table of Contents

### Getting Started
- [Prerequisites](#prerequisites)
- [First-Time Setup](#first-time-setup)
- [Configuring Email (Resend)](#configuring-email-resend)

### Daily Development
- [Starting the App](#starting-the-app)
- [Stopping the App](#stopping-the-app)
- [Syncing with Git](#syncing-with-git)

### Tools & Interfaces
- [phpMyAdmin](#phpmyadmin)
- [Recommended VSCode Setup](#recommended-vscode-setup)

### Reset & Recovery
- [Resetting the Containers](#resetting-the-containers)
- [Resetting the MySQL Database](#resetting-the-mysql-database)

### Code Reference
- [MVC Architecture Overview](#mvc-architecture-overview)
- [Directory Structure](#directory-structure)
- [Where to Put Your Code](#where-to-put-your-code)
- [Artisan Quick Reference](#artisan-quick-reference)

---

## Prerequisites

Install the following on your machine before working on the project:

| Tool | Download |
|------|----------|
| Git | https://git-scm.com/downloads |
| Docker Desktop | https://www.docker.com/products/docker-desktop |
| Node.js (LTS) | https://nodejs.org |

`npm` is included with Node.js and is required for installing frontend dependencies and running Vite during development.

**Windows users:** Docker Desktop requires WSL 2. The installer will prompt you to enable it. Follow Docker's instructions during install.

**Mac users:** Install Docker Desktop for Mac (Apple Silicon or Intel depending on your chip). You can check your chip under Apple menu → About This Mac.

After installing Docker Desktop, make sure it is **running** before using any Docker commands below.

You can verify your tools with:

```bash
git --version
docker --version
node -v
npm -v
````

---

## First-Time Setup

Do this once after cloning the repo.

### 1. Clone the repository

Go to the directory where you want the project to live before running these commands. The clone command will create a separate `csci-486-capstone` directory automatically.

```bash
git clone https://github.com/Camryn2223/csci-486-capstone.git
cd csci-486-capstone
```

### 2. Build and start the containers

```bash
docker compose up --build -d
```

This will build the PHP/Nginx image, pull the MySQL image, install Composer dependencies, generate your `APP_KEY`, run database migrations, and start all services in the background.

To watch the startup process:

```bash
docker compose logs app -f
```

Wait until the services finish starting before moving on.

### 3. Install frontend dependencies

```bash
npm install
```

This installs the JavaScript packages used by Vite.

### 4. Start the Vite development server

Open a **second terminal** in the project root and run:

```bash
npm run dev
```

Leave this running while you work. It watches your CSS and JavaScript files and rebuilds them automatically whenever they change.

### 5. Open the app

Go to [http://localhost:8080](http://localhost:8080) in your browser.

---

## Configuring Email (Resend)

This app sends transactional emails for account verification, password resets, and interview notifications. **Resend** is the recommended email provider. It has a free tier and integrates cleanly with Laravel.

> This only needs to be set up once per developer machine. The package itself is installed for everyone through `composer.lock`. Each teammate only needs to add the correct local values to their own `.env`.

### 1. Create a free account

Sign up at [https://resend.com](https://resend.com) and create an API key from the dashboard.

### 2. Verify a sender address

In the Resend dashboard, go to **Domains** and verify the email address or domain you want to send from. During development, you can verify a personal email address.

### 3. Update your `.env`

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_6VXbRdfk_vgEZCYVVWfn43HJRVnjtEUnL
MAIL_FROM_ADDRESS="onboarding@resend.dev"
MAIL_FROM_NAME="${APP_NAME}"
```

Replace:

* `onboarding@resend.dev` with your verified sender address if needed

### 4. Add the key to `config/services.php`

```php
'resend' => [
    'key' => env('RESEND_API_KEY'),
],
```

> Never commit your real API key. The `.env` file is git-ignored. Only `.env.example` should be committed, and it should always contain placeholder values instead of secrets.

If you update `.env` and the app does not pick up the change, clear config inside the container:

```bash
docker compose exec app php artisan config:clear
```

---

## Starting the App

After first-time setup, start the containers with:

```bash
docker compose up -d
```

Then start the Vite development server in a separate terminal:

```bash
npm run dev
```

Leave both running while developing.

The app will be available at:

* **Application:** [http://localhost:8080](http://localhost:8080)
* **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)

To watch container logs live:

```bash
docker compose logs -f
```

---

## Stopping the App

To stop the containers:

```bash
docker compose down
```

This stops and removes the containers but **preserves your database data**.

To stop the Vite dev server, press `Ctrl + C` in the terminal where `npm run dev` is running.

---

## Syncing with Git

### Daily workflow

Before starting work each day:

```bash
git pull origin main
docker compose exec app php artisan migrate
```

Then start the app if it is not already running:

```bash
docker compose up -d
npm run dev
```

### If a teammate added new PHP dependencies (`composer.json` changed)

The container installs dependencies automatically on startup. If you see missing-class errors, rebuild:

```bash
docker compose down
docker compose up --build -d
```

### If a teammate added new frontend dependencies (`package.json` changed)

Reinstall them locally:

```bash
npm install
```

Then restart the Vite dev server:

```bash
npm run dev
```

### If a teammate changed `.env.example`

Check the changes and manually copy any new variables into your own `.env`:

```bash
git diff HEAD~1 .env.example
```

Then update your local `.env` with your own values.

### Never commit `.env`

The `.env` file is git-ignored and must never be committed. It contains secrets and machine-specific values. If the team needs to share a new environment variable, add it to `.env.example` with a placeholder value only.

---

## phpMyAdmin

phpMyAdmin is included and starts automatically with Docker.

Once your containers are up, go to:

**[http://localhost:8081](http://localhost:8081)**

Use the database credentials from your `.env` file:

* `DB_USERNAME`
* `DB_PASSWORD`

From phpMyAdmin you can:

* browse tables
* inspect rows
* run SQL queries
* confirm that your app is creating or updating data correctly

---

## Recommended VSCode Setup

### 1. Install these extensions

* **PHP Intelephense** (`bmewburn.vscode-intelephense-client`)
* **Laravel Extra Intellisense** (`amiralizadeh9480.laravel-extra-intellisense`)

### 2. Disable VSCode's built-in PHP features

Open VSCode settings JSON and add:

```json
"php.suggest.basic": false,
"php.validate.enable": false
```

### 3. Install PHP and Composer locally

This is required for editor tooling only. The app itself still runs in Docker.

**Windows**

1. Download PHP 8.4 **VS17 x64 Non Thread Safe** zip from [https://windows.php.net/download](https://windows.php.net/download)
2. Create `C:\php`
3. Extract the zip into `C:\php`
4. Add `C:\php` to your system `Path`
5. Download and run `Composer-Setup.exe` from [https://getcomposer.org/download](https://getcomposer.org/download)

**Mac**

```bash
brew install php
brew install composer
```

If Homebrew is not installed:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

**Linux (Ubuntu/Debian)**

```bash
sudo apt update
sudo apt install php-cli php-mbstring unzip curl
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Linux (Arch)**

```bash
sudo pacman -S php composer
```

Verify both installed correctly:

```bash
php -v
composer -v
```

### 4. Enable required PHP extensions

**Windows**

1. Open `C:\php\php.ini`
2. Uncomment these lines by removing the leading `;`:

   ```
   extension=fileinfo
   extension=zip
   ```

**Mac**

1. Find your loaded config file:

   ```bash
   php --ini | grep "Loaded Configuration"
   ```
2. Open that file and uncomment:

   ```
   extension=fileinfo
   extension=zip
   ```

**Linux (Arch-based)**

1. Open `/etc/php/php.ini`
2. Uncomment:

   ```
   extension=zip
   extension=iconv
   ```

**Linux (Ubuntu/Debian)**

```bash
sudo apt install php8.4-fileinfo php8.4-zip
```

These are installed as separate packages and enabled automatically.

### 5. Install PHP dependencies locally for IntelliSense

Run this once after cloning, and again any time `composer.json` changes:

```bash
composer install
```

This creates the local `vendor/` folder so your editor can resolve Laravel classes properly.

---

## Resetting the Containers

Use this if a container is broken or you need a clean rebuild after changing Docker-related files:

```bash
docker compose down
docker compose up --build -d
```

Your database volume is preserved.

If frontend packages are missing afterward, rerun:

```bash
npm install
```

Then restart Vite:

```bash
npm run dev
```

---

## Resetting the MySQL Database

> Warning: this permanently deletes all local database data.

Use this only if you want a completely fresh local database.

```bash
docker compose down -v
docker compose up -d
docker compose exec app php artisan migrate
```

The `-v` flag removes the named Docker volume that stores MySQL data.

After resetting, start the frontend dev server again if needed:

```bash
npm run dev
```

---

## MVC Architecture Overview

This project follows the **Model-View-Controller** pattern. Every feature should be split across these three responsibilities.

```text
Request → Router → Controller → Model → Controller → View → Response
```

### Model

The Model represents your data and business logic. Models live in `app/Models/` and map to database tables through Eloquent ORM.

A model is responsible for:

* defining fillable fields, casts, and relationships
* querying and persisting data
* encapsulating record-level business rules

A Model should **not** know anything about HTTP requests or HTML rendering.

```php
// app/Models/Post.php
class Post extends Model
{
    protected $fillable = ['title', 'body', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### View

The View is the HTML returned to the browser. Views live in `resources/views/` and use Blade templates.

A view is responsible for:

* rendering HTML from controller-provided data
* reusing layouts and components
* displaying data only

A View should **not** query the database or contain business logic.

```blade
{{-- resources/views/posts/index.blade.php --}}
@extends('layouts.app')

@section('content')
    @foreach ($posts as $post)
        <h2>{{ $post->title }}</h2>
    @endforeach
@endsection
```

### Controller

The Controller connects routes, models, and views. Controllers live in `app/Http/Controllers/`.

A controller is responsible for:

* receiving the request
* calling the appropriate model or service
* returning the correct view or redirect

A Controller should **not** contain raw SQL or large amounts of business logic.

```php
// app/Http/Controllers/PostController.php
class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::latest()->get();

        return view('posts.index', compact('posts'));
    }
}
```

### Router

Routes are the entry point to the application. They live in:

* `routes/web.php` for browser routes
* `routes/api.php` for API routes

A route maps a URL and HTTP method to a controller action.

```php
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
```

### Complete request flow

1. Browser sends `GET /posts`
2. `routes/web.php` matches the request
3. Laravel calls `PostController@index`
4. The controller fetches data from `Post`
5. The model queries MySQL and returns results
6. The controller passes data to a Blade view
7. The view renders HTML and returns the response

---

## Directory Structure

```text
project-root/
├── app/                             # All server-side application code
│   ├── Http/
│   │   └── Controllers/
│   │       └── Controller.php       # [CONTROLLER] Base controller
│   ├── Policies/
│   │   └── ApplicationPolicy.php    # [POLICY] Authorization rules per resource
│   ├── Models/
│   │   ├── Concerns/
│   │   │   ├── HasInterviewerFeatures.php   # [MODEL] Trait for interviewer-specific logic
│   │   │   └── HasChairmanFeatures.php      # [MODEL] Trait for chairman-specific logic
│   │   └── User.php                         # [MODEL] Default User model
│   └── Providers/
│       └── AppServiceProvider.php   # Gate definitions and application bootstrapping
├── bootstrap/                       # Laravel bootstrapping
│   ├── app.php
│   ├── providers.php
│   └── cache/
├── config/                          # Laravel configuration
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── fortify.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php          # [MODEL] Fake data generator
│   ├── migrations/                  # [MODEL] Database schema changes
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php       # [MODEL] Seeder entry point
├── docker/                          # Docker support files
│   ├── mysql/
│   │   └── init.sql
│   ├── nginx.conf
│   └── start.sh
├── public/                          # Web root
│   ├── index.php
│   ├── favicon.ico
│   ├── robots.txt
│   └── .htaccess
├── resources/
│   ├── css/
│   │   └── app.css                  # Raw CSS compiled by Vite
│   ├── js/
│   │   ├── app.js                   # Raw JS entry point compiled by Vite
│   │   └── bootstrap.js
│   └── views/                       # [VIEW] All Blade templates
│       ├── auth/                    # Login, register, 2FA, password reset views
│       └── welcome.blade.php
├── routes/
│   ├── web.php                      # [ROUTER] Browser-facing routes
│   └── console.php                  # Artisan console command routes
├── storage/                         # Logs, cache, sessions
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── TestCase.php
│   ├── Feature/
│   │   └── ExampleTest.php          # Full request/response tests
│   └── Unit/
│       └── ExampleTest.php          # Unit tests
├── .editorconfig
├── .env                             # Local environment variables, never commit
├── .env.example                     # Shared environment template
├── .gitattributes
├── .gitignore
├── artisan                          # Laravel CLI entry point
├── composer.json                    # PHP dependency definitions
├── composer.lock                    # Locked PHP dependency versions
├── docker-compose.yml               # Docker service definitions
├── Dockerfile                       # PHP/Nginx container definition
├── package.json                     # JS dependency definitions
├── phpunit.xml                      # PHPUnit configuration
├── README.md
└── vite.config.js                   # Vite configuration
```

---

## Where to Put Your Code

### Models — `app/Models/`

Create one model per database table.

```bash
docker compose exec app php artisan make:model Post -m
```

The `-m` flag creates a migration too.

Put these on the model:

* relationships
* query scopes
* accessors and mutators
* record-level data logic

Do not put raw SQL directly in controllers when Eloquent can handle it.

---

### Concerns (Model Traits) — `app/Models/Concerns/`

Use concerns when a model is getting large or when some behavior applies only in certain roles or contexts.

There is no Artisan command for traits. Create them manually:

```text
app/Models/Concerns/HasInterviewerFeatures.php
app/Models/Concerns/HasChairmanFeatures.php
```

Example:

```php
namespace App\Models\Concerns;

trait HasChairmanFeatures
{
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'chairman_id');
    }
}
```

Then use the trait in the model:

```php
use App\Models\Concerns\HasChairmanFeatures;

class User extends Authenticatable
{
    use HasChairmanFeatures;
}
```

---

### Controllers — `app/Http/Controllers/`

Use one controller per resource.

```bash
docker compose exec app php artisan make:controller PostController --resource
```

A resource controller gives you the standard 7 CRUD methods:

| Method    | Route                  | Purpose           |
| --------- | ---------------------- | ----------------- |
| `index`   | `GET /posts`           | List all records  |
| `create`  | `GET /posts/create`    | Show create form  |
| `store`   | `POST /posts`          | Save a new record |
| `show`    | `GET /posts/{id}`      | Show one record   |
| `edit`    | `GET /posts/{id}/edit` | Show edit form    |
| `update`  | `PUT /posts/{id}`      | Save changes      |
| `destroy` | `DELETE /posts/{id}`   | Delete record     |

Keep controller methods short.

---

### Policies — `app/Policies/`

Use one policy per model/resource.

```bash
docker compose exec app php artisan make:policy ApplicationPolicy --model=Application
```

This creates a policy class with action methods stubbed out.

Example:

```php
class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return Gate::allows('review-applications', $application->jobPosition->organization);
    }
}
```

In controllers:

```php
$this->authorize('create', Application::class);
$this->authorize('view', $application);
```

In Blade:

```blade
@can('view', $application)
    <a href="{{ route('applications.show', $application) }}">View Application</a>
@endcan
```

---

### Gates — `app/Providers/AppServiceProvider.php`

Gates define reusable named permission checks.

This project uses these organization-scoped gates:

| Gate                  | Permission                                 |
| --------------------- | ------------------------------------------ |
| `create-positions`    | Create, edit, delete job positions         |
| `manage-templates`    | Create, edit, delete application templates |
| `review-applications` | View and manage applications               |
| `schedule-interviews` | Schedule and update interviews             |
| `manage-members`      | Grant and revoke member permissions        |

Example usage:

```php
Gate::allows('review-applications', $organization)
```

---

### Views — `resources/views/`

Use one subfolder per resource, matching the controller name.

```text
resources/views/
├── layouts/
│   └── app.blade.php
├── posts/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── auth/
    ├── login.blade.php
    ├── register.blade.php
    └── ...
```

Controllers pass data into views like this:

```php
return view('posts.index', compact('posts'));
```

Do not query the database from Blade views.

---

### Routes — `routes/web.php`

Register a resource controller in one line:

```php
Route::resource('posts', PostController::class);
```

Use:

* `routes/web.php` for HTML pages
* `routes/api.php` for JSON APIs

---

### Migrations — `database/migrations/`

Every schema change must be represented by a migration.

```bash
docker compose exec app php artisan make:migration add_published_at_to_posts_table
```

Run pending migrations with:

```bash
docker compose exec app php artisan migrate
```

---

### Seeders — `database/seeders/`

Use seeders for default, test, or demo data.

```bash
docker compose exec app php artisan db:seed
```

---

## Artisan Quick Reference

All Artisan commands must be run inside the container:

```bash
docker compose exec app php artisan <command>
```

| Command                                             | What it does                         |
| --------------------------------------------------- | ------------------------------------ |
| `make:model Post -m`                                | Create a model and migration         |
| `make:controller PostController --resource`         | Create a resource controller         |
| `make:policy ApplicationPolicy --model=Application` | Create a policy with stubbed methods |
| `make:policy ApplicationPolicy`                     | Create an empty policy               |
| `make:migration add_field_to_table`                 | Create a migration                   |
| `make:seeder PostSeeder`                            | Create a seeder                      |
| `migrate`                                           | Run pending migrations               |
| `migrate:rollback`                                  | Roll back the last migration batch   |
| `db:seed`                                           | Run seeders                          |
| `route:list`                                        | Show all registered routes           |
| `tinker`                                            | Open the Laravel REPL                |
