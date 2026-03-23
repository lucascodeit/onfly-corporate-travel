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

### 2. Generate JWT Secret

Generate the secret key used to sign authentication tokens:

```bash
docker compose exec api-app php artisan jwt:secret
```

This writes a random `JWT_SECRET` value into `api/.env`.

### 3. Run Migrations

```bash
docker compose exec api-app php artisan migrate
```

### 4. Create Admin User

Bootstrap the first admin user to access the platform:

```bash
docker compose exec api-app php artisan user:create-admin
```

The command will prompt for first name, last name, email, and password.

### 5. Start the Frontend

```bash
docker compose --profile front up -d --build
```

Vite dev server available at **http://localhost:5173**.

### 6. Start Everything

```bash
docker compose --profile api --profile front up -d --build
```

## Running Tests

```bash
docker compose exec api-app php artisan test
```

## API Collection (Bruno)

This project includes a [Bruno](https://www.usebruno.com/) collection with ready-to-use requests for every API endpoint, located at `api/bruno-collection/`.

### What is Bruno?

Bruno is a free, open-source API client — an alternative to Postman and Insomnia. Collections are stored as plain-text files directly in the repository, so they stay version-controlled alongside the code.

### How to use

1. Download and install Bruno from [usebruno.com](https://www.usebruno.com/).
2. Open Bruno and click **Open Collection**.
3. Select the `api/bruno-collection/` folder.
4. In the top-right environment dropdown, select **local**.
5. Run the **auth/login** request first — it automatically saves the JWT token for all other requests.
6. Browse the folders and execute any request.

### Available request folders

| Folder | Requests |
|---|---|
| `auth` | login, refresh_token, logout |
| `profile` | get_profile, update_profile, change_password |
| `users` | list_users, create_user, show_user, update_user, delete_user, change_user_password |
| `travel_requests` | list_travel_requests, create_travel_request, cancel_travel_request |
| `admin_travel_requests` | list_all_travel_requests, approve_travel_request, disapprove_travel_request |
| `notifications` | list_notifications, unread_count, show_notification, mark_as_read |
| `health` | health_check |


## Stopping Services

```bash
docker compose --profile api --profile front down
```

To also remove volumes (database data):

```bash
docker compose --profile api --profile front down -v
```

## Destroying everything (Docker)

From the repository root, tear down **all** Compose resources for this project: containers, the `onfly-network` network, and named volumes (including **MySQL data** in `mysql-data`). This does not delete your source code on disk.

```bash
docker compose --profile api --profile front down -v --remove-orphans
```

To also remove **images built** by this Compose file (e.g. `api-app`, `front`), add `--rmi local` (only images without a custom tag) or `--rmi all` (every image used by these services):

```bash
docker compose --profile api --profile front down -v --remove-orphans --rmi local
```

**Note:** `down -v` is irreversible for database data. Use `down` without `-v` if you only want to stop containers and keep the volume.
