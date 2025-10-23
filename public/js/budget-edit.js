// public/js/budgets-edit.js
(function(){
  const $ = (s, c=document) => c.querySelector(s);

  // ====== QTY controls ======
  const table = document.getElementById('biTable');
  if (table) {
    const urlTpl = table.dataset.qtyUrlTemplate || '';
    const csrf   = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const nf = n => {
      const v = Math.round(Number(n)||0);
      return 'Rp ' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    };
    const safeClosest = (t, sel) => {
      let el = t instanceof Element ? t : t?.parentElement || null;
      return el ? el.closest(sel) : null;
    };
    const buildUrl = rowId => urlTpl.replace(/\/0(\/|$)/, `/${rowId}$1`);
    const postQty = (rowId, payload) => {
      return fetch(buildUrl(rowId), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      }).then(r=>r.json()).then(j=>{
        if(!j || j.ok!==true) throw new Error(j?.message || 'Gagal update qty');
        return j;
      });
    };
    const updateRowUI = (tr, j) => {
      tr.querySelector('.qty-input').value = j.qty;
      tr.querySelector('.bi-row-total').textContent = nf(j.row_total);
      const g = document.getElementById('biGrandTotal');
      if (g) g.textContent = nf(j.grand_total);
    };

    table.addEventListener('click', (e)=>{
      const minus = safeClosest(e.target, '.qty-minus');
      const plus  = safeClosest(e.target, '.qty-plus');
      if (!minus && !plus) return;

      const tr = safeClosest(e.target, 'tr[data-row-id]');
      const rowId = tr?.dataset?.rowId;
      if (!rowId) return;

      const btn = minus || plus;
      btn.disabled = true;

      const delta = plus ? 'inc' : 'dec';
      postQty(rowId, { delta })
        .then(j => updateRowUI(tr, j))
        .catch(err => alert(err.message || 'Gagal memperbarui QTY.'))
        .finally(()=> btn.disabled = false);
    });

    table.addEventListener('keydown', (e)=>{
      const inp = e.target.closest?.('.qty-input');
      if (!inp) return;
      if (e.key !== 'Enter') return;
      e.preventDefault();
      commit(inp);
    });

    table.addEventListener('blur', (e)=>{
      const inp = e.target.closest?.('.qty-input');
      if (!inp) return;
      commit(inp);
    }, true);

    function commit(inp){
      const tr = inp.closest('tr[data-row-id]');
      const rowId = tr?.dataset?.rowId;
      if (!rowId) return;

      let v = parseInt((inp.value || '').replace(/\D/g,''),10);
      if (!Number.isFinite(v) || v<1) v = 1;

      inp.disabled = true;
      postQty(rowId, { qty: v })
        .then(j => updateRowUI(tr, j))
        .catch(err => alert(err.message || 'Gagal memperbarui QTY.'))
        .finally(()=> inp.disabled = false);
    }
  }

  // ====== Modal Tambah Item ======
  const modal    = document.getElementById('biModal');
  if (!modal) return;

  const OPENERS  = [document.getElementById('btnAddBi')].filter(Boolean);
  const search   = document.getElementById('biSearch');
  const resultTB = document.getElementById('biResult');
  const addForm  = document.getElementById('biAddForm');
  const itemIdIn = document.getElementById('biItemId');
  const SEARCH_URL = modal.dataset.searchUrl; // route('master.templates.item.search')

  // open/close
  function openModal() {
    modal.classList.add('open');
    document.documentElement.classList.add('modal-open');
    // langsung load semua item (tanpa query)
    fetchItems('');
    setTimeout(() => search && search.focus(), 60);
  }
  function closeModal() {
    modal.classList.remove('open');
    document.documentElement.classList.remove('modal-open');
  }

  OPENERS.forEach(btn => btn.addEventListener('click', openModal));
  modal.addEventListener('click', (e) => {
    if (e.target.dataset.close || e.target === modal) closeModal();
  });

  // search behaviour — ketik = filter server, kosong = semua
  let tmr = null;
  search?.addEventListener('input', () => {
    clearTimeout(tmr);
    tmr = setTimeout(() => fetchItems(search.value.trim()), 220);
  });
  search?.addEventListener('focus', () => {
    if ((search.value || '').trim() === '') fetchItems('');
  });

  // fetch + render
  function fetchItems(q) {
    if (!SEARCH_URL) return;
    resultTB.innerHTML = `<tr><td colspan="4" class="text-center">Memuat…</td></tr>`;

    const url = new URL(SEARCH_URL, window.location.origin);
    if (q) url.searchParams.set('q', q);

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(json => {
        const rows = Array.isArray(json.items) ? json.items : [];
        if (!rows.length) {
          resultTB.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada data.</td></tr>`;
          return;
        }
        resultTB.innerHTML = rows.map(r => {
          const price = (r.top_price && r.top_price > 0) ? r.top_price : (r.bottom_price || 0);
          return `
            <tr>
              <td>
                <div style="font-weight:700; color:#0f172a;">${escapeHtml(r.item_name)}</div>
                <div style="font-size:12px; color:#64748b;">${escapeHtml(r.description || '')}</div>
              </td>
              <td>${escapeHtml(r.unit_name || '-')}</td>
              <td class="text-right">Rp ${numFmt(price)}</td>
              <td>
                <button type="button"
                        class="btn-outline"
                        style="padding:6px 10px; border-radius:8px; font-weight:700;"
                        data-pick="${r.id}">
                  Pilih
                </button>
              </td>
            </tr>`;
        }).join('');
      })
      .catch(() => {
        resultTB.innerHTML = `<tr><td colspan="4" class="text-center">Gagal memuat data.</td></tr>`;
      });
  }

  // delegate click "Pilih"
  resultTB?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-pick]');
    if (!btn || !addForm || !itemIdIn) return;
    itemIdIn.value = btn.dataset.pick;
    addForm.submit();
  });

  // utils
  function numFmt(n) {
    try {
      return Math.round(Number(n) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    } catch { return n; }
  }
  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  // expose pick handler
  window.pickBi = function(btn){
    if (!addForm || !itemId) return;
    itemId.value = btn.getAttribute('data-id');
    addForm.submit(); // akan redirect balik ke edit + flash + scroll bottom
  };

  // Scroll ke bawah setelah menambah / menghapus
  if (document.body.dataset.scrollBottom === '1') {
    setTimeout(()=> window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }), 150);
  }
})();
