@extends('layouts.app')

@section('title','Create Master Program')
@section('page_title','Create Master Program')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
<link rel="stylesheet" href="{{ asset('css/master-program.css') }}">
@endpush

@section('content')

  {{-- Flash --}}
  @if(session('success'))
    <div class="flash success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="flash warn">
      <strong>Validasi gagal:</strong>
      <ul>
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Form (Create / Edit inline) ===== --}}
  <div class="mp-card">
    <form
      id="mpForm"
      method="POST"
      action="{{ route('master.program.store') }}"
      data-store-url="{{ route('master.program.store') }}"
      data-update-template="{{ route('master.program.update', 0) }}"
      autocomplete="off"
    >
      @csrf
      <input type="hidden" id="mpMethod" name="_method" value="POST">
      <input type="hidden" id="mpId"     name="id_event_program" value="">

      <div class="grid-2">
        <div>
          <label class="lbl">Program Name</label>
          <input type="text" name="name" class="inp" id="f_name" placeholder="Nama Program" value="{{ old('name') }}" required>
        </div>
        <div class="searchable col">
          <label class="lbl">Penanggung Jawab</label>
          <input type="text" id="mpPicInput" class="inp" placeholder="Cari Penanggung Jawab..." value="{{ old('pic_user_name') }}">
          <input type="hidden" name="pic_user_id" id="mpPicId" value="{{ old('pic_user_id') }}">
          <ul class="searchable__list" id="mpPicList" style="display:none;">
            @foreach($users as $u)
              <li data-id="{{ $u->id_user }}">
                <div class="searchable__name">{{ $u->name }}</div>
              </li>
            @endforeach
          </ul>
          @error('pic_user_id') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="row-gap">
        <div>
          <label class="lbl">Program Category</label>
          @php $categoryOld = old('category','Off Air'); @endphp
          <label class="radio">
            <input type="radio" name="category" value="Off Air" id="f_category_off" {{ $categoryOld === 'Off Air' ? 'checked' : '' }}>
            <span>Off Air</span>
          </label>
          <label class="radio">
            <input type="radio" name="category" value="On Air" id="f_category_on" {{ $categoryOld === 'On Air' ? 'checked' : '' }}>
            <span>On Air</span>
          </label>
        </div>
      </div>

      <div>
        <label class="lbl">Description</label>
        <input type="text" name="description" class="inp" id="f_desc" placeholder="Catatan" value="{{ old('description') }}">
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
  {{-- ===== List Table ===== --}}
  <div class="list-card">
    <div class="table-header">
      <h3>List Master Program</h3>
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="searchPrograms" placeholder="Search program">
        </div>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table" id="programTable">
        <thead>
          <tr>
            <th style="width:60px;">No.</th>
            <th>Nama Program</th>
            <th>Penanggung Jawab</th>
            <th>Kategori</th>
            <th>Deskripsi Singkat</th>
            @if($canEditMaster)
            <th style="width:92px;">Action</th>
            @endif
          </tr>
        </thead>
        <tbody>
        @php use Illuminate\Support\Str; @endphp
        @forelse($programs as $i => $p)
          <tr
            data-id="{{ $p->id_event_program }}"
            data-name="{{ $p->name }}"
            data-type="{{ $p->category }}"
            data-desc="{{ $p->description }}"
            data-pic="{{ $p->pic_user_id }}"
          >
            <td>{{ $programs->firstItem() + $i }}</td>
            <td class="t-clip t-clip-md" title="{{ $p->name }}">{{ Str::ucfirst(ltrim((string)($p->name ?? ''))) }}</td>
            <td>{{ $p->pic_name ?? '—' }}</td>
            <td>
              @php $isOn = strtolower($p->category ?? '') === 'on air'; @endphp
              <span class="tag {{ $isOn ? 'on' : 'off' }}">
                <span class="dot"></span>{{ $isOn ? 'On Air' : 'Off Air' }}
              </span>
            </td>
            <td class="t-clip t-clip-lg" title="{{ $p->description }}">{{ Str::limit(Str::ucfirst(ltrim((string)($p->description ?? '-'))), 60) }}</td>
            <td>
              @if($canEditMaster)
              <div class="action-buttons">
                <button type="button" class="btn-action mp-edit" title="Edit">
                  <i class="fas fa-pen"></i>
                </button>
                <form action="{{ route('master.program.destroy', $p->id_event_program) }}" method="POST" 
                      onsubmit="return confirm('Hapus program ini?')" style="display:inline;">
                  @csrf @method('DELETE')
                  <button class="btn-action" type="submit" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center">Belum ada data</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:12px;">
      {{ $programs->links() }}
    </div>
  </div>

@endsection

@push('scripts')
<script>
  window.__MP = {
    store : @json(route('master.program.store')),
    update: @json(route('master.program.update', 0)), // akan diganti id di JS
  };
</script>
<script src="{{ asset('js/master-programs.js') }}" defer></script>
@endpush