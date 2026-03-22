# Onfly Corporate Travel

Monorepo application with a Laravel 12 API backend and Vue.js 3.5 frontend, orchestrated via Docker Compose with profiles.

## Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | Laravel 12, PHP 8.5, MySQL 8.4     |
| Frontend   | Vue.js 3.5, Vite, Tailwind CSS 4   |
| Infra      | Docker, Docker Compose (Profiles)   |
| Testing    | PHPUnit                             |

## Project Structure

```
/
├── api/                  # Laravel 12 application
│   ├── Dockerfile
│   └── docker/nginx/     # Nginx configuration
├── front/                # Vue.js 3.5 application
│   └── Dockerfile
├── docker-compose.yml    # Profiles: api, front
└── README.md
```

## Prerequisites

- Docker >= 24.0
- Docker Compose >= 2.20

## Getting Started

### 1. Start the API

```bash
docker compose --profile api up -d --build
```

This starts PHP-FPM, Nginx (port **8080**), and MySQL (port **3306**).

### 2. Run Migrations

```bash
docker compose exec api-app php artisan migrate
```

### 3. Start the Frontend

```bash
docker compose --profile front up -d --build
```

Vite dev server available at **http://localhost:5173**.

### 4. Start Everything

```bash
docker compose --profile api --profile front up -d --build
```

## Running Tests

```bash
docker compose exec api-app php artisan test
```

## API Endpoints

| Method | Endpoint        | Description                        |
|--------|-----------------|------------------------------------|
| GET    | `/api/healthy`  | Health check with DB & version info|

## Stopping Services

```bash
docker compose --profile api --profile front down
```

To also remove volumes (database data):

```bash
docker compose --profile api --profile front down -v
```
