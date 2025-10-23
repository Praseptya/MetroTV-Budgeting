<header class="header">
    <div class="header-left">
        <button id="btnToggleSidebar" class="btn-outline" style="display:none;margin-right:12px;">
            <i class="fa-solid fa-bars"></i>
        </button>
        @push('styles')
        <style>@media(max-width:768px){ #btnToggleSidebar{display:inline-flex;align-items:center;gap:8px;} }</style>
        @endpush
        <h1>{{ $title ?: '—' }}</h1>
    </div>
    <div class="header-right">
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff"
                 alt="User" class="user-avatar">
            <div class="user-details">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-email">{{ auth()->user()->email }}</span>
            </div>
        </div>
    </div>
</header>
