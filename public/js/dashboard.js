/* public/js/dashboard.js
   - Inisialisasi chart (main + 3 mini)
   - Ganti range via chip => fetch('/dashboard/data?range=..') => update chart + stat + tabel
   - Safeguard tinggi container & fallback bila data kosong
*/

(function () {
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  // ----- Elemen present di Blade -----
  const dataBtn = $('#btnViewReport');
  const dataUrl = dataBtn ? dataBtn.dataset.dataUrl : null;

  // Stat cards
  const statNumbers = $$('.stat-card .stat-number');
  const statChanges = $$('.stat-card .stat-change');

  // Tabel
  const tableBody = $('.data-table tbody') || $('#dashboardTable tbody') || null;

  // Seed awal dari PHP
  const initial = (typeof window !== 'undefined' && window.__DASHBOARD__) || {
    labels: [],
    budgetData: [],
    approvalData: []
  };

  // ---------- Helpers ----------
  let mainChart = null;
  let tiny = { budget: null, approved: null, pending: null };

  function ensureHeight(el, px) {
    if (!el) return;
    const parent = el.parentElement;
    if (parent && (!parent.clientHeight || parent.clientHeight === 0)) {
      parent.style.minHeight = (px || 340) + 'px';
      parent.style.position = 'relative';
    }
    if ((!el.clientHeight || el.clientHeight === 0) && !el.style.height) {
      el.height = px || 340;
    }
  }

  function makeSmallChart(canvasId, labels, data, color) {
    const el = document.getElementById(canvasId);
    if (!el) return null;
    ensureHeight(el, 64);
    const ctx = el.getContext('2d');
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data,
          borderColor: color,
          backgroundColor: hexToRgba(color, 0.12),
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 } }
      }
    });
  }

  function makeMainChart(labels, budgetData, approvalData) {
    const el = document.getElementById('mainChart');
    if (!el) return null;
    ensureHeight(el, 340);

    const ctx = el.getContext('2d');
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Budget Diajukan',
            data: budgetData,
            borderColor: '#3B82F6',
            backgroundColor: hexToRgba('#3B82F6', 0.12),
            borderWidth: 2,
            fill: true,
            tension: 0.4
          },
          {
            label: 'Budget Disetujui',
            data: approvalData,
            borderColor: '#10B981',
            backgroundColor: hexToRgba('#10B981', 0.12),
            borderWidth: 2,
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 250 },
        plugins: { legend: { display: true, position: 'top' } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  function hexToRgba(hex, a) {
    const h = hex.replace('#', '');
    const bigint = parseInt(h, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${a})`;
  }

  // ---------- INIT ----------
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
      console.error('Chart.js belum termuat');
      return;
    }

    // Inisialisasi chart utama
    mainChart = makeMainChart(
      initial.labels,
      initial.budgetData,
      initial.approvalData
    );

    // 3 mini charts
    const pendingSeries = initial.budgetData.map((v, i) =>
      Math.max(0, Number(v) - Number(initial.approvalData[i] || 0))
    );
    tiny.budget   = makeSmallChart('budgetChart',   initial.labels, initial.budgetData,   '#3B82F6');
    tiny.approved = makeSmallChart('approvedChart', initial.labels, initial.approvalData, '#10B981');
    tiny.pending  = makeSmallChart('pendingChart',  initial.labels, pendingSeries,        '#EF4444');

    // Chips
    $$('.chip').forEach((btn) => btn.addEventListener('click', () => onRangeClick(btn)));

    // Search tabel
    const search = $('#searchInput');
    if (search && tableBody) {
      search.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        $$('.data-table tbody tr').forEach((tr) => {
          tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
      });
    }

    // Trigger load default range (biar data pasti terisi)
    const defaultChip = document.querySelector('.chip.active') || document.querySelector('.chip');
    if (defaultChip) onRangeClick(defaultChip);
  });

  // Resize safeguard
  window.addEventListener('resize', () => {
    try { mainChart && mainChart.resize(); } catch (_) {}
  });

  // ---------- View Report button ----------
  const v = document.getElementById('btnViewReport');
  if (v) v.addEventListener('click', (e) => {
    const url = v.dataset.reportUrl || v.getAttribute('href');
    if (url) window.location.href = url;
  });

  // ---------- Actions ----------
  function onRangeClick(btn) {
    // UI aktif
    $$('.chip').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');

    const range = btn.dataset.range || '12b';
    if (!dataUrl) return;

    const url = `${dataUrl}?range=${encodeURIComponent(range)}`;
    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      cache: 'no-store'
    })
      .then((r) => r.json())
      .then((json) => {
        // Fallback jika server kirim kosong
        const lbls = Array.isArray(json.labels) && json.labels.length ? json.labels : (mainChart?.data.labels || []);
        const bd   = Array.isArray(json.budgetData) && json.budgetData.length ? json.budgetData : (mainChart?.data.datasets?.[0]?.data || []);
        const ad   = Array.isArray(json.approvalData) && json.approvalData.length ? json.approvalData : (mainChart?.data.datasets?.[1]?.data || []);

        // Update chart utama
        updateMainChart(lbls, bd, ad);

        // Update mini charts
        updateTinyCharts(lbls, bd, ad);

        // Update stat cards
        if (json.stats) updateStats(json.stats);

        // (opsional) refresh tabel ringkas jika diberikan
        if (Array.isArray(json.rows) && tableBody) {
          renderRows(json.rows);
        }
      })
      .catch((err) => console.error('Fetch dashboard.data error:', err));
  }

  function updateMainChart(labels, budgetData, approvalData) {
    if (!mainChart) return;

    // Pastikan container punya tinggi sebelum update
    ensureHeight(document.getElementById('mainChart'), 340);

    mainChart.data.labels = labels;
    mainChart.data.datasets[0].data = budgetData;
    mainChart.data.datasets[1].data = approvalData;
    mainChart.update();
  }

  function updateTinyCharts(labels, budgetData, approvalData) {
    const pend = budgetData.map((v, i) => Math.max(0, Number(v) - Number(approvalData[i] || 0)));

    try { tiny.budget && tiny.budget.destroy(); } catch (_) {}
    try { tiny.approved && tiny.approved.destroy(); } catch (_) {}
    try { tiny.pending && tiny.pending.destroy(); } catch (_) {}

    tiny.budget   = makeSmallChart('budgetChart',   labels, budgetData,   '#3B82F6');
    tiny.approved = makeSmallChart('approvedChart', labels, approvalData, '#10B981');
    tiny.pending  = makeSmallChart('pendingChart',  labels, pend,         '#EF4444');
  }

  function updateStats(s) {
    if (statNumbers[0]) statNumbers[0].textContent = toInt(s.total_pengajuan);
    if (statNumbers[1]) statNumbers[1].textContent = toInt(s.disetujui);
    if (statNumbers[2]) statNumbers[2].textContent = toInt(s.menunggu);

    setGrowth(statChanges[0], s.growth_total);
    setGrowth(statChanges[1], s.growth_approve);
    setGrowth(statChanges[2], s.growth_wait);
  }

  function setGrowth(el, val) {
    if (!el) return;
    const num  = Number(val || 0);
    const span = el.querySelector('span') || document.createElement('span');
    const icon = el.querySelector('i') || document.createElement('i');

    el.classList.remove('positive', 'negative');
    icon.classList.remove('fa-arrow-up', 'fa-arrow-down');

    if (num >= 0) {
      el.classList.add('positive');
      icon.classList.add('fas', 'fa-arrow-up');
    } else {
      el.classList.add('negative');
      icon.classList.add('fas', 'fa-arrow-down');
    }

    span.textContent = `${Math.abs(num)}%`;
    if (!el.contains(icon)) el.prepend(icon);
    if (!el.contains(span)) el.appendChild(span);
  }

  function renderRows(rows) {
    const html = rows.map((r) => {
      const cls =
        r.status === 'Approve' ? 'approved' :
        r.status === 'Ditolak' ? 'rejected' :
        r.status === 'Revisi'  ? 'revision' : 'pending';
      return `
        <tr>
          <td>${escapeHtml(r.template || 'N/A')}</td>
          <td>${escapeHtml(r.dibuat_oleh || 'N/A')}</td>
          <td>${escapeHtml(r.tgl_buat || 'N/A')}</td>
          <td>${escapeHtml(r.total_budget || 'Rp0')}</td>
          <td><span class="status ${cls}"><i class="fas fa-circle"></i>${escapeHtml(r.status || 'Pending')}</span></td>
        </tr>`;
    }).join('');
    tableBody.innerHTML = html || `<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>`;
  }

  // ---------- Utils ----------
  function toInt(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
})();
