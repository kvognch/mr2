let stylesheet = document.querySelector('link[data-site-custom-css]');

if (stylesheet) {
  const endpoint = new URL(stylesheet.href);
  let refreshing = false;
  endpoint.searchParams.delete('v');

  async function refreshStylesheet() {
    if (document.hidden || refreshing) return;
    refreshing = true;

    try {
      const response = await fetch(endpoint, { method: 'HEAD', cache: 'no-store' });
      const version = response.headers.get('ETag')?.match(/^(?:W\/)?"([a-f0-9]{64})"$/)?.[1];
      if (!response.ok || !version || version === new URL(stylesheet.href).searchParams.get('v')) return;

      const next = stylesheet.cloneNode();
      const url = new URL(endpoint);
      url.searchParams.set('v', version);
      next.href = url.href;

      await new Promise((resolve) => {
        next.addEventListener('load', () => {
          stylesheet.remove();
          stylesheet = next;
          resolve();
        }, { once: true });
        next.addEventListener('error', () => {
          next.remove();
          resolve();
        }, { once: true });
        stylesheet.after(next);
      });
    } catch {
      // Keep the current styles if the server is temporarily unreachable.
    } finally {
      refreshing = false;
    }
  }

  setInterval(refreshStylesheet, 15_000);
  document.addEventListener('visibilitychange', refreshStylesheet);
  window.addEventListener('pageshow', (event) => {
    if (event.persisted) refreshStylesheet();
  });
}
