// Theme switcher for the race-display mock.
// Themes are CSS files under design/race-display/themes/<name>.css.
// Each defines variables on [data-theme="<name>"]; the active theme is applied
// to <html data-theme="…"> and persisted in localStorage.

const THEMES = [
  { id: 'default', label: 'Default', stylesheet: 'race-display/themes/default.css' },
  // Add new themes here AND drop a CSS file at race-display/themes/<id>.css.
];

const STORAGE_KEY = 'scavenger-hunt-design.theme';

function ensureStylesheet(theme) {
  const id = `theme-css-${theme.id}`;
  if (document.getElementById(id)) return;
  const link = document.createElement('link');
  link.id = id;
  link.rel = 'stylesheet';
  link.href = theme.stylesheet;
  document.head.appendChild(link);
}

export function applyTheme(themeId) {
  const theme = THEMES.find((t) => t.id === themeId) ?? THEMES[0];
  ensureStylesheet(theme);
  document.documentElement.setAttribute('data-theme', theme.id);
  try { localStorage.setItem(STORAGE_KEY, theme.id); } catch (_) { /* noop */ }
}

export function loadInitialTheme() {
  let stored = null;
  try { stored = localStorage.getItem(STORAGE_KEY); } catch (_) { /* noop */ }
  applyTheme(stored || 'default');
}

export function renderPicker(host) {
  if (!host) return;
  const current = document.documentElement.getAttribute('data-theme') || 'default';
  host.innerHTML = `
    <label class="text-xs font-medium text-neutral-600 mr-2" for="theme-picker">Race theme</label>
    <select id="theme-picker" class="select w-auto text-sm">
      ${THEMES.map((t) => `<option value="${t.id}" ${t.id === current ? 'selected' : ''}>${t.label}</option>`).join('')}
    </select>
  `;
  host.querySelector('#theme-picker').addEventListener('change', (e) => applyTheme(e.target.value));
}

// Auto-bootstrap when imported as a module on a page.
loadInitialTheme();
document.addEventListener('DOMContentLoaded', () => {
  renderPicker(document.querySelector('[data-theme-picker]'));
});
