(function () {
  // Toggle group + persist state
  document.addEventListener('click', function (e) {
    const t = e.target.closest('.sb-toggle');
    if (!t) return;

    const group = t.closest('.sb-group');
    const sub = group.querySelector('.sb-sub');
    const open = group.classList.toggle('is-open');

    t.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (sub) {
      if (open) sub.removeAttribute('hidden');
      else sub.setAttribute('hidden', 'hidden');
    }

    // simpan state
    const key = group.getAttribute('data-key') || 'group';
    try { localStorage.setItem('sidebar:' + key, open ? '1' : '0'); } catch (e) {}
  });

  // restore state
  document.querySelectorAll('.sb-group[data-key]').forEach(group => {
    const key = group.getAttribute('data-key');
    try {
      const v = localStorage.getItem('sidebar:' + key);
      if (v !== null) {
        const open = v === '1';
        const sub = group.querySelector('.sb-sub');
        group.classList.toggle('is-open', open);
        group.querySelector('.sb-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (sub) {
          if (open) sub.removeAttribute('hidden');
          else sub.setAttribute('hidden', 'hidden');
        }
      }
    } catch (e) {}
  });

  // (Opsional) hamburger di header
  document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
    document.querySelector('.mtv-sidebar')?.classList.toggle('active');
  });
})();
