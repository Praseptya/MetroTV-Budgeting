@extends('layouts.app')

@section('title','Laporan Pengajuan Anggaran')
@section('page_title','Laporan Pengajuan Anggaran')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
@endpush
@section('content')
  <div class="mp-card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('dashboard.report') }}" class="grid-2" autocomplete="off">
      <div>
        <label class="lbl">Kata kunci</label>
        <input type="text" class="inp" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama budget / template / deskripsi">
      </div>
      <div>
        <label class="lbl">Departemen</label>
        <input type="text" class="inp" name="dept" value="{{ $filters['dept'] ?? '' }}" placeholder="Nama departemen">
      </div>

      <div>
        <label class="lbl">Status</label>
        @php $st = $filters['status'] ?? 'all'; @endphp
        <select name="status" class="inp">
          <option value="all" {{ $st==='all'?'selected':'' }}>Semua</option>
          <option value="Pending" {{ $st==='Pending'?'selected':'' }}>Pending</option>
          <option value="Approved" {{ $st==='Approved'?'selected':'' }}>Approved</option>
          <option value="Rejected" {{ $st==='Rejected'?'selected':'' }}>Rejected</option>
          <option value="SendBack" {{ $st==='SendBack'?'selected':'' }}>Send Back</option>
        </select>
      </div>

      <div>
        <label class="lbl">Periode</label>
        @php $rg = $filters['range'] ?? '12b'; @endphp
        <select name="range" class="inp">
          <option value="12b" {{ $rg==='12b'?'selected':'' }}>12 bulan terakhir</option>
          <option value="30h" {{ $rg==='30h'?'selected':'' }}>30 hari terakhir</option>
          <option value="7h"  {{ $rg==='7h' ?'selected':'' }}>7 hari terakhir</option>
          <option value="24j" {{ $rg==='24j'?'selected':'' }}>24 jam terakhir</option>
        </select>
      </div>

      <div class="col">
        <label class="lbl">&nbsp;</label>
        <button class="btn-primary btn-solid" type="submit">Terapkan Filter</button>
        <a class="btn-outline" href="{{ route('dashboard.report') }}">Reset</a>
      </div>

      <div class="col" style="display:flex; align-items:flex-end; justify-content:flex-end;">
        <a class="btn-primary" href="{{ route('dashboard.export', request()->query()) }}">
          <i class="fa fa-file-export"></i> Export
        </a>
      </div>
    </form>
  </div>

  <div class="list-card">
    <div class="table-header">
      <h3>Hasil Laporan</h3>
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="localSearch" placeholder="Search cepat di tabel ini">
        </div>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table" id="reportTable">
        <thead>
          <tr>
            <th style="width:64px;">No.</th>
            <th>Nama Budget</th>
            <th>Departemen</th>
            <th>Periode</th>
            <th>Template</th>
            <th>PIC</th>
            <th>Dibuat Oleh</th>
            <th>Tgl Buat</th>
            <th>Total Budget</th>
            <th>Status</th>
            <th>Deskripsi</th>
          </tr>
        </thead>
        <tbody>
          @php
            use Illuminate\Support\Str;
            $start = ($rows->currentPage()-1) * $rows->perPage() + 1;
          @endphp
          @forelse($rows as $i => $r)
            @php
              $status = $r->status ?? 'Pending';
              $cls =
                $status === 'Approved' ? 'approved' :
                ($status === 'Rejected' ? 'rejected' :
                ($status === 'SendBack' ? 'revision' : 'pending'));
              $statusText =
                $status === 'Approved' ? 'Approve' :
                ($status === 'Rejected' ? 'Ditolak' :
                ($status === 'SendBack' ? 'Revisi' : 'Pending'));
              $period = ($r->periode_from && $r->periode_to)
                        ? \Carbon\Carbon::parse($r->periode_from)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($r->periode_to)->format('d M Y')
                        : '—';
              $desc = Str::limit((string)$r->description, 120);
            @endphp
            <tr>
              <td>{{ $start + $i }}</td>
              <td title="{{ $r->budget_name }}">{{ Str::limit($r->budget_name, 20, '..') }}</td>
              <td title="{{ $r->dept }}">{{ Str::limit($r->dept, 15, '..') }}</td>
              <td>{{ $period }}</td>
              <td title="{{ $r->template_name }}">{{ Str::limit($r->template_name, 15, '..') }}</td>
              <td title="{{ $r->pic_name }}">{{ Str::limit($r->pic_name, 15, '..') }}</td>
              <td title="{{ $r->creator_name }}">{{ Str::limit($r->creator_name, 15, '..') }}</td>
              <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
              <td>{{ 'Rp '.number_format((int)$r->total_amount,0,',','.') }}</td>
              <td>
                <span class="status {{ $cls }}"><i class="fas fa-circle"></i>{{ $statusText }}</span>
              </td>
              <td title="{{ $r->description }}">{{ Str::limit((string)$r->description, 20, '..') }}</td>
            </tr>
          @empty
            <tr><td colspan="11" class="text-center">Tidak ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:12px;">
      {{ $rows->links() }}
    </div>
  </div>
@endsection

@push('scripts')
<script>
(function(){
  const search = document.getElementById('localSearch');
  const tbody  = document.querySelector('#reportTable tbody');
  if (search && tbody) {
    search.addEventListener('input', function(){
      const q = this.value.toLowerCase();
      tbody.querySelectorAll('tr').forEach(tr=>{
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
})();
</script>
@endpush
