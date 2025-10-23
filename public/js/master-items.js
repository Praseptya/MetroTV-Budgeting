(function(){
  // Format input uang: tampil "Rp 1.234.567", tapi kirim hanya digit
  function formatMoney(val){
    const digits = (val || '').toString().replace(/\D+/g,'');
    if(!digits) return '';
    return 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function bindMoney(el){
    el.addEventListener('input', () => {
      const caret = el.selectionStart;
      const raw = el.value;
      el.value = formatMoney(raw);
      // caret handling minimal
      el.setSelectionRange(el.value.length, el.value.length);
    });
    // init
    el.value = formatMoney(el.value);
  }
  document.querySelectorAll('.money').forEach(bindMoney);

  // Search
  const s = document.getElementById('miSearch');
  s?.addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#miTable tbody tr').forEach(tr=>{
      tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  // Pencarian client-side
  document.getElementById('miSearch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#miTable tbody tr').forEach(tr=>{
      tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  // Edit → isi form
  const form        = document.getElementById('miForm');
  const idInput     = document.getElementById('miId');
  const methodInput = document.getElementById('miMethod');
  const nameInput   = document.getElementById('miName');
  const descInput   = document.getElementById('miDesc');
  const btnCancel   = document.getElementById('btnCancelEdit');

  // harga (dua kolom)
  const bottomInput = document.getElementById('miBottom');
  const topInput    = document.getElementById('miTop');

  // unit (searchable)
  const unitInput  = document.getElementById('unitInput');
  const unitHidden = document.getElementById('unitId');
  const unitList   = document.getElementById('unitList');

  const updateUrlTemplate = "{{ route('master.items.update', 0) }}".replace(/0$/, '__ID__');

  if(!form) return;

  const storeUrl = form.dataset.storeUrl;                   // e.g. /master/items
  const updateTpl = form.dataset.updateTemplate || '';      // e.g. /master/items/0

  // Klik tombol Edit di tabel
  document.querySelectorAll('.mi-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      const id = tr?.dataset.id;
      if(!id) return;

      // isi form dari data-* baris tabel (singkat)
      document.getElementById('miId').value        = id;
      document.getElementById('miName').value      = tr.dataset.name || '';
      document.getElementById('miDesc').value      = tr.dataset.desc || '';
      document.getElementById('miBottom').value    = tr.dataset.bottom || '';
      document.getElementById('miTop').value       = tr.dataset.top || '';
      document.getElementById('unitInput').value   = tr.dataset.unitname || '';
      document.getElementById('unitId').value      = tr.dataset.unitid || '';

      // switch ke PUT + set action update
      methodInput.value = 'PUT';
      form.action = updateTpl.replace(/0$/, String(id));     // ganti trailing /0 dengan id
      btnCancel.style.display = '';
      window.scrollTo({ top: form.offsetTop - 12, behavior: 'smooth' });
    });
  });

  // Batal edit → kembali ke mode create
  btnCancel?.addEventListener('click', () => {
    form.reset();
    document.getElementById('miId').value = '';
    methodInput.value = 'POST';
    form.action = storeUrl;
    btnCancel.style.display = 'none';
  });

  // ===== SEARCHABLE UNIT =====
  // buka dropdown saat fokus + filter awal
  unitInput?.addEventListener('focus', () => {
    unitList.style.display = 'block';
    filterUnit();
  });

  // SANGAT PENTING: jika user mulai mengetik, kosongkan hidden id
  unitInput?.addEventListener('input', () => {
    if (unitId) unitId.value = '';
    filterUnit();
  });

  // klik di luar → tutup
  document.addEventListener('click', (e) => {
    if (!unitList.contains(e.target) && e.target !== unitInput) {
      unitList.style.display = 'none';
    }
  });

  // klik pilihan → set teks + id
  unitList?.querySelectorAll('li').forEach((li) => {
    li.addEventListener('click', () => {
      const nm = li.querySelector('.searchable__name')?.textContent?.trim() || '';
      unitInput.value = nm;
      unitId.value    = li.dataset.id || '';
      unitList.style.display = 'none';
    });
  });

  function filterUnit() {
    const q = (unitInput.value || '').toLowerCase();
    let shown = 0;
    unitList.querySelectorAll('li').forEach((li) => {
      const show = li.innerText.toLowerCase().includes(q);
      li.style.display = show ? '' : 'none';
      if (show) shown++;
    });
    unitList.style.display = shown > 0 ? 'block' : 'none';
  }

  // ---- OPTIONAL: saat klik Edit (row -> form), set nilai Unit di UI ----
  document.querySelectorAll('.mi-edit').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      if (!tr) return;
      const uName = tr.dataset.unitname || '';
      const uId   = tr.dataset.unitid   || '';
      if (unitInput) unitInput.value = uName;
      if (unitId)    unitId.value    = uId;
    });
  });

  function groupID(n){
    n = String(n || '').replace(/[^\d]/g,'');
    return n.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function attachMoneyMask(input){
    const format = () => {
      const before = input.value || '';
      const start  = input.selectionStart || 0;

      const digits = before.replace(/[^\d]/g, '');
      if (digits === '') {
        input.value = '';
        return;
      }

      const after = groupID(digits);
      // Hitung pergeseran caret
      const diff = after.length - before.length;
      input.value = after;

      // Pulihkan caret bila masih fokus
      if (document.activeElement === input) {
        const pos = Math.max(0, start + diff);
        input.setSelectionRange(pos, pos);
      }
    };

    input.addEventListener('input', format);
    input.addEventListener('blur', format);
    input.addEventListener('paste', () => setTimeout(format, 0));
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Pasang masker ke semua input yang diberi atribut data-money
    document.querySelectorAll('input[data-money]').forEach(attachMoneyMask);

    // Jika masuk mode Edit, isi awal dari data-* diformat juga (opsional)
    const btm = document.getElementById('miBottom');
    const top = document.getElementById('miTop');
    if (btm) btm.value = groupID(btm.value);
    if (top) top.value = groupID(top.value);
  });
  
  function onlyDigits(v) {
    return String(v || '').replace(/[^\d]/g, '');
  }
  function groupID(v) {
    const s = onlyDigits(v);
    return s ? s.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
  }
  function setMoney(el, val) {
    if (!el) return;
    el.value = groupID(val);
  }

  /* ===== Handler tombol Edit ===== */
  // Pastikan ini dieksekusi setelah DOMContentLoaded
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.mi-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const tr = btn.closest('tr');
        if (!tr) return;

        // isi field teks biasa
        const id    = tr.dataset.id;
        const name  = tr.dataset.name || '';
        const unitId= tr.dataset.unitid || '';
        const unitNm= tr.dataset.unitname || '';
        const desc  = tr.dataset.desc || '';

        document.getElementById('miId').value      = id || '';
        document.getElementById('miName').value    = name;
        document.getElementById('unitId').value    = unitId;
        document.getElementById('unitInput').value = unitNm;
        document.getElementById('miDesc').value    = desc;

        // ======== FORMAT HARGA SAAT AUTOFILL ========
        // Jika pakai bottom/top price
        setMoney(document.getElementById('miBottom'), tr.dataset.bottom);
        setMoney(document.getElementById('miTop'),    tr.dataset.top);

        // Jika kamu masih punya 1 field "price", fallback ke salah satunya
        setMoney(
          document.getElementById('miPrice'),
          tr.dataset.price || tr.dataset.top || tr.dataset.bottom
        );

        // switch form ke mode UPDATE (PUT)
        const form   = document.getElementById('miForm');
        const method = document.getElementById('miMethod');
        method.value = 'PUT';
        // ganti action ke /master/items/{id}
        form.action  = (window.__MI_UPDATE_URL || '/master/items/__ID__').replace('__ID__', id);

        // tampilkan tombol Batal
        const cancel = document.getElementById('btnCancelEdit');
        if (cancel) cancel.style.display = '';
        // scroll ke form
        window.scrollTo({ top: form.offsetTop - 16, behavior: 'smooth' });
      });
    });

    // tombol Batal → reset ke mode create
    const cancel = document.getElementById('btnCancelEdit');
    if (cancel) {
      cancel.addEventListener('click', () => {
        const form = document.getElementById('miForm');
        form.reset();
        document.getElementById('miId').value    = '';
        document.getElementById('miMethod').value= 'POST';
        form.action = (window.__MI_STORE_URL || '/master/items');

        // pastikan field uang kosong (tanpa format)
        ['miBottom','miTop','miPrice'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });

        cancel.style.display = 'none';
      });
    }
  });

})();
