@extends('layouts.app')

@section('title', 'Management User - MetroTV Budgeting')
@section('page_title', 'Management User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
@endpush

@section('content')
    {{-- Flash --}}
    @if(session('success') || session('error') || $errors->any())
        <div class="flash-wrap">
            @if(session('success'))
                <div class="flash success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash error"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash warn">
                    <b>Periksa kembali input:</b>
                    <ul>
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Tambah User --}}
    <section class="charts-section">
        <div class="chart-card" style="margin-top:30px; margin-bottom:-20px;">
            <div class="chart-header" style="margin-bottom:0;"><h3>Tambah User</h3></div>
            <div class="chart-content" style="padding-top:16px; height:auto;">
                <form method="POST" action="{{ route('users.store') }}" autocomplete="off">
                    @csrf
                    <div class="grid-2">
                        <div>
                            <label class="lbl">Nama</label>
                            <input name="name" type="text" value="{{ old('name') }}" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Email</label>
                            <input name="email" type="email" value="{{ old('email') }}" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Username</label>
                            <input name="username" type="text" value="{{ old('username') }}" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Level/Role</label>
                            <select name="role" required class="inp">
                                <option value="">Pilih Level</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id_level }}" @selected(old('role') == $lvl->id_level)>{{ $lvl->level_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="lbl">Password</label>
                            <input name="password" type="password" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Konfirmasi Password</label>
                            <input name="password_confirmation" type="password" required class="inp">
                        </div>
                    </div>
                    <div class="row-gap">
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Edit User --}}
    <section class="charts-section" id="editCardWrap" style="display:none;">
        <div class="chart-card">
            <div class="chart-header" style="margin-bottom:0;">
                <h3>Edit User</h3>
                <div class="card-subtabs"><button type="button" id="btnCloseEdit" class="btn-outline">Tutup</button></div>
            </div>
            <div class="chart-content" style="padding-top:16px; height:auto;">
                <form id="formEdit" method="POST" action=""
                    data-update-tpl="{{ route('users.update', 0) }}">
                    @csrf @method('PUT')
                    <div class="grid-2">
                        <div>
                            <label class="lbl">Nama</label>
                            <input id="edit_name" name="name" type="text" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Email</label>
                            <input id="edit_email" name="email" type="email" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Username</label>
                            <input id="edit_username" name="username" type="text" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Level/Role</label>
                            <select id="edit_role" name="role" required class="inp">
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id_level }}">{{ $lvl->level_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row-gap">
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Reset Password --}}
    <section class="charts-section" id="resetCardWrap" style="display:none;">
        <div class="chart-card">
            <div class="chart-header" style="margin-bottom:0;">
                <h3>Reset Password</h3>
                <div class="card-subtabs"><button type="button" id="btnCloseReset" class="btn-outline">Tutup</button></div>
            </div>
            <div class="chart-content" style="padding-top:16px; height:auto;">
                <form id="formReset" method="POST" action=""
                      data-reset-tpl="{{ route('users.reset', 0) }}">
                    @csrf
                    <div class="grid-2">
                        <div>
                            <label class="lbl">Password Baru</label>
                            <input id="reset_pw1" name="password" type="password" required class="inp">
                        </div>
                        <div>
                            <label class="lbl">Konfirmasi Password</label>
                            <input id="reset_pw2" name="password_confirmation" type="password" required class="inp">
                        </div>
                    </div>
                    <div class="row-gap">
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-key"></i> Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Reassign & Delete --}}
    <section class="charts-section" id="reassignCardWrap" style="display:none;">
        <div class="chart-card">
            <div class="chart-header" style="margin-bottom:0;">
                <h3>Alihkan & Hapus User</h3>
                <div class="card-subtabs"><button type="button" id="btnCloseReassign" class="btn-outline">Tutup</button></div>
            </div>
            <div class="chart-content" style="padding-top:16px; height:auto;">
                <form id="formReassign" method="POST" action=""
                    data-reassign-tpl="{{ route('users.reassign_delete', 0) }}">
                    @csrf
                    <div class="grid-1">
                        <div>
                            <label class="lbl">User Tujuan</label>
                            <select id="target_user_id" name="target_user_id" required class="inp">
                                <option value="">Pilih user tujuan</option>
                                @foreach($userOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->name }} ({{ $opt->email ?? $opt->username }})</option>
                                @endforeach
                            </select>
                            <small class="help">Semua budget yang dibuat oleh user ini akan dipindahkan ke user tujuan di atas.</small>
                        </div>
                    </div>
                    <div class="row-gap">
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-people-arrows"></i> Alihkan & Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- List Users --}}
    <section class="table-section">
        <div class="table-header">
            <h3>Daftar User</h3>
            <div class="table-controls">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari nama/email/username/role" id="searchInput" value="{{ $q }}">
                </div>
                <a href="{{ route('users.index', array_filter(['q' => $q])) }}" class="btn-filter" title="Refresh">
                    <i class="fas fa-rotate-right"></i>
                </a>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="userTable">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th style="width:160px;">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr data-id="{{ $u->id }}"
                        data-name="{{ $u->name }}"
                        data-email="{{ $u->email }}"
                        data-username="{{ $u->username }}"
                        data-role_id="{{ $u->user_level_id }}">
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->role ?? '—' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-action btn-reset" title="Reset Password"><i class="fa-solid fa-key"></i></button>
                                <button class="btn-action btn-reassign" title="Alihkan & Hapus"><i class="fa-solid fa-people-arrows"></i></button>
                                <form method="POST" action="{{ route('users.destroy', $u->id) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Hapus"
                                            onclick="return confirm('Hapus user {{ $u->name }}? (Pastikan tidak ada relasi lagi)')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada user</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="pager-wrap">
                <div class="pager-left">
                    @if($users->onFirstPage())
                        <span class="pager btn disabled">Previous</span>
                    @else
                        <a class="pager btn" href="{{ $users->previousPageUrl() }}&q={{ urlencode($q) }}">Previous</a>
                    @endif
                </div>
                <div class="pager-center">
                    @for($i = 1; $i <= $users->lastPage(); $i++)
                        @if($i == $users->currentPage())
                            <span class="page-dot active">{{ $i }}</span>
                        @elseif($i == 1 || $i == $users->lastPage() || abs($i - $users->currentPage()) <= 1)
                            <a class="page-dot" href="{{ $users->url($i) }}&q={{ urlencode($q) }}">{{ $i }}</a>
                        @elseif($i == 2 && $users->currentPage() > 3)
                            <span class="page-ellipsis">…</span>
                        @elseif($i == $users->lastPage()-1 && $users->currentPage() < $users->lastPage()-2)
                            <span class="page-ellipsis">…</span>
                        @endif
                    @endfor
                </div>
                <div class="pager-right">
                    @if($users->hasMorePages())
                        <a class="pager btn" href="{{ $users->nextPageUrl() }}&q={{ urlencode($q) }}">Next</a>
                    @else
                        <span class="pager btn disabled">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/users.js') }}" defer></script>
@endpush
