# Scavenger Hunt - Multi-Site Architecture

This repository contains three Symfony sites that together form the Scavenger Hunt application. Each site has its own Docker setup and can be deployed independently.

## Sites

### Admin (`admin/`)

Full Symfony application that owns the database. Includes shared infrastructure (MariaDB, RabbitMQ, Mercure, Mailpit) in its `docker-compose.yml`. Handles:

- User authentication (login, register, password reset)
- CRUD for scavenger hunts, tasks, races, participants, highscores
- Race management (start, finish, timer)
- Internal REST API (`/api/v1/`) consumed by the other two sites — interactive docs at `/api/docs`
- Mercure publishing (JSON events for real-time updates)
- Background workers (RabbitMQ messenger for race end detection)

### Race Frontend (`race-frontend/`)

Lightweight Symfony application for participant gameplay. No database access. Handles:

- Joining races as a participant
- Race display with real-time updates via Mercure EventSource
- Task access key entry and solution guessing
- Highscore viewing

All data is fetched from the Admin site's API via the shared `AdminApiClient`.

### Archive (`archive/`)

Symfony application for browsing past and current hunts. No database access. Handles:

- Browsing available scavenger hunts
- Creating races (via the Admin API)
- Viewing highscores

Serves both HTML pages (for browser access) and JSON responses (for programmatic access via `Accept: application/json` header).

## Shared Library (`shared/`)

Local Composer package containing:

- `AdminApiClient` - HTTP client for the Admin API
- DTOs - Data Transfer Objects (`RaceDTO`, `ParticipantDTO`, etc.)

Referenced by `race-frontend` and `archive` as a Composer path dependency.

## Getting Started

Each site has its own `.env` file for configuration.

```bash
# Start all three sites (admin first, since it hosts shared infra)
task start

# Or start individually
task admin:start
task race:start
task archive:start
```

Access the sites via Traefik:

- Admin: `https://admin-{COMPOSE_DOMAIN}`
- Race Frontend: `https://race-{COMPOSE_DOMAIN}`
- Archive: `https://archive-{COMPOSE_DOMAIN}`
- Mercure: `https://mercure-{COMPOSE_DOMAIN}`

## Docker Architecture

Each site has its own `docker-compose.yml`:

- **`admin/docker-compose.yml`** — phpfpm, nginx, supervisor, plus shared infrastructure (mariadb, rabbit, mail, mercure, node)
- **`race-frontend/docker-compose.yml`** — phpfpm, nginx, node. Joins the admin's `app` network for local dev.
- **`archive/docker-compose.yml`** — phpfpm, nginx. Joins the admin's `app` network for local dev.

For production deployment on separate servers, each site runs its own compose file independently. The race-frontend and archive connect to the admin via `ADMIN_API_URL` environment variable (set to the external admin URL instead of Docker internal DNS).

## Taskfile Commands

| Command | Description |
|---------|-------------|
| `task start` | Start all three sites |
| `task stop` | Stop all three sites |
| `task admin:start` | Start admin site + shared infra |
| `task race:start` | Start race-frontend site |
| `task archive:start` | Start archive site |
| `task admin:console -- ...` | Run Symfony console in admin |
| `task admin:migrate` | Run database migrations |
| `task assets:build` | Build frontend assets for admin and race-frontend |
