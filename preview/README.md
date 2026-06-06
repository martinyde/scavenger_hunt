# Preview Stack

A second Docker compose stack that runs a feature-branch worktree
alongside the main scavenger stack, so you can manually exercise an
in-flight PR end-to-end before merging.

This is **developer tooling**. There is no CI integration and no
production analogue.

## When to use it

You're reviewing PR #N and want to click around the running app instead
of just reading the diff. The branch is checked out as a git worktree
under `worktrees/issue-<N>-<slug>/`, but the main stack still serves
`main`'s code from `admin/`, `race-frontend/`, `archive/`.

Spin up the preview, exercise it, tear it down.

## Hostnames

The preview embeds the issue number in every hostname:

- `admin-preview-<N>.scavenger.local.itkdev.dk`
- `race-preview-<N>.scavenger.local.itkdev.dk`
- `archive-preview-<N>.scavenger.local.itkdev.dk`
- `design-preview-<N>.scavenger.local.itkdev.dk`

For example, while previewing issue 17:

- `https://admin-preview-17.scavenger.local.itkdev.dk`
- `https://race-preview-17.scavenger.local.itkdev.dk`
- `https://archive-preview-17.scavenger.local.itkdev.dk`
- `https://design-preview-17.scavenger.local.itkdev.dk`

This is intentional: while only one preview can run at a time, your
browser's history, autocomplete, and saved logins will naturally
separate previews of different issues. Switching from previewing #17
to previewing #23 won't replay #17's URLs.

## Prerequisites

The **main** stack must already be running — the preview reuses its
Mercure hub, RabbitMQ broker, and Mailpit instance via Docker network
sharing. If `task start` (or `task admin:start`) hasn't been run, the
preview admin will fail to publish Mercure events.

You also need DNS for the preview hostnames pointing at the same IP as
your main `*.scavenger.local.itkdev.dk` entries. The itkdev local setup
typically wildcards `*.scavenger.local.itkdev.dk → 127.0.0.1`, which
already covers the issue-numbered subdomains — nothing to add.

If you don't have a wildcard and instead rely on `/etc/hosts`, you'll
need an entry per issue you preview, e.g. for issue 17:

```
127.0.0.1  admin-preview-17.scavenger.local.itkdev.dk
127.0.0.1  race-preview-17.scavenger.local.itkdev.dk
127.0.0.1  archive-preview-17.scavenger.local.itkdev.dk
127.0.0.1  design-preview-17.scavenger.local.itkdev.dk
```

Tip: a wildcard line in `dnsmasq` or `resolver` is much less painful
than per-issue `/etc/hosts` lines. Check `task preview-issue:up`'s
final output for the exact URLs to add if you're going the hosts route.

## Quick start

```bash
# Bring up the main stack first
task start

# Resolve issue #17 → worktrees/issue-17-…/ and start the preview
task preview-issue:up -- 17

# Click around (the final URLs are printed by `:up`)
open https://admin-preview-17.scavenger.local.itkdev.dk
open https://race-preview-17.scavenger.local.itkdev.dk
open https://archive-preview-17.scavenger.local.itkdev.dk
open https://design-preview-17.scavenger.local.itkdev.dk

# Tear down
task preview-issue:down
```

### Design-only fast path

If you're only iterating on the design playground (no Symfony code
changes), spin up just the static design preview — it skips composer
install, the preview DB, migrations, and fixtures:

```bash
task preview-issue:design:up -- 17
open https://design-preview-17.scavenger.local.itkdev.dk
task preview-issue:down
```

`preview-issue:down` tears down everything (including the design
container) with `--remove-orphans`, so there is no separate down task.

## How issue → worktree resolution works

`bin/preview-resolve.sh <N>` reads `git worktree list --porcelain` and
matches branches against `issue-<N>-*`. It errors if zero or multiple
worktrees match — there's no fuzzy logic. Branch names follow the
project convention `issue-<number>-<short-kebab-slug>` (see top-level
`CLAUDE.md`).

If you've forgotten to create a worktree for the issue, do that first:

```bash
git worktree add worktrees/issue-17-remove-admin-progress issue-17-remove-admin-progress
```

## Vendor / node_modules strategy

Worktrees don't contain `vendor/` or `node_modules/` (both gitignored),
and we don't want to run `composer install` every preview boot when the
deps haven't changed.

For each Symfony site (admin, race-frontend, archive), `preview-issue:up`
compares the worktree's `composer.lock` against main's:

- **Locks match** → bind-mount main's `vendor/` into the preview
  container. Instant boot.
- **Locks differ** (or main has no `vendor/` yet) → mount a docker named
  volume `scavenger_preview_<site>_vendor` and run `composer install`
  inside the container.

`package-lock.json` / `node_modules` follow the same rule. All three
Symfony sites have a node-based asset pipeline (webpack-encore +
Tailwind), and `public/build/` is gitignored, so `npm run build` always
runs against the worktree on every `:up` — only the `npm ci` step is
skipped when the lockfile matches main.

The `design/` playground uses the same `decide_mount` logic for its
`node_modules` and is built the same way on every `:up` (or via
`preview-issue:design:up` for the design-only fast path). Its build
output lives at `design/dist/css/app.css`, which the design-nginx
container serves alongside the hand-written HTML pages in `design/`.

Named volumes are wiped by `preview-issue:down --volumes`, so deps are
rebuilt fresh on the next `:up`.

## Database, fixtures, migrations

The preview gets its **own** MariaDB container, on its own
`preview_app` network, with its own `mariadb_data` volume scoped to the
`scavenger_preview` compose project. It does not share data with the
main stack.

`preview-issue:up` always runs `doctrine:migrations:migrate`. Fixtures
load conditionally:

- First `:up` (no rows in `user` table) → fixtures load.
- Subsequent `:up` (data present) → fixtures skipped, state preserved.
- Force reload with `task preview-issue:fixtures`. This re-runs
  `doctrine:fixtures:load --no-interaction` (which purges with DELETE,
  not TRUNCATE — needed because FK constraints prevent TRUNCATE).

## Shared infrastructure (Mercure / Rabbit / Mail)

The preview containers join the main stack's `scavenger_app` Docker
network in addition to their own. That gives them DNS access to:

- `scavenger-mercure-1:3000` — the existing Mercure hub
- `scavenger-rabbit-1:5672` — the existing RabbitMQ broker
- `scavenger-mail-1:1025`   — the existing Mailpit instance

The preview admin publishes Mercure events to the same hub as main.
A browser session against `admin-preview-<N>.scavenger.local.itkdev.dk`
and one against `admin.scavenger.local.itkdev.dk` will both receive
each other's events over EventSource. This is a deliberate trade-off —
we'd rather share the hub than burn another port and another container.

If that's a problem for a specific review (e.g. you're testing a change
to Mercure topic naming), stop the main stack first.

## Cross-site links

The preview admin sets `RACE_FRONTEND_BASE_URL` and `ARCHIVE_BASE_URL`
in its compose `environment:` block to the issue-numbered preview
hostnames, so any `{{ race_frontend_base_url }}` /
`{{ archive_base_url }}` link in templates points at the preview app —
never at main.

These env vars were introduced in PR #22; if the worktree you're
previewing predates that, the URLs simply have no effect (templates
won't reference them).

## One preview at a time

`preview-issue:up` errors out if any container with the
`com.docker.compose.project=scavenger_preview` label is running:

```
error: a preview stack is already running:
       - scavenger_preview-admin-nginx-1
       - scavenger_preview-admin-phpfpm-1
       …
Run 'task preview-issue:down' first.
```

There's no silent swap and no parallel preview support. If you need
that, open a follow-up issue.

Note that the issue-numbered hostnames do **not** loosen this
constraint — the guard is keyed on the compose project label, not on
hostname. Multiple previews at once would still collide on the shared
preview DB volume and on the `scavenger_app` network attachment.

## Targets

| Target | Description |
|---|---|
| `task preview-issue:up -- <N>` | Resolve issue #N to a worktree, bring up the full preview stack (admin, race, archive, design), run migrations, load fixtures (only if DB is empty). |
| `task preview-issue:design:up -- <N>` | Bring up ONLY the design playground for issue #N — no PHP, no DB. Fast path when iterating on design-only changes. |
| `task preview-issue:down` | Tear down containers, network, and the preview DB volume (sweeps design containers too via `--remove-orphans`). |
| `task preview-issue:fixtures` | Force-reload Doctrine fixtures into the preview DB (destructive). |
| `task preview-issue:logs` | Tail logs from all preview containers. |
| `task preview-issue:status` | Show which preview containers are currently running. |

## Files

- `preview/docker-compose.yml` — the single compose file (this directory).
- `bin/preview-resolve.sh` — issue number → worktree path resolver.
- `Taskfile.yml` (repo root) — `preview-issue:*` targets.

## Limitations / known gotchas

- **Main stack must be up.** The preview leans on main for Mercure /
  Rabbit / Mail. If main is down, the preview admin will start but
  publishing and async messaging will fail.
- **Shared Mercure hub.** Events leak between main and preview admin —
  see "Shared infrastructure" above.
- **No parallel previews.** One worktree at a time. Hard guard.
- **No CI integration.** This is local-only dev tooling.
- **Worktrees gitignored.** A worktree must exist on disk at
  `worktrees/issue-<N>-<slug>/`. Create one with `git worktree add` if
  needed.
- **`/etc/hosts` users:** without a wildcard, you need three lines per
  issue you intend to preview (one per site). Strongly prefer a real
  wildcard at the resolver level.
