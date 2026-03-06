# Project Name

A Laravel application running in Docker with MySQL, structured using the **Model-View-Controller (MVC)** pattern.

---

## Table of Contents

- [Prerequisites](#prerequisites)
- [MVC Architecture Overview](#mvc-architecture-overview)
- [Directory Structure](#directory-structure)
- [First-Time Setup](#first-time-setup)
- [Running the Container](#running-the-container)
- [Stopping the Container](#stopping-the-container)
- [Resetting the Container](#resetting-the-container)
- [Resetting the MySQL Database](#resetting-the-mysql-database)
- [phpMyAdmin](#phpmyadmin)
- [Syncing with Git](#syncing-with-git)
- [Where to Put Your Code](#where-to-put-your-code)

---

## Prerequisites

Install the following on your machine regardless of operating system:

| Tool | Download |
|------|----------|
| Git | https://git-scm.com/downloads |
| Docker Desktop | https://www.docker.com/products/docker-desktop |

**Windows users:** Docker Desktop requires WSL 2. The installer will prompt you to enable it. Follow Docker's instructions during install.

**Mac users:** Install Docker Desktop for Mac (Apple Silicon or Intel depending on your chip). You can check your chip under Apple menu -> About This Mac.

After installing Docker Desktop, make sure it is **running** (the Docker whale icon should be visible in your taskbar/menu bar) before running any commands below.

---

## MVC Architecture Overview

This project follows the **Model-View-Controller** pattern. Every feature you build should be split across these three responsibilities. Do not mix them.

```
Request -> Router -> Controller -> Model -> Controller -> View -> Response
```

### Model
The Model represents your data and all business logic tied to it. Models live in `app/Models/` and map directly to a database table via Laravel's Eloquent ORM. A model is responsible for:
- Defining the shape of a database record (fillable fields, casts, relationships)
- Querying and persisting data
- Defining relationships to other models (`hasMany`, `belongsTo`, etc.)

A Model should **not** know anything about HTTP requests or how data is displayed.

```php
// app/Models/Post.php
class Post extends Model {
    protected $fillable = ['title', 'body', 'user_id'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
```

### View
The View is the HTML that gets sent to the browser. Views live in `resources/views/` and use Laravel's **Blade** templating engine (`.blade.php` files). A view is responsible for:
- Rendering HTML using data passed to it from the Controller
- Reusable layouts and components (`@extends`, `@include`, `@component`)

A View should **not** query the database or contain business logic. It only displays what it is given.

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
The Controller is the glue between a Route, the Model, and the View. Controllers live in `app/Http/Controllers/` and are responsible for:
- Receiving an HTTP request from a route
- Calling the appropriate Model(s) to fetch or persist data
- Passing data to a View and returning the response

A Controller should **not** contain raw SQL or HTML. Keep controller methods short - if a method grows long, move logic into the Model or a dedicated service class.

```php
// app/Http/Controllers/PostController.php
class PostController extends Controller {
    public function index(): View {
        $posts = Post::latest()->get();
        return view('posts.index', compact('posts'));
    }
}
```

### Router
Routes are not a formal MVC layer, but they are the entry point that ties everything together. Routes live in `routes/web.php` (browser) and `routes/api.php` (API). A route maps a URL + HTTP method to a Controller method.

```php
// routes/web.php
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
```

### The complete flow for a page request

1. Browser sends `GET /posts`
2. `routes/web.php` matches the URL and calls `PostController@index`
3. The controller calls `Post::latest()->get()` on the Model
4. The Model queries MySQL and returns a collection
5. The controller passes the collection to `resources/views/posts/index.blade.php`
6. The View renders HTML and sends it back to the browser

---

## Directory Structure

```
project-root/
├── app/                             # All server-side application code
│   ├── Http/
│   │   └── Controllers/
│   │       └── Controller.php       # [CONTROLLER] Base controller - your controllers extend this
│   ├── Models/
│   │   └── User.php                 # [MODEL] Default User model - add your models alongside this
│   └── Providers/
│       └── AppServiceProvider.php   # Laravel service provider (rarely edited)
├── bootstrap/                       # Laravel bootstrapping - do not edit
│   ├── app.php
│   ├── providers.php
│   └── cache/
├── config/                          # Laravel configuration files - edit these to change app behaviour
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php          # [MODEL] Fake data generator for User - add yours here
│   ├── migrations/                  # [MODEL] Every schema change goes here, never edit the DB directly
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php       # [MODEL] Entry point for seeding the database
├── docker/                          # Docker support files - do not edit unless you know what you're doing
│   ├── mysql/
│   │   └── init.sql
│   ├── nginx.conf
│   └── start.sh
├── public/                          # Web root - only index.php and compiled assets live here, do not put app logic here
│   ├── index.php
│   ├── favicon.ico
│   ├── robots.txt
│   └── .htaccess
├── resources/
│   ├── css/
│   │   └── app.css                  # Raw CSS - compiled by Vite
│   ├── js/
│   │   ├── app.js                   # Raw JS entry point - compiled by Vite
│   │   └── bootstrap.js
│   └── views/                       # [VIEW] All Blade templates live here
│       └── welcome.blade.php        # Default Laravel welcome page - replace or extend this
├── routes/
│   ├── web.php                      # [ROUTER] Browser-facing routes -> Controllers
│   └── console.php                  # Artisan console command routes
├── storage/                         # Logs, cache, sessions - git-ignored, never commit contents
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── TestCase.php
│   ├── Feature/
│   │   └── ExampleTest.php          # Tests that cover full request -> response flows
│   └── Unit/
│       └── ExampleTest.php          # Tests that cover individual classes and methods
├── .editorconfig                    # Editor formatting rules (committed - keeps code style consistent across editors)
├── .env                             # Your local environment variables - git-ignored, never commit this
├── .env.example                     # Shared environment variable template - committed to git
├── .gitattributes                   # Git line-ending and diff settings
├── .gitignore
├── artisan                          # Laravel CLI entry point - do not edit
├── composer.json                    # PHP dependency definitions
├── composer.lock                    # Locked PHP dependency versions - always commit this
├── docker-compose.yml               # Docker service definitions
├── Dockerfile                       # PHP/Nginx container definition
├── package.json                     # JS dependency definitions (for Vite/frontend assets)
├── phpunit.xml                      # PHPUnit test configuration
├── README.md
└── vite.config.js                   # Vite bundler configuration for CSS/JS assets
```


---

## First-Time Setup

Do this once after cloning the repo.

### 1. Clone the repository (if not done already)
Go to a directory you want to place the project in to access later before running these commands. The clone command will create a separate `csci-486-capstone` directory automatically within whatever directory you're already in.
```bash
git clone https://github.com/Camryn2223/csci-486-capstone.git
cd csci-486-capstone
```

### 2. Build and start the containers

```bash
docker compose up --build -d
```

This will build the PHP/Nginx image, pull the MySQL image, install Composer dependencies, generate your `APP_KEY`, run database migrations, and start all services in the background. Wait for the containers to finish starting before moving on - you can watch progress with `docker compose logs app -f` and wait until you see `ready to handle connections`.

### 3. Open the app

Go to [http://localhost:8080](http://localhost:8080) in your browser.

---

## Running the Container

After first-time setup, start the containers with:

```bash
docker compose up -d
```

The `-d` flag runs them in the background. Your app will be at [http://localhost:8080](http://localhost:8080).

To see live logs:

```bash
docker compose logs -f
```

---

## Stopping the Container

```bash
docker compose down
```

This stops and removes the containers but **preserves your database data**.

---

## Resetting the Container

Use this if a container is broken or you want a clean rebuild of the image (e.g., after Dockerfile changes):

```bash
docker compose down
docker compose up --build -d
```

This rebuilds the image from scratch and restarts everything. Your database volume is preserved.

---

## Resetting the MySQL Database

> ⚠️ This permanently deletes all data in your local database. Use only when you want a clean slate.

```bash
docker compose down -v
docker compose up -d
docker compose exec app php artisan migrate
```

The `-v` flag removes the named Docker volume that stores MySQL data. On the next `up`, the database is recreated from scratch and migrations are re-run.

---

## phpMyAdmin

phpMyAdmin is included and runs automatically alongside the app. Once your containers are up, go to:

**[http://localhost:8081](http://localhost:8081)**

You will be logged in automatically using the credentials from your `.env` file (`DB_USERNAME` and `DB_PASSWORD`). From here you can browse tables, run SQL queries, inspect rows, and watch your data change in real time as you use the app - no extra login required.

## Syncing and Copying Composer to LocaL
```bash
docker compose down
docker compose up --build -d
docker compose cp app:/var/www/html/vendor ./vendor
```

---

## Syncing with Git

### Daily workflow

```bash
# Pull the latest changes before starting work
git pull origin main

# After pulling, re-run migrations in case new ones were added
docker compose exec app php artisan migrate
```

### If a teammate added new dependencies (composer.json changed)

The container installs dependencies automatically on start. If you notice errors related to missing classes, rebuild:

```bash
docker compose down
docker compose up --build -d
```

### If a teammate changed `.env.example`

Check the diff and manually copy any new variables into your local `.env`:

```bash
git diff HEAD~1 .env.example
```

Then open your `.env` and add any missing keys.

### Never commit `.env`

The `.env` file is in `.gitignore` and must never be committed. It contains secrets and local values that differ per machine. Always use `.env.example` to share new environment variables with the team (with placeholder values, never real secrets).

---

## Where to Put Your Code

### Models - `app/Models/`

One file per database table. The model defines the data shape, relationships, and any logic for querying or mutating that data.

```bash
docker compose exec app php artisan make:model Post -m
```
The `-m` flag creates a matching migration at the same time.

Relationship methods, query scopes, and attribute accessors/mutators all belong on the model. Raw SQL does not - use Eloquent methods.

### Controllers - `app/Http/Controllers/`

One file per resource. A controller should have at most these 7 methods, matching standard CRUD actions:

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
The `--resource` flag stubs all 7 methods for you.

### Views - `resources/views/`

One subfolder per resource, mirroring your controller. A controller named `PostController` should use views in `resources/views/posts/`.

```
resources/views/
├── layouts/
│   └── app.blade.php       # Main page shell (nav, header, footer)
├── posts/
│   ├── index.blade.php     # PostController@index
│   ├── create.blade.php    # PostController@create
│   ├── edit.blade.php      # PostController@edit
│   └── show.blade.php      # PostController@show
└── users/
    └── ...
```

Views receive data from the controller via `view('posts.index', compact('posts'))`. Never query the database inside a view.

### Routes - `routes/web.php` and `routes/api.php`

Register a full set of resourceful routes for a controller in one line:

```php
Route::resource('posts', PostController::class);
```

This automatically creates all 7 routes (index, create, store, show, edit, update, destroy) and maps them to the correct controller methods.

Use `routes/web.php` for anything that returns HTML. Use `routes/api.php` for anything that returns JSON.

### Migrations - `database/migrations/`

Every change to the database schema (new table, new column, dropped column) must be a migration. Never edit the database directly.

```bash
docker compose exec app php artisan make:migration add_published_at_to_posts_table
```

After creating or pulling new migrations:

```bash
docker compose exec app php artisan migrate
```

### Seeders - `database/seeders/`

Use seeders to populate the database with default or test data. Run them with:

```bash
docker compose exec app php artisan db:seed
```

---

### Artisan quick reference

All `artisan` commands must be run inside the container:

```bash
docker compose exec app php artisan <command>
```

| Command | What it does |
|---------|--------------|
| `make:model Post -m` | Create a model + migration |
| `make:controller PostController --resource` | Create a resourceful controller |
| `make:migration add_field_to_table` | Create a migration |
| `make:seeder PostSeeder` | Create a seeder |
| `migrate` | Run pending migrations |
| `migrate:rollback` | Undo the last batch of migrations |
| `db:seed` | Run seeders |
| `route:list` | List all registered routes |
| `tinker` | Open an interactive Laravel shell |