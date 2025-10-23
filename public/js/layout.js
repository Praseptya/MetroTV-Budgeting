(function () {
  // Toggle group (Master Data) + persist state
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.nav-group-toggle');
    if (!toggle) return;

    const group = toggle.closest('.nav-group');
    const submenu = group.querySelector('.submenu');
    const open = group.classList.toggle('open');

    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (submenu) {
      if (open) submenu.removeAttribute('hidden');
      else submenu.setAttribute('hidden', 'hidden');
    }

    // persist
    const key = group.getAttribute('data-key') || 'nav-group';
    try {
      localStorage.setItem('sidebar:' + key, open ? '1' : '0');
    } catch (err) {}
  });

  // Restore persistent state on load
  document.querySelectorAll('.nav-group[data-key]').forEach(group => {
    const key = group.getAttribute('data-key');
    try {
      const v = localStorage.getItem('sidebar:' + key);
      if (v !== null) {
        const open = v === '1';
        const submenu = group.querySelector('.submenu');
        group.classList.toggle('open', open);
        group.querySelector('.nav-group-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (submenu) {
          if (open) submenu.removeAttribute('hidden');
          else submenu.setAttribute('hidden', 'hidden');
        }
      }
    } catch (err) {}
  });

  // (Opsional) Mobile open/close: jika kamu punya tombol menu di header dengan id #btnToggleSidebar
  document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
    document.querySelector('.sidebar.v2')?.classList.toggle('active');
  });
})();
(function () {
  const KEY_FLAG   = 'mtv_keep_scroll_flag';
  const KEY_Y      = 'mtv_keep_scroll_y';
  const KEY_URL    = 'mtv_keep_scroll_url';
  const KEY_TIME   = 'mtv_keep_scroll_time';

  // --- 1) TANGKAP SUBMIT FORM: simpan posisi scroll + url asal ---
  document.addEventListener('submit', function (e) {
    const f = e.target;
    if (!(f instanceof HTMLFormElement)) return;
    if (f.dataset.noKeepScroll !== undefined) return; // opt-out per form

    sessionStorage.setItem(KEY_FLAG, '1');
    sessionStorage.setItem(KEY_Y, String(window.scrollY || window.pageYOffset || 0));
    sessionStorage.setItem(KEY_URL, location.pathname + location.search);
    sessionStorage.setItem(KEY_TIME, String(Date.now()));
  }, true);

  // --- 1b) Opsional: untuk link yang memang reload/redirect tapi mau tetap keep scroll ---
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[data-keep-scroll]');
    if (!a) return;
    sessionStorage.setItem(KEY_FLAG, '1');
    sessionStorage.setItem(KEY_Y, String(window.scrollY || window.pageYOffset || 0));
    sessionStorage.setItem(KEY_URL, location.pathname + location.search);
    sessionStorage.setItem(KEY_TIME, String(Date.now()));
  }, true);

  // --- 2) RESTORE saat halaman selesai dimuat kembali (bukan reload manual) ---
  function restoreIfNeeded() {
    try {
      const flag = sessionStorage.getItem(KEY_FLAG);
      const url  = sessionStorage.getItem(KEY_URL);
      const yStr = sessionStorage.getItem(KEY_Y);
      const tStr = sessionStorage.getItem(KEY_TIME);

      // Hanya pulihkan bila:
      // - ada flag,
      // - URL sama (redirect back ke halaman yang sama),
      // - dan waktunya masih fresh (< 30 detik untuk menghindari stale restore)
      if (flag === '1'
          && url === (location.pathname + location.search)
          && yStr != null
          && (!tStr || (Date.now() - Number(tStr) < 30_000))) {

        const y = Math.max(0, parseInt(yStr, 10) || 0);

        // Pulihkan setelah layout settle—coba dua kali untuk aman.
        requestAnimationFrame(() => window.scrollTo({ top: y, behavior: 'instant' }));
        setTimeout(() => window.scrollTo({ top: y, behavior: 'instant' }), 0);
      }
    } finally {
      // Selalu bersihkan supaya reload manual berikutnya tidak ikut restore
      sessionStorage.removeItem(KEY_FLAG);
      sessionStorage.removeItem(KEY_Y);
      sessionStorage.removeItem(KEY_URL);
      sessionStorage.removeItem(KEY_TIME);
    }
  }

  // Jalankan saat DOM siap & juga saat semua resource selesai
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    restoreIfNeeded();
  } else {
    document.addEventListener('DOMContentLoaded', restoreIfNeeded);
  }
  window.addEventListener('load', restoreIfNeeded);
})();