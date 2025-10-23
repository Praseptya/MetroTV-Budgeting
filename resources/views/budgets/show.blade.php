@extends('layouts.app')

@section('title','Detail Budget')
@section('page_title','Detail Budget')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/budget.css') }}">
<link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
@endpush

@section('content')
  {{-- Flash --}}
  

<div style="display:flex; margin:10px 0px 5px 30px;">
  <a href="{{ route('budgets.create') }}"
     class="btn-outline">
    <i class="fa fa-arrow-left"></i>Back
  </a>
  @if(session('success') || session('error'))
      @if(session('success')) <div class="flash success" style="margin-left: 350px">{{ session('success') }}</div> @endif
      @if(session('error'))   <div class="flash error" style="margin-left: 200px;">{{ session('error') }}</div>   @endif
  @endif
</div>

<div class="mp-card">
  <div class="grid-2">
    {{-- Template --}}
    <div class="col-2">
      <label class="lbl">Template</label>
      <input class="inp"
             value="{{ $budget->template_name ?? '—' }}"
             readonly>
    </div>

    <div>
      <label class="lbl">Budget Name</label>
      <input class="inp"
             value="{{ $budget->budget_name ?? '-' }}"
             readonly>
    </div>
    <div>
      <label class="lbl">Total Budget</label>
      <input class="inp"
             value="{{ 'Rp '.number_format($grandTotal ?? 0,0,',','.') }}"
             readonly>
    </div>

    <div>
      <label class="lbl">Department</label>
      <input class="inp"
             value="{{ $deptName ?? '—' }}"
             readonly>
    </div>
    <div>
      <label class="lbl">Period</label>
      <input class="inp"
             value="{{ $periodeFmt ?? '—' }}"
             readonly>
    </div>

    <div>
      <label class="lbl">PIC</label>
      <input type="text" class="inp" value="{{ $budget->program_pic_name ?? '—' }}" readonly>
    </div>

    <div>
      <label class="lbl">Program Category</label>
      @php $isOn = strtolower($budget->program_category ?? '') === 'on air'; @endphp
      <div class="row-gap">
        <span class="badge {{ $isOn ? 'badge-on' : 'badge-off' }}">
          {{ $isOn ? 'On Air' : 'Off Air' }}
        </span>
      </div>
    </div>

    <div>
      <label class="lbl">Created By</label>
      <input type="text" class="inp" value="{{ $budget->created_by_name ?? '—' }}" readonly>
    </div>
    <div>
      <label class="lbl">Created At</label>
      <input type="text" class="inp" value="{{ $createdAtFmt ?? '—' }}" readonly>
    </div>

    {{-- Deskripsi (full width) --}}
    <div class="col-2">
      <label class="lbl">Description</label>
      <textarea class="inp"
                rows="3"
                readonly>{{ $budget->description ?? '' }}</textarea>
    </div>
  </div>
</div>

@if(isset($budget->status) && strtolower($budget->status) === 'rejected')
  <div class="flash error" style="margin-top:-20px;">
    <strong>Alasan Penolakan:</strong><br>
    <span style="display:block; margin-top:4px; color:#991b1b;">
      {{ $budget->rejection_reason ?? '(Tidak ada alasan dicantumkan)' }}
    </span>
  </div>
@endif
{{-- ===== List Item Budget ===== --}}
<div class="mp-card" style="margin-top:24px;">
  <div class="table-header">
    <h3 style="font-weight:700;">List Item</h3>
  </div>

  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:60px;">No.</th>
          <th>Nama Item</th>
          <th>Deskripsi Singkat</th>
          <th class="text-right">Qty</th>
          <th>Satuan</th>
          <th class="text-right">Harga Satuan</th>
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $i => $row)
          <tr>
            <td>{{ $i+1 }}</td>
            <td style="font-weight:600; color:#0f172a;">{{ $row->item_name }}</td>
            <td title="{{ $row->short_desc }}">{{ \Illuminate\Support\Str::limit($row->short_desc, 45) ?: '—' }}</td>
            <td class="text-right">{{ (int)$row->qty }}</td>
            <td>{{ $row->unit ?: '-' }}</td>
            <td class="text-right">{{ 'Rp '.number_format($row->unit_price,0,',','.') }}</td>
            <td class="text-right">{{ 'Rp '.number_format($row->amount,0,',','.') }}</td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center">Belum ada item.</td></tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <th colspan="6" style="text-align:right;">Grand Total</th>
          <th class="text-right">{{ 'Rp '.number_format($grandTotal ?? 0,0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
