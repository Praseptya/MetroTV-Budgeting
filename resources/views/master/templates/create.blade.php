@extends('layouts.app')

@php
  $isEdit    = !empty($template ?? null);
  $readonly  = $readonly ?? false;
  $pageTitle = $readonly ? 'Detail Master Template' : ($isEdit ? 'Edit Master Template' : 'Create Master Template');
  $tplCat    = strtoupper(trim($template->category ?? old('category','Off Air')));
@endphp

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/master-templates.css') }}">
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
@endpush

@section('content')
@php
  $u = auth()->user();
  $role = strtolower((string)($u->level_name ?? $u->level ?? $u->role ?? ''));
  $isStaff = in_array($role, ['staff','staf']);
@endphp
  {{-- Flash --}}
  @if(session('success') || session('error') || $errors->any())
    <div class="flash-wrap">
      @if(session(key: 'success')) <div class="flash success">{{ session('success') }}</div> @endif
      @if(session(key: 'error'))   <div class="flash error">{{ session('error') }}</div>   @endif
      @if($errors->any())
        <div class="flash warn">
          <strong>Validasi gagal:</strong>
          <ul>
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif
    </div>
  @endif

  {{-- =================== FORM CARD =================== --}}
  <div class="mp-card">
    <form method="POST" action="{{ route('master.templates.store') }}" autocomplete="off">
      @csrf
      @if($isEdit)
        <input type="hidden" name="id_template" value="{{ $template->id_template }}">
      @endif

      <div class="grid-2">
        {{-- Row 1: Nama Template (full) --}}
        <div class="col-2">
          <label class="lbl">Template Name</label>
          <input type="text"
                 name="name"
                 class="inp"
                 placeholder="Nama Template"
                 value="{{ old('name', $template->name ?? '') }}"
                 {{ $readonly ? 'readonly disabled' : '' }}>
          @error('name') <div class="flash error" style="margin-top:-6px">{{ $message }}</div> @enderror
        </div>

        {{-- Row 2: Event / PIC --}}
        <div class="searchable">
          <label class="lbl">Event/Program</label>
          <input type="text"
                 id="eventInput"
                 class="inp"
                 placeholder="Cari Program..."
                 value="{{ old('event_program_name', $template->event_program_name ?? '') }}"
                 {{ $readonly ? 'readonly disabled' : '' }}>
          <input type="hidden"
                 name="event_program_id"
                 id="eventId"
                 value="{{ old('event_program_id', $template->event_program_id ?? '') }}">
          <ul class="searchable__list" id="eventList" style="display:none;">
            @foreach($programs as $p)
              <li data-id="{{ $p->id_event_program }}"
                  data-pic="{{ $p->pic_user_id }}"
                  data-category="{{ $p->category }}"
                  data-desc="{{ $p->description }}">
                <div class="searchable__name">{{ $p->name }}</div>
                <div class="searchable__meta">{{ $p->category }} | {{ $p->description }}</div>
              </li>
            @endforeach
          </ul>
          @error('event_program_id') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>

        <div class="searchable">
          <label class="lbl">PIC</label>
          <input type="text"
                 id="picInput"
                 class="inp"
                 placeholder="Cari Penanggung Jawab..."
                 value="{{ old('pic_user_name', $template->pic_user_name ?? '') }}"
                 {{ $readonly ? 'readonly disabled' : '' }}>
          <input type="hidden"
                 name="pic_user_id"
                 id="picId"
                 value="{{ old('pic_user_id', $template->pic_user_id ?? '') }}">
          <ul class="searchable__list" id="picList" style="display:none;">
            @foreach($users as $u)
              <li data-id="{{ $u->id_user }}">
                <div class="searchable__name">{{ $u->name }}</div>
              </li>
            @endforeach
          </ul>
          @error('pic_user_id') <div class="flash error" style="margin-top:-6px">{{ $message }}</div> @enderror
        </div>

        {{-- Row 3: Kategori --}}
        <div class="col-2">
          <label class="lbl">Program Category</label>
          @php $cat = old('category', $template->category ?? 'Off Air'); @endphp
          <div class="row-gap">
            <label class="radio">
              <input type="radio" name="category" value="Off Air" {{ $readonly ? 'disabled' : '' }} {{ $cat==='Off Air' ? 'checked' : '' }}>
              <span>Off Air</span>
            </label>
            <label class="radio">
              <input type="radio" name="category" value="On Air" {{ $readonly ? 'disabled' : '' }} {{ $cat==='On Air' ? 'checked' : '' }}>
              <span>On Air</span>
            </label>
          </div>
          @error('category') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>

        {{-- Row 4: Description --}}
        <div class="col-2">
          <label class="lbl">Description</label>
          <textarea name="description"
                    rows="3"
                    class="inp"
                    placeholder="Catatan"
                    {{ $readonly ? 'readonly disabled' : '' }}>{{ old('description', $template->description ?? '') }}</textarea>
          @error('description') <div class="flash error" style="margin-top:-6px">{{ $message }}</div> @enderror
        </div>
      </div>

      @unless($readonly)
        <div class="row-gap" style="margin-top:10px;">
          <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
            <i class="fa fa-save"></i> Save
          </button>
        </div>
      @endunless
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
  {{-- ===== List Template (hanya saat create) ===== --}}
  @if(!$isEdit && !$readonly)
    <div class="list-card">
      <div class="table-header">
        <h3>List Template</h3>
        <div class="table-controls">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchTemplates" placeholder="Search template">
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="data-table" id="templateTable">
          <thead>
            <tr>
              <th style="width:60px;">No.</th>
              <th>Template</th>
              <th>Penanggung Jawab</th>
              <th>Tanggal</th>
              <th>Total</th>
              <th>Kategori</th>
              <th>Deskripsi Singkat</th>
              <th style="width:92px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($templates as $i => $t)
              <tr>
                <td>{{ $i+1 }}</td>
                <td><span class="t-clip t-clip-md" title="{{ $t->template_name }}">{{ $t->template_name }}</span></td>
                <td>{{ $t->created_by_name }}</td>
                <td>{{ $t->created_date_fmt }}</td>
                <td>{{ 'Rp '.number_format($t->grand_total,0,',','.') }}</td>
                <td>
                  @php $isOn = strtolower($t->category ?? 'off air') === 'on air'; @endphp
                  <span class="tag {{ $isOn ? 'on' : 'off' }}"><span class="dot"></span>{{ $isOn ? 'On Air' : 'Off Air' }}</span>
                </td>
                <td><span class="t-clip t-clip-lg" title="{{ $t->short_desc }}">{{ Str::limit($t->short_desc, 120) }}</span></td>
                <td>
                  <div class="action-buttons">
                    <a href="{{ route('master.templates.show', $t->id_template) }}" class="btn-action" title="Detail"><i class="fas fa-eye"></i></a>
                    @if($canEditMaster)
                    <a href="{{ route('master.templates.edit', $t->id_template) }}" class="btn-action" title="Edit"><i class="fas fa-pen"></i></a>
                    
                    <form action="{{ route('master.templates.destroy', $t->id_template) }}" method="POST" onsubmit="return confirm('Hapus template ini?')" style="display:inline;">
                      @csrf @method('DELETE')
                      <button class="btn-action" type="submit" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center">Belum ada template</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  {{-- =================== LIST ITEM (edit/detail) =================== --}}
  @if($isEdit || $readonly)
    <div class="mp-card" style="padding-top:20px;">
      <div class="table-header">
        <h3 style="font-weight:700;">List Item</h3>
      </div>

      <div class="table-container">
        <table class="data-table" id="tiTable"
              @if($isEdit && !$readonly)
              data-qty-url-template="{{ route('master.templates.items.qty', ['id' => $template->id_template, 'rowId' => 0]) }}"
              @endif
        >
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
            @forelse($templateItems as $i => $row)
              @php
                $total = ((int)$row->qty) * ((float)$row->price);
                $gt += $total;
                $isOn = ($tplCat === 'ON AIR');
              @endphp
              <tr data-row-id="{{ $row->row_id }}">
                <td>{{ $i+1 }}.</td>
                <td style="font-weight:600; color:#0f172a;">{{ $row->item_name }}</td>
                <td title="{{ $row->description }}">{{ \Illuminate\Support\Str::limit($row->description, 45) ?: '—' }}</td>
                <td class="text-right">
                  @if($readonly)
                    {{ (int)$row->qty }}
                  @else
                    <div class="qty-ctrl">
                      <button type="button" class="qty-btn qty-minus" title="Kurangi" aria-label="Kurangi">−</button>
                      <input type="text" class="qty-input" value="{{ (int)$row->qty }}" inputmode="numeric" pattern="[0-9]*">
                      <button type="button" class="qty-btn qty-plus" title="Tambah" aria-label="Tambah">+</button>
                    </div>
                  @endif
                </td>
                <td>{{ $row->unit ?? $row->unit_name ?? '-' }}</td>
                <td class="text-right">{{ 'Rp '.number_format($row->price,0,',','.') }}</td>
                <td class="text-right">
                  <span class="ti-row-total">{{ 'Rp '.number_format($total,0,',','.') }}</span>
                </td>
                <td class="text-center">
                  @unless($readonly)
                    <form action="{{ route('master.templates.items.destroy', [$template->id_template, $row->row_id]) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus item ini?');"
                          style="display:inline;">
                      @csrf @method('DELETE')
                      <button class="btn-action" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  @else
                    <span style="color:#94a3b8;">—</span>
                  @endunless
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center">Belum ada item.</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" style="text-align:right;">Grand Total</th>
              <th class="text-right"><span id="tiGrandTotal">{{ 'Rp '.number_format($grandTotal ?? 0,0,',','.') }}</span></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  @endif

  {{-- =================== STICKY FOOTER =================== --}}
  @if($isEdit && empty($readonly))
    <div class="mt-stickyfoot">
      <div class="mt-stickyfoot__inner">
        <button id="btnAddItem" type="button" class="md-btn md-btn--primary" aria-label="Add Item">
          <i class="fas fa-plus"></i>
          <span>Add Item</span>
        </button>
        <a href="{{ route('master.templates.index') }}" class="md-btn md-btn--success" aria-label="Done">
          <span>Done</span>
        </a>
      </div>
    </div>
  @endif

  {{-- =================== MODAL ADD ITEM =================== --}}
  @unless($readonly)
    <div id="mtModal" class="mt-modal">
      <div class="mt-modal__panel">
        <div class="mt-modal__head">
          <h3>Tambah Item dari Master</h3>
          <button type="button" class="mt-modal__close" onclick="closeMtModal()">×</button>
        </div>
        <div class="mt-modal__body">
          <input id="miSearch" class="inp" type="text"
            placeholder="Cari item (min 2 huruf)…"
            data-search-url="{{ route('master.templates.item.search', [], false) }}">
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
              <tbody id="miResult">
                <tr><td colspan="4" class="text-center">Ketik untuk mencari item…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="mt-modal__foot">
          <button type="button" class="btn-outline" onclick="closeMtModal()">Close</button>
        </div>
      </div>
    </div>

    <form id="miAddForm"
          method="POST"
          action="{{ $isEdit ? route('master.templates.items.store', $template->id_template) : '#' }}"
          style="display:none;">
      @csrf
      <input type="hidden" name="item_id" id="miItemId">
      <input type="hidden" name="qty" value="1">
    </form>
  @endunless
  <input type="hidden" id="afterAddItem" value="{{ session('afterAddItem') ? '1' : '0' }}">
@endsection

@push('scripts')
  <div id="route-mi-search"
      data-url="{{ route('master.templates.item.search', [], false) }}"
      style="display:none"></div>
  <script>
    window.MT_CONF = window.MT_CONF || {};
    window.MT_CONF.miSearchUrl = @json(route('master.templates.item.search', [], false));
  </script>
  <script src="{{ asset('js/master-templates.js') }}"></script>
@endpush

