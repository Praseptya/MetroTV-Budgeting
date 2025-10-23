@extends('layouts.app')

@section('title','Create Master Item - MetroTV Budgeting')
@section('page_title','Create Master Item')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
  <link rel="stylesheet" href="{{ asset('css/master-items.css') }}">
@endpush

@section('content')
<div>

  {{-- Flash --}}
  @if(session('success') || session('error') || $errors->any())
    <div class="flash-wrap" style="margin: 0 32px;">
      @if(session('success'))
        <div class="flash success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="flash error"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div>
      @endif
      @if ($errors->any())
        <div class="flash warn">
          <b>Periksa kembali input:</b>
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
    </div>
  @endif

  <div class="mp-card">
    <form id="miForm"
          method="POST"
          action="{{ route('master.items.store') }}"
          data-store-url="{{ route('master.items.store') }}"
          data-update-template="{{ route('master.items.update', 0) }}"> 
      @csrf
      <input type="hidden" id="miMethod" name="_method" value="POST">
      <input type="hidden" id="miId" name="id_item" value="">

        <div>
          <label class="lbl">Item Name</label>
          <input type="text" name="item_name" class="inp" id="miName"
                placeholder="Nama item" value="{{ old('item_name') }}" required>
        </div>

      <div class="grid-2">
      <div>
        <label class="lbl">Bottom Price</label>
        <input
          type="text"
          name="bottom_price"
          id="miBottom"
          class="inp"
          placeholder="Rp"
          value="{{ old('bottom_price') }}"
          data-money
        >
      </div>
      <div>
        <label class="lbl">Top Price</label>
        <input
          type="text"
          name="top_price"
          id="miTop"
          class="inp"
          placeholder="Rp"
          value="{{ old('top_price') }}"
          data-money
        >
      </div>
      </div>
        <div class="col">
          <label class="lbl">Satuan / Unit</label>
          <div class="searchable">
            {{-- <input> ini harus punya name="unit_text" agar teks yang diketik terkirim --}}
            <input
              type="text"
              id="unitInput"
              name="unit_text"
              class="inp"
              placeholder="Cari / ketik unit..."
              value="{{ old('unit_text') ?? ($row->unit_name ?? '') }}"
            >

            {{-- hidden id tetap untuk kasus pilih dari daftar --}}
            <input
              type="hidden"
              name="unit_id"
              id="unitId"
              value="{{ old('unit_id') ?? ($row->unit_id ?? '') }}"
            >

            <ul class="searchable__list" id="unitList" style="display:none;">
              @foreach($units as $u)
                <li data-id="{{ $u->id_unit }}">
                  <div class="searchable__name">{{ $u->unit_name }}</div>
                </li>
              @endforeach
            </ul>
          </div>
          @error('unit_id')   <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
          @error('unit_text') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
      
      <div>
        <label class="lbl">Description</label>
        <input type="text" name="description" id="miDesc" class="inp"
              placeholder="Catatan" value="{{ old('description') }}">
      </div>

      <div class="row-gap" style="margin-top:16px;">
        <button class="btn-primary" type="submit" id="btnSave" style="display:inline-flex; align-items:center; gap:8px;">
          <i class="fa fa-save"></i> Save
        </button>
        <button type="button" class="btn-outline" id="btnCancelEdit" style="display:none;">Batal</button>
      </div>
    </form>
  </div>

@php
  $u = auth()->user();
  $__lv = null;
  if ($u) {
    $__lv = $u->level ?? $u->role ?? $u->level_name ?? null;
    if (!$__lv && isset($u->user_level_id)) {
      $__lv = \Illuminate\Support\Facades\DB::table('user_levels')
        ->where(\Illuminate\Support\Facades\Schema::hasColumn('user_levels','id_level') ? 'id_level' : 'id', $u->user_level_id)
        ->value(\Illuminate\Support\Facades\Schema::hasColumn('user_levels','level_name') ? 'level_name' : 'name');
    }
  }
  $canEditMaster = !in_array(strtolower(trim((string)($__lv))), ['staff','staf']);
@endphp
  {{-- ===== Tabel List ===== --}}
  <div class="list-card" style="margin: 0 32px 32px;">
    <div class="table-header">
      <h3>List Master Item</h3>
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="miSearch" placeholder="Search">
        </div>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table" id="miTable">
        <thead>
          <tr>
            <th style="width:60px;">No.</th>
            <th>Nama Item <i class="fa-solid fa-arrow-down-short-wide"></i></th>
            <th>Satuan</th>
            <th>Bottom Price</th>
            <th>Top Price</th>
            <th>Deskripsi Singkat</th>
            @if($canEditMaster)
              <th style="width:90px;">Action</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @php $start = ($items->currentPage()-1) * $items->perPage() + 1; @endphp
          @forelse($items as $i => $row)
            <tr data-id="{{ $row->id_item }}"
                data-name="{{ $row->item_name }}"
                data-unitid="{{ $row->unit_id }}"
                data-unitname="{{ $row->unit_name }}"
                data-bottom="{{ $row->bottom_price }}"
                data-top="{{ $row->top_price }}"
                data-desc="{{ $row->description }}">
              <td>{{ $start + $i }}</td>
              <td class="t-clip t-clip-md" title="{{ $row->item_name }}">{{ \Illuminate\Support\Str::ucfirst(ltrim((string)$row->item_name)) }}</td>
              <td>{{ $row->unit_name ?: '—' }}</td>
              <td>{{ 'Rp '.number_format($row->bottom_price,0,',','.') }}</td>
              <td>{{ 'Rp '.number_format($row->top_price,0,',','.') }}</td>
              @php
                $desc = \Illuminate\Support\Str::ucfirst(ltrim((string)$row->description));
                $desc = \Illuminate\Support\Str::limit($desc, 100);
              @endphp
              <td class="t-clip t-clip-lg" title="{{ $desc }}">{{ $desc }}</td>
              @if($canEditMaster)
              <td>
                <div class="action-buttons">
                  <button class="btn-action mi-edit" title="Edit"><i class="fa-solid fa-pen"></i></button>
                  <form method="POST" action="{{ route('master.items.destroy', $row->id_item) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action mi-del" title="Hapus"
                            onclick="return confirm('Hapus item {{ $row->item_name }}?')">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
              @endif
            </tr>
          @empty
            <tr><td colspan="7" class="text-center">Belum ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:12px;">
      {{ $items->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.__MI_UPDATE_URL = "{{ route('master.items.update', 0) }}".replace(/0$/, '__ID__');
  window.__MI_STORE_URL  = "{{ route('master.items.store') }}";
</script>
  <script src="{{ asset('js/master-items.js') }}" defer></script>
@endpush
