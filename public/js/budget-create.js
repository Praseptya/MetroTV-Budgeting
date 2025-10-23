(function(){
  const sel   = document.getElementById('tplSelect');
  const total = document.getElementById('totalBudget');
  const desc  = document.getElementById('descInput');

  function formatRupiah(n){
    try {
      return 'Rp ' + (Math.round(n||0)).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    } catch(e){
      return 'Rp ' + n;
    }
  }

  async function onTplChange(){
    const id = sel.value;
    if(!id){
      total.value = '';
      desc.value  = '';
      return;
    }

    const patt = sel.getAttribute('data-detail-url');
    const url  = (patt || '/budgets/templates/__ID__/detail').replace('__ID__', id);

    try {
      const res  = await fetch(url, {
        headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      const data = await res.json();
      if(!data.ok) throw new Error('Template not found');

      total.value = formatRupiah(data.template.grand_total || 0);
      desc.value  = data.template.description || '';

    } catch(err){
      console.error(err);
      total.value = '';
      desc.value  = '';
    }
  }

  sel && sel.addEventListener('change', onTplChange);

  // Date picker enhancement
  document.addEventListener('DOMContentLoaded', () => {
    const from = document.getElementById('periodeFrom');
    const to   = document.getElementById('periodeTo');

    // ====== 1️⃣ Buka kalender saat klik di mana saja ======
    document.querySelectorAll('input[type="date"].date-picker').forEach((input) => {
      input.addEventListener('click', (e) => {
        try {
          e.target.showPicker(); // Browser modern (Chrome, Edge)
        } catch (err) {
          e.target.focus(); // Fallback untuk browser lain
        }
      });
    });

    // ====== 2️⃣ Validasi: tanggal akhir ≥ tanggal awal ======
    if (from && to) {
      from.addEventListener('change', () => {
        // Kalau user ubah tanggal awal → set minimal tanggal akhir
        to.min = from.value;
        if (to.value && to.value < from.value) {
          alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai.');
          to.value = from.value;
        }
      });

      to.addEventListener('change', () => {
        if (from.value && to.value < from.value) {
          alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai.');
          to.value = from.value;
        }
      });
    }
  });

})();