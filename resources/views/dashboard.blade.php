@extends('layouts.app')

@section('title', 'Dashboard - MetroTV Budgeting')
@section('page_title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('head')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" defer></script>
@endpush

@section('content')
    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">Total Pengajuan {{ now()->isoFormat('MMMM') }}</div>
                <div class="stat-number">{{ $totalBudgets }}</div>
                <div class="stat-subtitle">(Project)</div>
                <div class="stat-change {{ $budgetChange > 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $budgetChange > 0 ? 'up' : 'down' }}"></i>
                    <span>{{ abs($budgetChange) }}%</span>
                </div>
            </div>
            <div class="stat-chart"><canvas id="budgetChart" width="80" height="40"></canvas></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">Disetujui</div>
                <div class="stat-number">{{ $approvedBudgets }}</div>
                <div class="stat-subtitle">(Item)</div>
                <div class="stat-change {{ $approvedChange > 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $approvedChange > 0 ? 'up' : 'down' }}"></i>
                    <span>{{ abs($approvedChange) }}%</span>
                </div>
            </div>
            <div class="stat-chart"><canvas id="approvedChart" width="80" height="40"></canvas></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">Menunggu Persetujuan</div>
                <div class="stat-number">{{ $pendingApprovals }}</div>
                <div class="stat-subtitle">(Item)</div>
                <div class="stat-change {{ $pendingChange > 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $pendingChange > 0 ? 'up' : 'down' }}"></i>
                    <span>{{ abs($pendingChange) }}%</span>
                </div>
            </div>
            <div class="stat-chart"><canvas id="pendingChart" width="80" height="40"></canvas></div>
        </div>
    </div>

    {{-- Chart utama --}}
    <div class="charts-section">
        <div class="chart-card main-chart">
            <div class="chart-header">
                <h3>Laporan Pengajuan Anggaran</h3>
                <div class="card-subtabs">
                    <button type="button" class="chip active" data-range="12b">12 bulan</button>
                    <button type="button" class="chip" data-range="30h">30 hari</button>
                    <button type="button" class="chip" data-range="7h">7 hari</button>
                    <button type="button" class="chip" data-range="24j">24 jam</button>
                    <button
                        id="btnViewReport"
                        class="btn-outline"
                        data-report-url="{{ route('dashboard.report') }}"
                        data-export-url="{{ route('dashboard.export') }}"
                        data-data-url="{{ route('dashboard.data') }}"
                    >View Laporan</button>
                </div>
            </div>
            <div class="chart-content">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Budgets Table --}}
    <div class="table-section">
    <div class="table-header">
        <h3>Tabel Pengajuan Terbaru</h3>
        <div class="table-controls">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search" id="searchInput">
        </div>
        <button class="btn-export" id="btnCalendar"><i class="fas fa-calendar-alt"></i></button>
        <button class="btn-filter" id="btnFilter"><i class="fas fa-sliders-h"></i></button>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table" id="dashboardTable">
        <thead>
            <tr>
            <th>Budget <i class="fas fa-sort"></i></th>
            <th>Dibuat Oleh <i class="fas fa-sort"></i></th>
            <th>Tgl Buat <i class="fas fa-sort"></i></th>
            <th>Total Budget <i class="fas fa-sort"></i></th>
            <th>Status <i class="fas fa-sort"></i></th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentBudgets as $budget)
            @php
            $status = $budget->latest_status ?? 'Pending';
            $statusClass = match($status) {
                'Approved' => 'approved',
                'Rejected' => 'rejected',
                'SendBack', 'Revisi' => 'revision',
                default => 'pending'
            };
            $statusText = match($status) {
                'Approved' => 'Approve',
                'Rejected' => 'Ditolak',
                'SendBack', 'Revisi' => 'Revisi',
                default => 'Pending'
            };
            $totalNominal = (int)($budget->total_budget ?? 0);
            @endphp
            <tr>
            <td class="t-clip t-clip-md" title="{{ $budget->budget_name }}">{{ $budget->budget_name }}</td>
            <td>{{ $budget->creator_name ?? '—' }}</td>
            <td>{{ $budget->created_at ? \Carbon\Carbon::parse($budget->created_at)->format('d M Y') : '—' }}</td>
            <td>Rp{{ number_format($totalNominal, 0, ',', '.') }}</td>
            <td>
                <span class="status {{ $statusClass }}">
                <i class="fas fa-circle"></i>{{ $statusText }}
                </span>
            </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
            @endforelse

        </tbody>
        </table>
    </div>
    </div>

@endsection

@push('scripts')
    {{-- Seed data untuk chart (harus ada sebelum dashboard.js) --}}
    <script>
      window.__DASHBOARD__ = @json($__DASHBOARD__ ?? [
        'labels'       => [],
        'budgetData'   => [],
        'approvalData' => []
      ]);
    </script>

    {{-- Muat Chart.js dulu --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    {{-- Lalu script dashboard (boleh defer) --}}
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush
