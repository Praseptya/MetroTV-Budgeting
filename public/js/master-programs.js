(function () {
  const form = document.getElementById('mpForm');
  if (!form) return;

  const methodInput = document.getElementById('mpMethod');
  const idInput     = document.getElementById('mpId');
  const nameInput   = document.getElementById('f_name');
  const descInput   = document.getElementById('f_desc');
  const rOn         = document.getElementById('f_category_on');
  const rOff        = document.getElementById('f_category_off');
  const btnCancel   = document.getElementById('btnCancelEdit');

  const picInput  = document.getElementById('mpPicInput');
  const picHidden = document.getElementById('mpPicId');
  const picList   = document.getElementById('mpPicList');

  const storeUrl  = form.dataset.storeUrl || form.action;
  const updateTpl = form.dataset.updateTemplate || '';

  // === searchable PIC (ketik untuk filter, klik untuk pilih) ===
  makeSearchable('mpPicInput','mpPicList','mpPicId');

  // === klik Edit => isi form + switch ke PUT ===
  document.querySelectorAll('.mp-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      if (!tr) return;

      const id   = tr.dataset.id || '';
      const name = tr.dataset.name || '';
      const desc = tr.dataset.desc || '';
      const cat  = (tr.dataset.type || '').toLowerCase();
      const pic  = tr.dataset.pic || '';

      idInput.value   = id;
      nameInput.value = name;
      descInput.value = desc;
      (cat === 'on air' ? rOn : rOff).checked = true;

      // PIC UI+hidden
      picHidden.value = pic;
      const optName = picList?.querySelector(`li[data-id="${CSS.escape(pic)}"] .searchable__name`);
      picInput.value = optName ? optName.textContent.trim() : '';

      // switch action
      methodInput.value = 'PUT';
      form.action = updateTpl.replace(/0$/, String(id));

      if (btnCancel) btnCancel.style.display = '';
      window.scrollTo({ top: form.offsetTop - 16, behavior: 'smooth' });
    });
  });

  // === Batal => reset ke POST/store ===
  btnCancel?.addEventListener('click', () => {
    form.reset();
    idInput.value     = '';
    methodInput.value = 'POST';
    form.action       = storeUrl;
    btnCancel.style.display = 'none';
  });

  // === Validasi: PIC harus dipilih dari list ===
  form.addEventListener('submit', (e) => {
    // kalau user ngetik manual, kosongkan hidden supaya kena validasi server
    if (picInput && picList && picHidden) {
      const anyMatch = Array.from(picList.querySelectorAll('li'))
        .some(li => li.dataset.id === picHidden.value);
      if (!anyMatch) picHidden.value = '';
    }

    if (!picHidden.value) {
      e.preventDefault();
      alert('Silakan pilih Penanggung Jawab dari daftar (jangan ketik manual).');
      picInput?.focus();
    }
  });

  // helper searchable
  function makeSearchable(inputId, listId, hiddenId){
    const input  = document.getElementById(inputId);
    const list   = document.getElementById(listId);
    const hidden = document.getElementById(hiddenId);
    if(!input || !list) return;

    input.addEventListener('focus', ()=>{ list.style.display='block'; filter(); });
    input.addEventListener('input', ()=>{ hidden.value=''; filter(); });
    document.addEventListener('click', e=>{
      if(!list.contains(e.target) && e.target!==input) list.style.display='none';
    });

    function filter(){
      const q = input.value.toLowerCase();
      let shown = 0;
      list.querySelectorAll('li').forEach(li=>{
        const show = li.innerText.toLowerCase().includes(q);
        li.style.display = show ? '' : 'none';
        if (show) shown++;
      });
      list.style.display = shown>0 ? 'block':'none';
    }

    list.querySelectorAll('li').forEach(li=>{
      li.addEventListener('click', ()=>{
        const nm = li.querySelector('.searchable__name');
        input.value  = nm ? nm.textContent.trim() : '';
        hidden.value = li.dataset.id || '';
        list.style.display = 'none';
      });
    });
  }
})();
