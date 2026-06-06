/* Mobile race-display hub — tabs + swipe + auto-switch.
   Mock-only JS; the production port wires the same affordances against
   real state. The three panels live in one DOM container; we slide a
   horizontal track and update tab aria-selected.

   Wiring contract (every mobile page uses the same structure):
     [data-race-hub]                container, attr data-active=search|list|task
       [data-race-tabs] [data-tab=search|list|task]   tab buttons
       [data-race-panels]           overflow:hidden viewport
         [data-race-track]          horizontal grid (3 panels, 100% each)
           [data-panel=search] [data-panel=list] [data-panel=task]
*/

const ORDER = ['search', 'list', 'task'];

function findHubs() {
  return Array.from(document.querySelectorAll('[data-race-hub]'));
}

function setActive(hub, name) {
  if (!ORDER.includes(name)) return;
  hub.setAttribute('data-active', name);
  const track = hub.querySelector('[data-race-track]');
  const idx = ORDER.indexOf(name);
  if (track) track.style.transform = `translateX(-${idx * 100}%)`;

  hub.querySelectorAll('[data-tab]').forEach((tab) => {
    tab.setAttribute('aria-selected', String(tab.getAttribute('data-tab') === name));
  });
}

function bindTabs(hub) {
  hub.querySelectorAll('[data-tab]').forEach((tab) => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      setActive(hub, tab.getAttribute('data-tab'));
    });
  });
}

function bindSwipe(hub) {
  const viewport = hub.querySelector('[data-race-panels]');
  if (!viewport) return;
  let startX = null;
  let startY = null;
  viewport.addEventListener('touchstart', (e) => {
    const t = e.changedTouches[0];
    startX = t.clientX;
    startY = t.clientY;
  }, { passive: true });
  viewport.addEventListener('touchend', (e) => {
    if (startX === null) return;
    const t = e.changedTouches[0];
    const dx = t.clientX - startX;
    const dy = t.clientY - startY;
    startX = null;
    startY = null;
    if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
    const current = hub.getAttribute('data-active') || 'search';
    const idx = ORDER.indexOf(current);
    if (dx < 0 && idx < ORDER.length - 1) setActive(hub, ORDER[idx + 1]);
    if (dx > 0 && idx > 0) setActive(hub, ORDER[idx - 1]);
  });
}

function bindKeyboard(hubs) {
  /* Keyboard ←/→ navigates the most-recently-interacted hub. For the simple
     single-hub case (active-search / active-list) that's the only hub. For
     the side-by-side preview page we bias toward whichever hub the pointer
     last entered. */
  let target = hubs[0] || null;
  hubs.forEach((hub) => {
    hub.addEventListener('pointerenter', () => { target = hub; });
    hub.addEventListener('focusin',      () => { target = hub; });
  });
  document.addEventListener('keydown', (e) => {
    if (!target) return;
    if (e.target && /^(INPUT|TEXTAREA)$/.test(e.target.tagName)) return;
    const current = target.getAttribute('data-active') || 'search';
    const idx = ORDER.indexOf(current);
    if (e.key === 'ArrowRight' && idx < ORDER.length - 1) setActive(target, ORDER[idx + 1]);
    if (e.key === 'ArrowLeft'  && idx > 0)                 setActive(target, ORDER[idx - 1]);
  });
}

function bindKeyChips(hub) {
  /* Auto-switch: tap a found key chip on the search panel → open task detail. */
  hub.querySelectorAll('[data-jump="task"]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      setActive(hub, 'task');
    });
  });
  /* Same for task rows on the list panel. */
  hub.querySelectorAll('[data-jump="task-from-list"]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      setActive(hub, 'task');
    });
  });
}

function bindAnswerForm(hub) {
  /* Mock-only behavior: the state toggle and the submit button drive a
     local "submitted" flag. A "correct" submit slides back to search,
     a "wrong" submit stays on the task. */
  const form = hub.querySelector('[data-answer-form]');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const expected = (form.getAttribute('data-correct-answer') || '').trim().toUpperCase();
    const value = (form.querySelector('input')?.value || '').trim().toUpperCase();
    const correct = expected ? value === expected : false;
    setAnswerState(hub, correct ? 'correct' : 'wrong');
    if (correct) setTimeout(() => setActive(hub, 'search'), 600);
  });
}

function setAnswerState(hub, state) {
  hub.querySelectorAll('[data-feedback]').forEach((el) => {
    el.hidden = el.getAttribute('data-feedback') !== state;
  });
  hub.querySelectorAll('[data-state-button]').forEach((b) => {
    b.setAttribute('aria-pressed', String(b.getAttribute('data-state-button') === state));
  });
}

function bindStateToggle(hub) {
  hub.querySelectorAll('[data-state-button]').forEach((b) => {
    b.addEventListener('click', () => setAnswerState(hub, b.getAttribute('data-state-button')));
  });
}

function init() {
  const hubs = findHubs();
  if (!hubs.length) return;
  hubs.forEach((hub) => {
    const initial = hub.getAttribute('data-active') || 'search';
    setActive(hub, initial);
    bindTabs(hub);
    bindSwipe(hub);
    bindKeyChips(hub);
    bindAnswerForm(hub);
    bindStateToggle(hub);
  });
  bindKeyboard(hubs);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
