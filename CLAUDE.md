# Scavenger Hunt — Workflow Conventions

## Default flow for any feature, change, or bug-fix request

When the user asks for a feature, change, or bug fix, the main agent must orchestrate this chain — do **not** make changes directly in the main session:

1. **Issue first.** Delegate to `issue-agent` to draft and create a GitHub issue. Show the draft to the user and wait for confirmation before creating.
2. **Worktree + branch.** Create a worktree (or branch) named after the issue (e.g. `issue-<number>-<short-slug>`).
3. **Implement.** Delegate to `development-agent`, which honors its own checkpoints (approach approval, manual-test pause, quality/test review).
4. **Commit, push, PR.** Delegate to `git-agent`. The PR body must include `Closes #<issue-number>`.
5. **Stop at the PR.** Never merge. Report the PR URL to the user and wait.

### Design work vs. development work vs. infrastructure work

Three specialist agents cover three distinct surfaces. Pick the one that matches the issue's primary scope:

- **`design-agent`** — purely visual work: new mockups, design-system changes, race-display themes, anything under `design/`. Scoped strictly to the `design/` playground; produces Tailwind/HTML mockups. Never touches `admin/`, `archive/`, `race-frontend/`, `shared/`, or project infra. When an approved design needs to land in a Symfony app, that's a follow-up issue for `development-agent`.
- **`development-agent`** — application code in `admin/`, `race-frontend/`, `archive/`, and `shared/`. PHP, Twig, JS/CSS, composer/package manifests, migrations, fixtures. Does not edit project-level tooling.
- **`infra-agent`** — project infrastructure: `Taskfile.yml`, `docker-compose*.yml`, `bin/` helper scripts, `.github/` workflows, root-level repo config (root `README.md`, `.editorconfig`, `.gitignore`, etc.). Does not edit application source code.

Do not ask one agent to do another's work. If a single issue would genuinely span scopes (rare), split it into two issues — one per agent.

### When to skip the flow

Skip the issue/PR chain only for:

- Questions and explanations (no code change)
- One-line typo fixes the user explicitly asks to apply directly
- Edits to local config under `.claude/` that don't ship to production
- Anything the user explicitly says to "just do" or "skip the issue for"

When in doubt, ask which mode the user wants before starting.

## Prerequisites the user must keep working

- `gh` must be authenticated (`gh auth status` green). If not, the chain halts at step 1 — surface this immediately rather than trying to push without auth.
- Remote `origin` must point at the GitHub repo (currently `git@github.com:martinyde/scavenger_hunt.git`).

## Branch and worktree conventions

- Never commit to `main`.
- Worktrees live under `worktrees/<name>/` (gitignored). They are intentionally *outside* `.claude/` because sub-agents do not have write access to `.claude/**` even in `acceptEdits` mode.
- Branch names: `issue-<number>-<short-kebab-slug>` when an issue exists; otherwise `<type>/<slug>` (e.g. `feat/sites-open-task`).

## Commit and PR style

Follow the formats in `.claude/agents/git-agent.md`. Conventional-commit type prefix, PR with Summary + Test plan + `Closes #N`.
