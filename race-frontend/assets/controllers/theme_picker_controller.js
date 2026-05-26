import { Controller } from "@hotwired/stimulus";

/*
 * Stimulus port of design/src/js/themes.js.
 *
 * The active race theme is reflected on <html data-theme="…"> and persisted
 * in localStorage. The server picks the initial theme via the `race_theme()`
 * Twig global (RACE_THEME_DEFAULT / `?theme=` query param); this controller
 * lets a user override at runtime and remembers their choice.
 *
 * All race-display surfaces consume CSS variables defined under the matching
 * [data-theme="<id>"] selector, so adding a future theme is purely a CSS
 * change — no Twig or controller edits required.
 */

const THEMES = [
    { id: "default", label: "Default" },
    // Add future themes here AND ship a [data-theme="<id>"] block in CSS.
];

const STORAGE_KEY = "scavenger-hunt.race-theme";

export default class extends Controller {
    connect() {
        this.loadInitialTheme();
        this.renderPicker();
    }

    loadInitialTheme() {
        let stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch (_) {
            /* localStorage unavailable — fall back to server-side theme */
        }
        if (stored) {
            this.applyTheme(stored);
        } else {
            // Server-rendered <html data-theme="…"> is already correct;
            // record it so subsequent visits stay consistent.
            const current = document.documentElement.getAttribute("data-theme");
            if (current) {
                this.applyTheme(current);
            } else {
                this.applyTheme("default");
            }
        }
    }

    applyTheme(themeId) {
        const theme = THEMES.find((t) => t.id === themeId) ?? THEMES[0];
        document.documentElement.setAttribute("data-theme", theme.id);
        try {
            localStorage.setItem(STORAGE_KEY, theme.id);
        } catch (_) {
            /* noop */
        }
    }

    renderPicker() {
        const host = this.element;
        const current =
            document.documentElement.getAttribute("data-theme") || "default";
        host.innerHTML = `
            <label class="text-xs font-medium text-neutral-600 mr-2" for="theme-picker">Race theme</label>
            <select id="theme-picker" class="select w-auto text-sm">
                ${THEMES.map(
                    (t) =>
                        `<option value="${t.id}" ${
                            t.id === current ? "selected" : ""
                        }>${t.label}</option>`
                ).join("")}
            </select>
        `;
        host.querySelector("#theme-picker").addEventListener(
            "change",
            (event) => this.applyTheme(event.target.value)
        );
    }
}
