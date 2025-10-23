@extends('layouts.app')

@section('title','Create New Budget')
@section('page_title','Create New Budget')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/budget.css') }}">
<link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
@endpush

@section('content')
  {{-- Flash --}}
  @if(session('success') || session('error') || $errors->any())
    <div class="flash-wrap">
      @if(session('success')) <div class="flash success">{{ session('success') }}</div> @endif
      @if(session('error'))   <div class="flash error">{{ session('error') }}</div>   @endif
      @if($errors->any())
        <div class="flash warn">
          <strong>Validasi gagal:</strong>
          <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
    </div>
  @endif

  <div class="mp-card">
    <form method="POST" action="{{ route('budgets.store') }}" autocomplete="off">
      @csrf
      <div class="grid-2">
        <div class="col-2">
            <label class="lbl">Template</label>
            <select id="tplSelect"
                    name="template_id"
                    class="inp"
                    data-detail-url="{{ route('budgets.template.detail', ['id' => '__ID__'], false) }}"
                    required>
            <option value="">Pilih Template</option>
            @foreach($templates as $t)
                <option value="{{ $t->id_template }}">{{ $t->name }}</option>
            @endforeach
            </select>
            @error('template_id') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="lbl">Budget Name</label>
            <input id="budgetName"
                type="text"
                name="budget_name"
                class="inp"
                placeholder="Nama budget"
                value="{{ old('budget_name') }}"
                required>
            @error('budget_name') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="lbl">Total Budget</label>
            <input id="totalBudget"
                type="text"
                class="inp"
                placeholder="—"
                value=""
                readonly required>
        </div>
        <div>
            <label class="lbl">Department</label>
            <input type="text"
                name="dept"
                class="inp"
                placeholder="Nama Departemen"
                value="{{ old('dept') }}" required>
            @error('dept') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
        <div class="grid-2">
          <div>
            <label class="lbl">Periode from</label>
            <input type="date" name="periode_from" id="periodeFrom" class="inp date-picker"
                  value="{{ old('periode_from') }}" required>
          </div>
          <div>
            <label class="lbl">Periode to</label>
            <input type="date" name="periode_to" id="periodeTo" class="inp date-picker"
                  value="{{ old('periode_to') }}" required>
          </div>
          @error('periode_from') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
          @error('periode_to')   <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
        </div>
        <div class="col-2">
            <label class="lbl">Description</label>
            <textarea id="descInput"
                    name="description"
                    rows="3"
                    class="inp"
                    placeholder="Catatan" required>{{ old('description') }}</textarea>
        </div>
      </div>

      <div class="row-gap" style="margin-top:10px;">
        <button class="btn-primary" type="submit" style="display:inline-flex;align-items:center;gap:8px;">
          <i class="fa fa-save"></i> Save
        </button>
      </div>
    </form>
  </div>

<div class="list-card">
  <div class="table-header">
    <h3>List Budget</h3>
  </div>

  <div class="table-container">
    <table class="data-table" id="budgetTable">
      <thead>
        <tr>
            <th style="width:60px;">No.</th>
            <th>Budget</th>
            <th>Dibuat Oleh</th>
            <th>Departemen</th>
            <th>Periode</th>
            <th>Total</th>
            <th>Status</th>
            <th>Deskripsi Singkat</th>
            <th style="width:132px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @php use Illuminate\Support\Str; @endphp
        @forelse($budgets as $i => $b)
            @php
            $status     = strtolower(trim($b->status ?? 'pending'));
            $isApproved = $status === 'approved';
            $isPending  = $status === 'pending';
            $isRejected = in_array($status, ['rejected','ditolak']);
            @endphp
            <tr>
            <td>{{ $i+1 }}</td>
            <td title="{{ $b->budget_name }}">
                <span class="cell-ellipsis cell-w-budget">{{ $b->budget_name }}</span>
            </td>
            <td class="t-ellipsis" title="{{ $b->created_by_name }}">{{ $b->created_by_name }}</td>
            <td class="t-ellipsis" title="{{ $b->dept_name }}">{{ $b->dept_name ?: '—' }}</td>
            <td>{{ $b->periode_fmt }}</td>
            <td>{{ 'Rp '.number_format($b->grand_total,0,',','.') }}</td>
            <td>
                <span class="status-pill {{ $isApproved ? 'approved' : ($isRejected ? 'rejected' : 'pending') }}">
                <span class="dot"></span>
                {{ $isApproved ? 'Approved' : ($isRejected ? 'Rejected' : 'Pending') }}
                </span>
            </td>
            <td title="{{ $b->description }}">
                <span class="cell-ellipsis cell-w-desc">
                    {{ \Illuminate\Support\Str::limit($b->description, 140) }}
                </span>
            </td>
            <td>
                <div class="action-buttons" style="display:flex; gap:6px;">
                <a href="{{ route('budgets.show', $b->id_budget) }}" class="btn-action" title="Detail">
                    <i class="fas fa-eye"></i>
                </a>

                @if($isApproved)
                    <a href="{{ route('budgets.show', $b->id_budget) }}#download" class="btn-action" title="Download">
                    <i class="fas fa-download"></i>
                    </a>
                @elseif($isPending)
                    <a href="{{ route('budgets.edit', $b->id_budget) }}" class="btn-action" title="Edit">
                    <i class="fas fa-pen"></i>
                    </a>
                    <form action="{{ route('budgets.destroy', $b->id_budget) }}" method="POST"
                        onsubmit="return confirm('Hapus budget ini?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn-action" type="submit" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                    </form>
                @elseif($isRejected)
                    <a href="{{ route('budgets.edit', $b->id_budget) }}" class="btn-action" title="Revisi">
                    <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('budgets.destroy', $b->id_budget) }}" method="POST"
                        onsubmit="return confirm('Hapus budget ini?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn-action" type="submit" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                    </form>
                @endif
                </div>
            </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center">Belum ada budget.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/budget-create.js') }}" defer></script>
@endpush
