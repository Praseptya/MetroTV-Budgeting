@extends('layouts.app')

@section('title','Edit Budget')
@section('page_title','Edit Budget')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
  <link rel="stylesheet" href="{{ asset('css/budget.css') }}">
@endpush

@section('content')
@if(session('success'))
  <div class="flash success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash error">{{ session('error') }}</div>
@endif
@if($errors->any())
  <div class="flash warn">
    <strong>Validasi gagal:</strong>
    <ul>
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

{{-- =================== FORM EDIT =================== --}}
<div class="mp-card">
  <form method="POST" action="{{ route('budgets.update', $budget->id_budget) }}">
    @csrf
    @method('PUT')

    <div class="grid-2">
      {{-- Template --}}
      <div class="col-2">
        <label class="lbl">Template</label>
        <select id="tplSelect"
                name="template_id"
                class="inp"
                required
                data-detail-url="{{ route('budgets.template.detail', ['id' => '__ID__'], false) }}">
          <option value="">Pilih Template</option>
          @foreach($templates as $t)
            <option value="{{ $t->id_template }}"
              {{ $t->id_template == $budget->template_id ? 'selected' : '' }}>
              {{ $t->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="lbl">Budget Name</label>
        <input type="text" name="budget_name" class="inp"
               value="{{ old('budget_name', $budget->budget_name) }}" required>
      </div>
      <div>
        <label class="lbl">Total Budget</label>
        <input type="text" class="inp" readonly
               value="{{ 'Rp '.number_format(DB::table('budget_items')->where('budget_id',$budget->id_budget)->sum('amount'),0,',','.') }}">
      </div>

      <div>
        <label class="lbl">Department</label>
        <input type="text" name="dept" class="inp"
               value="{{ old('dept',$budget->dept) }}" required>
      </div>
      <div class="grid-2">
        <div>
          <label class="lbl">Periode From</label>
          <input
            type="date"
            name="periode_from"
            id="periode_from"
            class="inp"
            value="{{ old('periode_from', $periodeFrom ?? '') }}"
            required
          >
        </div>

        <div>
          <label class="lbl">Periode To</label>
          <input
            type="date"
            name="periode_to"
            id="periode_to"
            class="inp"
            value="{{ old('periode_to', $periodeTo ?? '') }}"
            required
          >
        </div>
      </div>
      <div class="col-2">
        <label class="lbl">Description</label>
        <textarea name="description" rows="3" class="inp" required>{{ old('description',$budget->description) }}</textarea>
      </div>
    </div>

    <div class="row-gap" style="margin-top:12px; justify-content:flex-end; display:flex; gap:10px;">
      <a href="{{ route('budgets.create') }}" class="btn-outline">
        Cancel
      </a>
      <button class="btn-primary btn-solid" type="submit" style="display:inline-flex; align-items:center; gap:8px;">
        <i class="fa fa-save"></i> Update
      </button>
    </div>
  </form>
</div>

{{-- ===== List Item (Edit Budget) ===== --}}
<div class="mp-card" style="padding-top:20px;">
  <div class="table-header">
    <h3 style="font-weight:700;">List Item</h3>
    <div>
      <button id="btnAddBi" type="button" class="btn-outline">+ Tambah Item</button>
    </div>
  </div>

  <div class="table-container">
    <table class="data-table" id="biTable"
           data-qty-url-template="{{ route('budgets.items.qty', ['id' => $budget->id_budget, 'rowId' => 0]) }}">
      <thead>
        <tr>
          <th style="width:60px;">No.</th>
          <th>Nama Item</th>
          <th>Deskripsi Singkat</th>
          <th class="text-right">QTY</th>
          <th>Satuan</th>
          <th class="text-right">Harga Satuan</th>
          <th class="text-right">Total</th>
          <th class="text-center" style="width:60px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @php $gt=0; @endphp
        @forelse($items as $i => $row)
          @php
            $total = (int)$row->amount;
            $gt += $total;
          @endphp
          <tr data-row-id="{{ $row->id_budget_item }}">
            <td>{{ $i+1 }}.</td>
            <td style="font-weight:600; color:#0f172a;">{{ $row->item_name }}</td>
            <td title="{{ $row->short_desc }}">{{ \Illuminate\Support\Str::limit($row->short_desc, 60) ?: '—' }}</td>

            {{-- QTY dengan kontrol plus/minus --}}
            <td class="text-right">
              <div class="qty-ctrl">
                <button type="button" class="qty-btn qty-minus" title="Kurangi">−</button>
                <input type="text" class="qty-input" value="{{ (int)$row->qty }}" inputmode="numeric" pattern="[0-9]*">
                <button type="button" class="qty-btn qty-plus" title="Tambah">+</button>
              </div>
            </td>

            <td>{{ $row->unit ?: '-' }}</td>
            <td class="text-right">{{ 'Rp '.number_format($row->unit_price,0,',','.') }}</td>
            <td class="text-right">
              <span class="bi-row-total">{{ 'Rp '.number_format($total,0,',','.') }}</span>
            </td>
            <td class="text-center">
              <form action="{{ route('budgets.items.destroy', [$budget->id_budget, $row->id_budget_item]) }}"
                    method="POST" onsubmit="return confirm('Hapus item ini?');" style="display:inline;">
                @csrf @method('DELETE')
                <button class="btn-action" title="Delete"><i class="fa fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center">Belum ada item.</td></tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <th colspan="6" style="text-align:right;">Grand Total</th>
          <th class="text-right"><span id="biGrandTotal">{{ 'Rp '.number_format($grandTotal ?? ($gt ?? 0),0,',','.') }}</span></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

{{-- MODAL --}}
<div id="biModal"
     class="mt-modal"
     data-search-url="{{ route('master.templates.item.search') }}">
  <div class="mt-modal__panel">
    <div class="mt-modal__head">
      <h3>Tambah Item dari Master</h3>
      <button type="button" class="mt-modal__close" data-close="1">×</button>
    </div>

    <div class="mt-modal__body">
      <input id="biSearch"
             class="inp"
             placeholder="Cari item… (kosongkan untuk lihat semua)">
      <div class="table-container" style="margin-top:12px;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Satuan</th>
              <th class="text-right">Harga</th>
              <th style="width:120px;">Action</th>
            </tr>
          </thead>
          <tbody id="biResult">
            <tr><td colspan="4" class="text-center">Memuat…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-modal__foot">
      <button type="button" class="btn-outline" data-close="1" style="margin-bottom: 5px;">Close</button>
    </div>
  </div>
</div>

{{-- form add (hidden) --}}
<form id="biAddForm" method="POST"
      action="{{ route('budgets.items.store', $budget->id_budget) }}"
      style="display:none;">
  @csrf
  <input type="hidden" name="item_id" id="biItemId">
</form>

@endsection

@push('scripts')
<script>
    document.body.dataset.budgetItemSearch = @json(route('budgets.item.search'));
    document.body.dataset.scrollBottom = @json(session('scroll_bottom') ? '1' : '0');
</script>
<script src="{{ asset('js/budget-create.js') }}" defer></script>
<script src="{{ asset('js/budget-edit.js') }}" defer></script>
@endpush
