# Mobile `/race/{id}` mock — design notes

These three mockups (`active-search.html`, `active-list.html`,
`active-task.html`) are a single design proposal for the participant-facing
mobile hub. They are NOT three separate routes — they are three default
panels of the same hub. Reviewers can open any file to land on that panel.

## Sample data baked into the mocks

| Field            | Value                                            |
| ---------------- | ------------------------------------------------ |
| Team name        | `Team Cat`                                       |
| Team avatar      | Inline cat-silhouette SVG (placeholder)          |
| Timer            | `14:32` (left phone), `14:01` (right phone)      |
| Progress         | `3 / 12`                                         |
| Word-search size | 10×10                                            |
| Found keys       | `CAT` (task solved), `DOG` (task open)           |
| Locked keys      | 10 chips of varying lengths, shown as `• • •`     |
| Active task      | "Name the breed on the brewery sign."            |
| Correct answer   | `Dachshund`                                      |
| Wrong sample     | `Beagle` with two penalty guesses (+60 s)        |

## Data assumptions for the port

These are the schema deltas the design implies. The implementation issue
should treat them as design intent, not as ratified DB changes.

- **`Task` gains a `keyWord` column** (string, uppercase, unique within a
  Race). It is what links a task to a word in the search grid.
- **`Task` has a `textBefore` and `textAfter` field** (already implied by
  the existing race-display mock; mobile inherits the same fields).
- **`Race` gains a `wordSearchGrid`** — a 10×10 array of single letters,
  generated server-side from the chosen keys. The mock just hard-codes
  one; the port will need a generator service.
- **`Participant` (or `Team`) gains an `avatar`** — either a stored image
  URL or a deterministic SVG. The mock uses a cat-shaped placeholder SVG
  so the markup is image-free for now.
- **Per-team progress lives on the `Participant` aggregate** (we render
  `3/12` in the top bar). Already implied by existing progress logic.
- **A `foundKeys` collection per Participant** — list of `keyWord` values
  the team has located in the grid. This drives:
  - Which letters render with `.is-found-strong` in the grid.
  - Which chips render with `.is-found` / `.is-found-done` in the rail.
  - Which tasks render in the task list (all *other* tasks are hidden).

## Navigation contract (mocked here, must be honored by the port)

All three of these must always work, on every panel:

1. **Tap the tab** in the sticky tab strip → switches panel instantly.
2. **Horizontal swipe** inside the panel viewport → next / previous panel.
3. **Tap a found-key chip** on the Word-search panel → jumps to Task detail.
4. **Tap a task row** on the Task-list panel → jumps to Task detail.
5. **Correct submit** on Task detail → slides back to Word search after
   ~600 ms.
6. **Wrong submit** on Task detail → stays on Task detail, increments
   `guesses` counter, shows wrong-feedback banner.

The mock implements all of these in `mobile-hub.js` (vanilla, ~80 LOC).

## Open questions for the implementation issue

- **Do guesses cost real time?** The mocks claim `+30 s per wrong guess`
  but the existing `Race` entity does not (yet) carry a penalty model.
  Decision needed: (a) penalty is purely cosmetic feedback, (b) penalty
  reduces the displayed countdown only, (c) penalty actually shortens the
  race for that team.
- **What happens when the word search is fully solved before the timer
  ends?** The mocks don't cover this. Options:
  1. Hide the search panel (only Tasks + Detail remain in the tab strip).
  2. Keep the search panel but render the grid in a "all-found" celebratory
     state.
  3. Auto-collapse the search panel into a congratulatory banner inside
     the Tasks panel.
  Recommendation: option 2 — keeps tab layout stable and matches the
  existing mock's "rank chip" celebratory pattern. Confirm before porting.
- **How are locked chips visually communicated?** Currently shown as
  `• • •` dots whose count equals the keyWord length. This leaks puzzle
  difficulty (length) on purpose, but if that is considered too much of a
  hint, replace with a fixed-width `•••` and a small lock icon.
- **Does the top bar collapse on scroll?** The mock keeps it sticky and
  always visible. If the panels grow longer (very wordy tasks), consider
  hiding everything except the timer on scroll. Out of scope for v1.
- **Should the answer input be `type="text"` or `type="search"`?** The
  mock uses `type="text"` with `autocapitalize="characters"`. The port
  should confirm whether server validation is case-insensitive (assumed
  yes in the mock).
- **Real avatar source.** The mock embeds an SVG. The port should decide
  between (a) uploaded images on `Team`, (b) deterministic SVG initials.
  If (b), the `.race-avatar` primitive already added for `progress.html`
  is reusable here — drop the SVG and use the initial pattern instead.

## Deferred state (intentionally not designed yet)

- **Race ended / time expired.** Not in scope. The current mocks always
  show a positive timer.
- **All tasks solved.** Same — assumed mid-race.
- **Network offline / submission failure.** No design for the toast or
  inline error banner. The `.race-feedback-wrong` style would carry over
  but the copy is different ("Couldn't reach the server" rather than
  "Not quite").
- **Reduced-motion preference.** The horizontal slide animation uses a
  250 ms transform transition; respect `prefers-reduced-motion` in the
  port by clamping the duration to 0 ms.

## Files / tokens / classes introduced

New themed primitives added to `design/race-display/themes/default.css`
(all read from existing `--race-*` variables, so themes still work):

- Topbar: `.race-topbar`, `.race-topbar-timer`, `.race-topbar-team`,
  `.race-topbar-team-name`, `.race-topbar-avatar`, `.race-topbar-progress`
- Tabs: `.race-tabs`, `.race-tab`
- Panel container: `.race-panels`, `.race-panels-track`, `.race-panel`,
  `.race-panel-eyebrow`, `.race-panel-title`
- Word-search: `.race-wordsearch`, `.race-wordsearch-cell` (+
  `.is-found`, `.is-found-strong` modifiers)
- Key chips: `.race-key-rail`, `.race-key` (+ `.is-found`,
  `.is-found-done`, `.is-locked` modifiers), `.race-key-dot`
- Task list: `.race-task-row`, `.race-task-row-key`,
  `.race-task-row-title`, `.race-task-row-meta`, `.race-task-row-chevron`
- Feedback: `.race-feedback`, `.race-feedback-icon`,
  `.race-feedback-correct`, `.race-feedback-wrong`
- Misc: `.race-hint`, `.race-hint-label`, `.race-task-narration`,
  `.race-task-narration-muted`, `.race-state-toggle`, `.race-phone`

No new CSS variables were added — every primitive consumes the existing
`--race-*` set so the theme picker keeps working.

## Hand-off summary for the Symfony port

- **Twig template (likely):** `race-frontend/templates/race/active.html.twig`
  (or a new `active_mobile.html.twig` partial included from it once the
  layout decision is made).
- **Single route, three panels.** Don't model the three files here as
  three Symfony routes. The route stays `/race/{id}`. Panel selection is
  client-side state, optionally synced to a URL hash (`#search` /
  `#list` / `#task`).
- **Mercure topic.** When `foundKeys` updates server-side, push a
  `race.{id}.team.{teamId}.keyFound` event so the grid + chips + task
  list all refresh without a page reload.
- **Submission handler.** Existing answer-submit controller can stay;
  the mobile mock just expects a JSON response with `{ ok: bool,
  guesses: int }` so the wrong-state banner can render the running
  guess count.
