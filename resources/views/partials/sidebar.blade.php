@php
  // Ambil level user sekarang
  $levelName = auth()->check()
      ? \DB::table('user_levels')
          ->where('id_level', auth()->user()->user_level_id)
          ->value('level_name')
      : null;

  // Hitung jumlah budget Pending (untuk badge)
  $pendingCount = 0;
  if(in_array($levelName, ['Admin', 'Manager', 'Director'])) {
      $pendingCount = \DB::table('budgets')
          ->where('status', 'Pending')
          ->count();
  }
@endphp

<aside class="mtv-sidebar">
  {{-- Header --}}
  <div class="sb-head">
    <div class="logo">
      <span class="program">PROGRAM</span>
      <img class="logo-img" src="{{ asset('logo.png') }}" alt="MetroTV">
    </div>
  </div>

  {{-- Body --}}
  <div class="sb-body">
    <ul class="sb-list">

      {{-- Dashboard --}}
      <li class="sb-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
        <a href="{{ route('dashboard') }}" class="sb-link" aria-current="{{ request()->routeIs('dashboard') ? 'page':'false' }}">
          <img class="sb-ico-img" src="{{ asset('icon/grid-dashboard.png') }}" alt="">
          <span class="sb-text">Dashboard</span>
        </a>
      </li>

      {{-- MASTER DATA --}}
      @php
        $isMasterOpen = request()->routeIs('master.items.*')
                      || request()->routeIs('master.program.*')
                      || request()->routeIs('master.templates.*');
      @endphp
      <li class="sb-item sb-group {{ $isMasterOpen ? 'is-open' : '' }}" data-key="master">
        <button type="button" class="sb-link sb-toggle" aria-expanded="{{ $isMasterOpen ? 'true':'false' }}">
          <img class="sb-ico-img" src="{{ asset('icon/notes.png') }}" alt="">
          <span class="sb-text">Master Data</span>
          <img class="sb-caret" src="{{ asset('icon/caret.png') }}" alt="">
        </button>

        <ul class="sb-sub sb-subgrid" {{ $isMasterOpen ? '' : 'hidden' }}>
          <li class="sb-subitem {{ request()->routeIs('master.items.*') ? 'is-active' : '' }}">
            <a class="sb-sublink" href="{{ route('master.items.index') }}">
              <span>Master Item</span>
            </a>
          </li>
          <li class="sb-subitem {{ request()->routeIs('master.program.*') ? 'is-active' : '' }}">
            <a class="sb-sublink" href="{{ route('master.program.index') }}">
              <span>Master Program</span>
            </a>
          </li>
          <li class="sb-subitem {{ request()->routeIs('master.templates.*') ? 'is-active' : '' }}">
            <a class="sb-sublink" href="{{ route('master.templates.index') }}">
              <span>Master Template</span>
            </a>
          </li>
        </ul>
      </li>

      {{-- Buat Budget Baru --}}
      <li class="sb-item {{ request()->routeIs('budgets.create') ? 'is-active' : '' }}">
        <a href="{{ route('budgets.create') }}" class="sb-link">
          <img class="sb-ico-img" src="{{ asset('icon/notes-edit-add.png') }}" alt="">
          <span class="sb-text">Create New Budget</span>
        </a>
      </li>

      {{-- Approval Budget (hanya untuk atasan) --}}
      @if(in_array($levelName, ['Admin', 'Manager', 'Director']))
      <li class="sb-item {{ request()->routeIs('approval.*') ? 'is-active' : '' }}">
        <a href="{{ route('approval.index') }}" class="sb-link">
          <img class="sb-ico-img" src="{{ asset('icon/notes-check.png') }}" alt="">
          <span class="sb-text">
            Approval Budget
            @if($pendingCount > 0)
              <span class="badge-pending">{{ $pendingCount }}</span>
            @endif
          </span>
        </a>
      </li>
      @endif

      {{-- Manajemen User --}}
      @can('admin-only')
      <li class="sb-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
        <a href="{{ route('users.index') }}" class="sb-link">
          <img class="sb-ico-img" src="{{ asset('icon/user-circle.png') }}" alt="">
          <span class="sb-text">User Management</span>
        </a>
      </li>
      @endcan 
    </ul>
  </div>

  {{-- Footer --}}
  <div class="sb-foot">
    <a class="sb-footlink" href="{{ route('help.index') }}">
      <img class="sb-ico-img" src="{{ asset('icon/help.png') }}" alt="">
      <span>Help Center</span>
    </a>
    <a class="sb-footlink danger" href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <img class="sb-ico-img" src="{{ asset('icon/logout.png') }}" alt="">
      <span>Log Out</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
  </div>
</aside>
