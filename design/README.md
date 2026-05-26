# Scavenger Hunt — Design Site

A static playground for the visual design of the three Scavenger Hunt apps
(`admin/`, `archive/`, `race-frontend/`). Plain HTML + Tailwind + vanilla JS.
**No backend, no framework.** Nothing here ships to production directly —
approved designs are ported by hand into the Symfony Twig templates.

> Owned by `design-agent`. Anything outside `design/` is out of scope here.

## Local development

```bash
cd design
npm install
npm run watch     # rebuilds dist/css/app.css on file change
```

Then open `design/index.html` directly in a browser, or any of the mock pages
under `design/archive/`, `design/admin/`, `design/race-display/`.

The router uses `fetch()` against partial files, so pages must be served from
a path the browser can read. Opening `index.html` via `file://` works in modern
browsers; if your browser blocks local fetches, run any static server, e.g.:

```bash
npx http-server design -p 8080
```

## One-shot build

```bash
cd design
npm run build     # writes minified design/dist/css/app.css
```

The GitHub Pages workflow runs this in CI and serves the result. `design/dist/`
is gitignored — never commit built CSS.

## Structure

```
design/
├── index.html                    # landing page + hash-router shell
├── design-system.html            # the source of truth for tokens
├── partials/                     # router fragments (home, etc.)
├── archive/index.html            # archive mock
├── admin/list.html, layout.html  # admin mocks
├── race-display/
│   ├── active.html               # participant view, default theme
│   └── themes/
│       └── default.css           # CSS variables for the default race theme
├── src/
│   ├── styles/input.css          # Tailwind entry + @layer components
│   └── js/
│       ├── router.js             # hash router
│       └── themes.js             # race-theme switcher
├── tailwind.config.js            # tokens live here
├── postcss.config.js
└── package.json
```

## Design tokens

All tokens are declared in `tailwind.config.js` under `theme.extend`:

- **Colors**: `brand`, `neutral`, and `success` / `warn` / `danger` / `info`
- **Fonts**: `font-sans` (Inter), `font-display` (Fraunces), `font-mono`
- **Type scale**: standard Tailwind sizes, slightly tighter line heights
- **Radii**: `rounded` / `rounded-md` / `rounded-lg` / `rounded-xl`
- **Shadows**: `shadow-soft`, `shadow-card`, `shadow-pop`, `shadow-focus`

Reusable component classes (`.btn-primary`, `.card`, `.pill-*`, `.table`,
`.nav-link`, `.progress`, …) live in `src/styles/input.css` under
`@layer components`. Use them instead of repeating long utility strings.

## Adding a race-display theme

The race display reads colors and typography from CSS custom properties
scoped under `[data-theme="<name>"]`. To add a theme:

1. Copy `race-display/themes/default.css` to
   `race-display/themes/<your-theme>.css` and adjust the variables.
2. Add an entry to the `THEMES` array in `src/js/themes.js`:
   ```js
   { id: 'your-theme', label: 'Your theme', stylesheet: 'race-display/themes/your-theme.css' }
   ```
3. The picker in the design-site header and on `race-display/active.html`
   will pick it up automatically.

That is the whole contract: variables + a picker entry. The HTML on every
race-display page uses `var(--race-…)` exclusively, so no markup changes are
needed.

## Hand-off to the Symfony sites

Approved designs are **not** automatically applied to the Twig templates.
The flow is:

1. `design-agent` produces and iterates on mocks here.
2. The PR for a design change gets reviewed visually (open the page locally
   or wait for the GitHub Pages deploy).
3. Once approved, a follow-up issue is filed: "Port `<page>` design into
   `<symfony-app>`." That issue is picked up by `development-agent`, which
   touches Twig, controllers, and Symfony assets.

`design-agent` never edits the Symfony sites itself. The PR body for a
design PR should describe how the design maps to the existing Twig
partials so the porting issue can be scoped quickly.

## Deployment

Pushes to `main` that change anything under `design/` (or the workflow
itself) run `.github/workflows/deploy-design-site.yml`, which builds the
CSS and publishes the whole `design/` directory to GitHub Pages.

The Pages source must be set to "GitHub Actions" in the repo settings —
this is a one-time manual toggle.
