(function () {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

  // ===== UTIL =====
  function escapeHtml(s) {
    return (s || '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }
  function numberFormat(n) {
    try {
      return (Math.round((n || 0))).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    } catch { return n; }
  }

  // ===== MODAL ADD ITEM =====
  const modal    = $('#mtModal');
  const btnOpen1 = $('#btnAddItem');
  const btnOpen2 = $('#btnAddItemBottom');
  const btnClose = modal ? modal.querySelector('.mt-modal__close') : null;
  const btnCancel= modal ? modal.querySelector('.mt-modal__foot .btn-outline') : null;

  function openModal() {
    if (!modal) return;
    modal.classList.add('open');
    $('#miSearch')?.focus();
    fetchItems('');
  }
  function closeModal() { modal?.classList.remove('open'); }

  window.openMtModal  = openModal;
  window.closeMtModal = closeModal;

  btnOpen1 && btnOpen1.addEventListener('click', openModal);
  btnOpen2 && btnOpen2.addEventListener('click', openModal);
  btnClose && btnClose.addEventListener('click', closeModal);
  btnCancel&& btnCancel.addEventListener('click', closeModal);
  modal && modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal?.classList.contains('open')) closeModal(); });

  // ===== ITEM SEARCH =====
  const miSearch = $('#miSearch');
  const miResult = $('#miResult');
  const miItemId = $('#miItemId');
  const addForm  = $('#miAddForm');
  const searchUrl= miSearch?.dataset.searchUrl || '';

  let timer = null;
  function fetchItems(q) {
    if (!miResult || !searchUrl) return;
    const query = (q || '').trim();
    if (!query.length) {
      // load default 20
    }
    miResult.innerHTML = '<tr><td colspan="4" class="text-center">Loading…</td></tr>';

    const url = new URL(searchUrl, window.location.origin);
    if (query.length) url.searchParams.set('q', query);

    fetch(url.href, {
      method: 'GET',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(res => {
        const rows = res.items || [];
        if (!rows.length) {
          miResult.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ditemukan</td></tr>';
          return;
        }
        miResult.innerHTML = rows.map(r => {
          const price = (r.top_price && r.top_price > 0) ? r.top_price : (r.bottom_price || 0);
          return `
            <tr>
              <td>
                <div style="font-weight:700; color:#0f172a;">${escapeHtml(r.item_name)}</div>
                <div style="font-size:12px; color:#64748b;">${escapeHtml(r.description || '')}</div>
              </td>
              <td>${escapeHtml(r.unit_name || '-')}</td>
              <td class="text-right">Rp ${numberFormat(price)}</td>
              <td>
                <button type="button" class="btn-outline"
                  style="padding:6px 10px; border-radius:8px; font-weight:700;"
                  data-id="${r.id}" onclick="pickMi(this)">Pilih</button>
              </td>
            </tr>`;
        }).join('');
      })
      .catch(err => {
        console.error('fetchItems error', err);
        miResult.innerHTML = '<tr><td colspan="4" class="text-center">Gagal memuat data</td></tr>';
      });
  }

  if (miSearch) {
    miSearch.addEventListener('focus', () => fetchItems(''));
    miSearch.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(() => fetchItems(this.value), 250);
    });
  }

  window.pickMi = function (el) {
    if (!addForm || !miItemId) return;
    miItemId.value = el.dataset.id || '';
    addForm.submit();
  };

  // ===== SEARCHABLE PROGRAM & PIC =====
  function makeSearchable(inputId, listId, hiddenId) {
    const input  = document.getElementById(inputId);
    const list   = document.getElementById(listId);
    const hidden = document.getElementById(hiddenId);
    if (!input || !list) return;

    function filter() {
      const q = (input.value || '').toLowerCase();
      let visible = 0;
      list.querySelectorAll('li').forEach(li => {
        const show = li.innerText.toLowerCase().includes(q);
        li.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      list.style.display = visible > 0 ? 'block' : 'none';
    }

    input.addEventListener('focus', filter);
    input.addEventListener('input', filter);
    document.addEventListener('click', e => {
      if (!list.contains(e.target) && e.target !== input) list.style.display = 'none';
    });

    list.querySelectorAll('li').forEach(li => {
      li.addEventListener('click', () => {
        const name = li.querySelector('.searchable__name')?.innerText || '';
        input.value = name;
        if (hidden) hidden.value = li.dataset.id || '';
        list.style.display = 'none';

        if (inputId === 'eventInput') {
          const pic  = li.dataset.pic || '';
          const cat  = (li.dataset.category || '').toLowerCase();
          const desc = li.dataset.desc || '';

          if (pic) {
            const picInput = $('#picInput');
            const picId    = $('#picId');
            const picNameEl= $(`#picList li[data-id="${pic}"] .searchable__name`);
            if (picId) picId.value = pic;
            if (picInput && picNameEl) picInput.value = picNameEl.innerText;
          }
          const rOn  = $('input[name="category"][value="On Air"]');
          const rOff = $('input[name="category"][value="Off Air"]');
          if (rOn && rOff) (cat.includes('on') ? rOn : rOff).checked = true;

          const txt = $('textarea[name="description"]');
          if (txt) txt.value = desc;
        }
      });
    });
  }

  makeSearchable('eventInput', 'eventList', 'eventId');
  makeSearchable('picInput', 'picList', 'picId');

  document.addEventListener('DOMContentLoaded', function() {
    const flag = document.getElementById('afterAddItem');
    if (flag && flag.value === '1') {
      // Scroll ke bawah
      const el = document.querySelector('.mp-card:last-of-type');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'end' });
      } else {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
      }

      // Auto open modal Add Item
      const btn = document.getElementById('btnAddItem');
      if (btn) btn.click();
    }

    const form = document.querySelector('form[action*="master/templates"]');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      const progId = document.getElementById('eventId')?.value.trim();
      const progInput = document.getElementById('eventInput');
      const picId = document.getElementById('picId')?.value.trim();
      const picInput = document.getElementById('picInput');

      if (!progId || progId === '') {
        e.preventDefault();
        alert('Silakan pilih Event/Program dari daftar yang muncul (bukan mengetik manual).');
        if (progInput) progInput.focus();
        return;
      }

      if (!picId || picId === '') {
        e.preventDefault();
        alert('Silakan pilih Penanggung Jawab dari daftar yang muncul (bukan mengetik manual).');
        if (picInput) picInput.focus();
        return;
      }
    });
  });

  // Validasi form sebelum submit
  document.querySelector('form')?.addEventListener('submit', function(e) {
    const picId = document.getElementById('picId')?.value.trim();
    const picInput = document.getElementById('picInput');

    if (!picId || picId === '') {
      e.preventDefault();
      alert('Silakan pilih Penanggung Jawab dari daftar yang muncul (bukan mengetik manual).');
      if (picInput) picInput.focus();
    }
  });


  const table = document.getElementById('tiTable');
  if (!table) return;

  const urlTpl = table.dataset.qtyUrlTemplate || '';
  const csrf   = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function buildUrl(rowId){
    if (!urlTpl) return '';
    return urlTpl.replace(/\/0(\/|$)/, `/${rowId}$1`);
  }

  function nf(idr){
    const n = Math.round(Number(idr) || 0);
    return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  }

  function getRow(el){
    return el.closest && el.closest('tr[data-row-id]') || null;
  }

  function setBusy(el, on){
    if (!el) return;
    el.disabled = !!on;
  }

  function updateRowUI(tr, qty, rowTotal, grand){
    const qtyInput = tr.querySelector('.qty-input');
    const totalEl  = tr.querySelector('.ti-row-total');
    if (qtyInput) qtyInput.value = qty;
    if (totalEl)  totalEl.textContent = nf(rowTotal);
    const gEl = document.getElementById('tiGrandTotal');
    if (gEl) gEl.textContent = nf(grand);
  }

  // ⬇️ RETURN promise supaya bisa di-.finally()
  function postQty(rowId, payload){
    const url = buildUrl(rowId);
    return fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(j => {
      if (!j || j.ok !== true) throw new Error(j?.message || 'Gagal update qty');
      return j; // penting: resolve data
    });
  }

  // Helper: safeClosest untuk kasus text node
  function safeClosest(target, selector){
    let el = target;
    if (!(el instanceof Element)) el = target?.parentElement || null;
    return el ? el.closest(selector) : null;
  }

  // Klik +/-
  table.addEventListener('click', function(e){
    const btnMinus = safeClosest(e.target, '.qty-minus');
    const btnPlus  = safeClosest(e.target, '.qty-plus');
    if (!btnMinus && !btnPlus) return;

    const tr = safeClosest(e.target, 'tr[data-row-id]');
    const rowId = tr?.dataset?.rowId;
    if (!rowId) return;

    const btn = btnMinus || btnPlus;
    setBusy(btn, true);

    const delta = btnPlus ? 'inc' : 'dec';
    postQty(rowId, { delta })
      .then(j => {
        updateRowUI(tr, j.qty, j.row_total, j.grand_total);
      })
      .catch(err => {
        alert(err.message || 'Gagal memperbarui QTY.');
      })
      .finally(() => setBusy(btn, false)); // ⬅️ sekarang nggak error
  });

  // Edit manual di input QTY (Enter/blur)
  table.addEventListener('keydown', function(e){
    const inp = safeClosest(e.target, '.qty-input');
    if (!inp) return;
    if (e.key !== 'Enter') return;
    e.preventDefault();
    commitQty(inp);
  });

  table.addEventListener('blur', function(e){
    const inp = safeClosest(e.target, '.qty-input');
    if (!inp) return;
    commitQty(inp);
  }, true);

  function commitQty(inp){
    const tr = safeClosest(inp, 'tr[data-row-id]');
    const rowId = tr?.dataset?.rowId;
    if (!rowId) return;

    let val = parseInt((inp.value || '').replace(/\D/g,''), 10);
    if (!Number.isFinite(val) || val < 1) val = 1;

    setBusy(inp, true);
    postQty(rowId, { qty: val })
      .then(j => {
        updateRowUI(tr, j.qty, j.row_total, j.grand_total);
      })
      .catch(err => {
        alert(err.message || 'Gagal memperbarui QTY.');
      })
      .finally(() => setBusy(inp, false));
  }
})();
