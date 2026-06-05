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
| `task preview-issue:up -- <N>` | Bring up a preview stack pointed at the worktree for issue #N (see [preview/README.md](preview/README.md)) |
| `task preview-issue:down` | Tear down the preview stack |

## Preview Stack (manual PR review)

To exercise an in-flight feature branch end-to-end without merging it,
spin up a second compose stack that mounts the branch's git worktree:

```bash
task preview-issue:up -- 17    # finds worktrees/issue-17-*/, brings up the preview
task preview-issue:down        # tears it down
```

Reachable at `https://{admin,race,archive}-preview-<N>.scavenger.local.itkdev.dk`
(the issue number is embedded in the hostname so browser history,
autocomplete, and saved logins separate previews of different issues).
See [`preview/README.md`](preview/README.md) for the full workflow,
including DNS setup, fixture handling, and shared-infra trade-offs.

## Development Agents

This project includes a set of Claude Code development agents (`.claude/agents/`) to support the development workflow. All agents operate on feature branches — merging to `main` always requires manual approval. Manage agents interactively with the `/agents` command in Claude Code.

### Available Agents

| Agent | Model | Color | Tools | Delegates to |
|-------|-------|-------|-------|-------------|
| **issue-agent** | sonnet | blue | Read, Grep, Glob, Bash | — |
| **development-agent** | opus | green | Read, Write, Edit, Grep, Glob, Bash | code-quality-agent, test-agent |
| **code-quality-agent** | sonnet | yellow | Read, Write, Edit, Grep, Glob, Bash | — |
| **test-agent** | sonnet | purple | Read, Write, Edit, Grep, Glob, Bash | issue-agent |
| **git-agent** | sonnet | orange | Read, Grep, Glob, Bash (no Write/Edit) | — |

### How to Invoke

Ask Claude to run the agent by name, for example:
- "Run the code quality agent on admin"
- "Use the development agent to implement issue #12"
- "Run the test agent on the changed files"
- "Create a GitHub issue for adding rate limiting to the API"
- "Commit and create a PR for my changes"

### Development Workflow

```
1. Issue        — User or issue-agent creates a GitHub issue
2. Develop      — development-agent implements the feature/fix on a feature branch
3. Quality      — code-quality-agent checks and fixes coding standards
4. Test         — test-agent runs tests and verifies changes
5. Git          — git-agent commits, pushes, and creates a PR
6. User Review  — User reviews the PR and merges to main manually
```

### Safety Rules

- Agents never push to `main` or merge PRs — user approval is always required
- All package installs (composer, npm) run inside Docker containers, never on the host
- Agents cannot read `.env.local` files or `~/.ssh`
- No force pushes or `--no-verify` flags
- Agents cannot change git remotes
- Per-agent tool scoping is defined via frontmatter in each agent's `.md` file
- Universal deny rules are enforced in `.claude/settings.json`
