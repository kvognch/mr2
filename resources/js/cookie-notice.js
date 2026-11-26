const notice = document.querySelector('[data-cookie-notice]');

if (notice) {
  const acknowledgement = 'mr2_cookie_notice=accepted';
  const maxAge = 400 * 24 * 60 * 60;

  function syncNotice() {
    notice.hidden = document.cookie.split(';').some(cookie => cookie.trim() === acknowledgement);
  }

  notice.querySelector('[data-cookie-accept]').addEventListener('click', () => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${acknowledgement}; Max-Age=${maxAge}; Path=/; SameSite=Lax${secure}`;
    notice.hidden = true;
  });

  syncNotice();
  window.addEventListener('pageshow', syncNotice);
}
