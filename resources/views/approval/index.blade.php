@extends('layouts.app')

@section('title','Approval Budget')
@section('page_title','Approval Budget')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
  <link rel="stylesheet" href="{{ asset('css/budget.css') }}">
@endpush

@section('content')
<div class="list-card">
  <div class="table-header">
    <div class="table-controls">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchBudgets" placeholder="Search Name">
      </div>
    </div>
  </div>

  <div class="table-container">
    <table class="data-table" id="approvalTable">
      <thead>
        <tr>
          <th style="width:60px;">No.</th>
          <th>Template</th>
          <th>Dibuat Oleh</th>
          <th>Periode</th>
          <th>Total</th>
          <th>Status</th>
          <th style="width:120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($budgets as $i => $b)
          <tr>
            <td>{{ $i+1 }}</td>
            <td class="t-multiline">{{ $b->budget_name }}</td>
            <td>{{ $b->created_by }}</td>
            <td>{{ date('d M', strtotime($b->periode_from)) }} - {{ date('d M', strtotime($b->periode_to)) }}</td>
            <td>{{ 'Rp '.number_format($b->total,0,',','.') }}</td>
            <td>
              @php
                $status = strtolower($b->status ?? 'pending');
                $class = $status == 'approved' ? 'on' : ($status == 'rejected' ? 'off' : 'pending');
              @endphp
              <span class="tag {{ $class }}">
                <span class="dot"></span> {{ ucfirst($status) }}
              </span>
            </td>
            <td>
                <div class="action-buttons">
                    <a href="{{ route('budgets.show', $b->id_budget) }}" class="btn-action" title="View Detail">
                    <i class="fas fa-eye"></i>
                    </a>

                    @if($b->status === 'Pending')
                    {{-- Approve --}}
                    <form method="POST" action="{{ route('approval.approve', $b->id_budget) }}" style="display:inline;">
                        @csrf
                        <button class="btn-action btn-approve" title="Approve">
                        <i class="fas fa-check"></i>
                        </button>
                    </form>

                    {{-- Reject --}}
                    <form method="POST" action="{{ route('approval.reject', $b->id_budget) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="reason" class="reject-reason">
                        <button class="btn-action btn-reject" title="Reject">
                        <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @endif

                    @if(strtolower($b->status ?? '') !== 'pending')
                    <form action="{{ route('budgets.destroy', $b->id_budget) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data budget ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action btn-reject" title="Hapus">
                        <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </td>

          </tr>
        @empty
          <tr><td colspan="7" class="text-center">Tidak ada budget untuk disetujui</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchBudgets').addEventListener('input', function(){
  const q = this.value.toLowerCase();
  document.querySelectorAll('#approvalTable tbody tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  document.querySelectorAll('.btn-approve, .btn-reject').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();

      const form = this.closest('form');
      const isApprove = this.classList.contains('btn-approve');

      if (isApprove) {
        // ===== APPROVE CONFIRMATION =====
        Swal.fire({
          title: 'Setujui Budget Ini?',
          text: 'Pastikan semua data sudah benar sebelum menyetujui.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Setujui',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#16a34a',
          cancelButtonColor: '#6b7280',
        }).then((result) => {
          if (result.isConfirmed) form.submit();
        });

      } else {
        // ===== REJECT WITH REASON =====
        Swal.fire({
          title: 'Tolak Budget Ini?',
          html: `
            <p style="font-size:14px; color:#6b7280; margin-bottom:8px;">Tuliskan alasan penolakan di bawah ini:</p>
            <textarea id="rejectReason" class="swal2-textarea" placeholder="Contoh: Anggaran terlalu besar untuk kategori ini" style="height:90px"></textarea>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Kirim Penolakan',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#6b7280',
          preConfirm: () => {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
              Swal.showValidationMessage('Harap tuliskan alasan penolakan');
              return false;
            }
            return reason;
          }
        }).then((result) => {
          if (result.isConfirmed && result.value) {
            form.querySelector('.reject-reason').value = result.value;
            form.submit();
          }
        });
      }
    });
  });
</script>
@endpush
