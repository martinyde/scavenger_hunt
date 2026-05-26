// Tiny hash router for the design site.
// Maps `#/<key>` to a partial HTML file that gets dropped into <main data-router-outlet>.
// Keep this dumb: no history API, no params, no nested routes.

const routes = {
  '':              'partials/home.html',
  '/':             'partials/home.html',
  '/design-system':'design-system.html',
  '/archive':      'archive/index.html',
  '/admin':        'admin/list.html',
  '/race':         'race-display/active.html',
};

async function loadRoute() {
  const outlet = document.querySelector('[data-router-outlet]');
  if (!outlet) return;
  const raw = (location.hash || '#/').slice(1);
  const path = raw.split('?')[0] || '/';
  const target = routes[path] ?? routes['/'];

  try {
    const res = await fetch(target, { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const html = await res.text();
    // Strip <html>/<head>/<body> wrappers if present — partials may be full pages.
    const match = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
    outlet.innerHTML = match ? match[1] : html;
    // Re-trigger any inline scripts inside the loaded partial.
    outlet.querySelectorAll('script').forEach((old) => {
      const s = document.createElement('script');
      if (old.src) s.src = old.src;
      else s.textContent = old.textContent;
      old.replaceWith(s);
    });
    highlightActiveTab(path);
    window.scrollTo({ top: 0, behavior: 'instant' });
  } catch (err) {
    outlet.innerHTML = `<div class="p-8 text-danger-700">Failed to load ${target}: ${err.message}</div>`;
  }
}

function highlightActiveTab(path) {
  document.querySelectorAll('[data-shell-tab]').forEach((el) => {
    const target = el.getAttribute('data-shell-tab');
    el.classList.remove('shell-tab-active');
    el.classList.add('shell-tab');
    if (target === path) {
      el.classList.add('shell-tab-active');
    }
  });
}

window.addEventListener('hashchange', loadRoute);
window.addEventListener('DOMContentLoaded', loadRoute);
