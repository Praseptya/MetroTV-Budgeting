<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    /** Halaman Dashboard (angka default: bulan berjalan agar ada baseline awal) */
    public function index()
    {
        // ---- Base tanpa GROUP BY (pakai budgets.status) ----
        $base = DB::table('budgets as b')
            ->leftJoin('templates as t', 't.id_template', '=', 'b.template_id')
            ->leftJoin('users as u', 'u.id_user', '=', 'b.created_by')
            ->selectRaw("
                b.id_budget,
                COALESCE(b.budget_name, t.name, '(Tanpa Nama)') AS budget_name,
                COALESCE(u.name, '—')                          AS creator_name,
                b.created_at                                   AS created_at,
                (
                SELECT COALESCE(SUM(bi.amount), 0)
                FROM budget_items bi
                WHERE bi.budget_id = b.id_budget
                )                                              AS total_budget,
                COALESCE(b.status, 'Pending')                  AS latest_status
            ");

        // ====== STAT CARDS ======
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        $totalBudgets = DB::table('budgets')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->count();

        $approvedBudgets = DB::query()->fromSub($base, 'x')
            ->whereRaw("x.latest_status = 'Approved'")
            ->count();

        $pendingApprovals = DB::query()->fromSub($base, 'x')
            ->whereRaw("x.latest_status = 'Pending'")
            ->count();

        // growth sederhana
        $prevStart = (clone $startMonth)->subMonth();
        $prevEnd   = (clone $endMonth)->subMonth();
        $prevTotal = DB::table('budgets')->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $budgetChange = $prevTotal > 0 ? round((($totalBudgets - $prevTotal) / $prevTotal) * 100) : 0;

        $prevApproved = DB::query()->fromSub($base, 'p')
            ->where('p.created_at', '<', Carbon::now()->subDays(30))
            ->whereRaw("p.latest_status = 'Approved'")
            ->count();

        $prevPending = DB::query()->fromSub($base, 'p')
            ->where('p.created_at', '<', Carbon::now()->subDays(30))
            ->whereRaw("p.latest_status = 'Pending'")
            ->count();

        $approvedChange = $prevApproved > 0 ? round((($approvedBudgets - $prevApproved) / $prevApproved) * 100) : 0;
        $pendingChange  = $prevPending  > 0 ? round((($pendingApprovals - $prevPending) / $prevPending) * 100) : 0;

        // ====== Recent table ======
        $recentBudgets = DB::query()->fromSub($base, 'x')
            ->orderByDesc('x.created_at')
            ->limit(10)
            ->get();

        // ====== Seed chart (12 bulan) ======
        $labels = [];
        $budgetSeries = [];
        $approvedSeries = [];
        for ($i = 11; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd   = Carbon::now()->subMonths($i)->endOfMonth();

            $labels[] = $mStart->isoFormat('MMM YY');
            $budgetSeries[] = DB::table('budgets')->whereBetween('created_at', [$mStart, $mEnd])->count();

            $approvedSeries[] = DB::query()->fromSub($base, 'x')
                ->whereBetween('x.created_at', [$mStart, $mEnd])
                ->whereRaw("x.latest_status = 'Approved'")
                ->count();
        }

        return view('dashboard', compact(
            'totalBudgets','budgetChange','approvedBudgets','approvedChange',
            'pendingApprovals','pendingChange','recentBudgets'
        ))->with('__DASHBOARD__', [
            'labels'       => $labels,
            'budgetData'   => $budgetSeries,
            'approvalData' => $approvedSeries,
        ]);
    }

    /** AJAX: sesuaikan semua data dengan range chip yang dipilih */
    public function data(Request $request)
    {
        $range = $request->query('range', '12b'); // 12 bulan (default)
        $now   = Carbon::now();

        $labels = [];
        $budgetData = [];
        $approvalData = [];

        // Helper untuk total per budget (correlated sum)
        $sumExpr = DB::raw("(SELECT COALESCE(SUM(bi.amount),0) FROM budget_items bi WHERE bi.budget_id = b.id_budget)");

        // ===== Build series per-range =====
        switch ($range) {
            case '30h': // 30 hari terakhir (per-hari)
            {
                for ($i = 29; $i >= 0; $i--) {
                    $start = (clone $now)->subDays($i)->startOfDay();
                    $end   = (clone $now)->subDays($i)->endOfDay();

                    $labels[]     = $start->isoFormat('D MMM');
                    $budgetData[] = DB::table('budgets')->whereBetween('created_at', [$start, $end])->count();
                    $approvalData[] = DB::table('budgets')
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'Approved')
                        ->count();
                }
                $periodStart = (clone $now)->subDays(29)->startOfDay();
                $periodEnd   = (clone $now)->endOfDay();
                break;
            }

            case '7h': // 7 hari (per-hari)
            {
                for ($i = 6; $i >= 0; $i--) {
                    $start = (clone $now)->subDays($i)->startOfDay();
                    $end   = (clone $now)->subDays($i)->endOfDay();

                    $labels[]     = $start->isoFormat('ddd'); // Sen, Sel, ...
                    $budgetData[] = DB::table('budgets')->whereBetween('created_at', [$start, $end])->count();
                    $approvalData[] = DB::table('budgets')
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'Approved')
                        ->count();
                }
                $periodStart = (clone $now)->subDays(6)->startOfDay();
                $periodEnd   = (clone $now)->endOfDay();
                break;
            }

            case '24j': // 24 jam terakhir (per-jam)
            {
                for ($i = 23; $i >= 0; $i--) {
                    $start = (clone $now)->subHours($i)->startOfHour();
                    $end   = (clone $now)->subHours($i)->endOfHour();

                    $labels[]     = $start->format('H:00');
                    $budgetData[] = DB::table('budgets')->whereBetween('created_at', [$start, $end])->count();
                    $approvalData[] = DB::table('budgets')
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'Approved')
                        ->count();
                }
                $periodStart = (clone $now)->subHours(23)->startOfHour();
                $periodEnd   = (clone $now)->endOfHour();
                break;
            }

            case '12b': // 12 bulan terakhir (per-bulan) — default
            default:
            {
                for ($i = 11; $i >= 0; $i--) {
                    $start = (clone $now)->subMonths($i)->startOfMonth();
                    $end   = (clone $now)->subMonths($i)->endOfMonth();

                    $labels[]     = $start->isoFormat('MMM YY');
                    $budgetData[] = DB::table('budgets')->whereBetween('created_at', [$start, $end])->count();
                    $approvalData[] = DB::table('budgets')
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'Approved')
                        ->count();
                }
                $periodStart = (clone $now)->subMonths(11)->startOfMonth();
                $periodEnd   = (clone $now)->endOfMonth();
                break;
            }
        }

        // ===== Stats untuk 3 kartu =====
        $total_pengajuan = DB::table('budgets')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $disetujui = DB::table('budgets')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->where('status', 'Approved')
            ->count();

        $menunggu = DB::table('budgets')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->where('status', 'Pending')
            ->count();

        // Growth sederhana (bandingkan dengan periode sebelumnya yang sama panjang)
        $diffDays = $periodStart->diffInDays($periodEnd) + 1;
        $prevStart = (clone $periodStart)->subDays($diffDays);
        $prevEnd   = (clone $periodEnd)->subDays($diffDays);

        $prev_total = DB::table('budgets')->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prev_appr  = DB::table('budgets')->whereBetween('created_at', [$prevStart, $prevEnd])->where('status','Approved')->count();
        $prev_wait  = DB::table('budgets')->whereBetween('created_at', [$prevStart, $prevEnd])->where('status','Pending')->count();

        $growth_total   = $prev_total > 0 ? round((($total_pengajuan - $prev_total) / $prev_total) * 100) : 0;
        $growth_approve = $prev_appr  > 0 ? round((($disetujui - $prev_appr) / $prev_appr) * 100) : 0;
        $growth_wait    = $prev_wait  > 0 ? round((($menunggu - $prev_wait) / $prev_wait) * 100) : 0;

        // ===== Rows tabel ringkas (10 terbaru di periode) =====
        $rows = DB::table('budgets as b')
            ->leftJoin('templates as t', 't.id_template', '=', 'b.template_id')
            ->leftJoin('users as u', 'u.id_user', '=', 'b.created_by')
            ->whereBetween('b.created_at', [$periodStart, $periodEnd])
            ->orderByDesc('b.created_at')
            ->limit(10)
            ->get([
                DB::raw('COALESCE(b.budget_name, t.name, "(Tanpa Nama)") as template'),
                DB::raw('COALESCE(u.name, "—") as dibuat_oleh'),
                DB::raw('DATE_FORMAT(b.created_at, "%d %b %Y") as tgl_buat'),
                DB::raw('(SELECT COALESCE(SUM(bi.amount),0) FROM budget_items bi WHERE bi.budget_id = b.id_budget) as total_amount'),
                DB::raw('COALESCE(b.status, "Pending") as latest_status'),
            ])
            ->map(function ($r) {
                // Nominal format
                $r->total_budget = 'Rp' . number_format((int)$r->total_amount, 0, ',', '.');
                unset($r->total_amount);

                // Label status id-ID
                $r->status = match ($r->latest_status) {
                    'Approved' => 'Approve',
                    'Rejected' => 'Ditolak',
                    'SendBack' => 'Revisi',
                    default    => 'Pending'
                };
                unset($r->latest_status);
                return $r;
            });

        return response()->json([
            'labels'       => $labels,
            'budgetData'   => $budgetData,
            'approvalData' => $approvalData,
            'stats'        => [
                'total_pengajuan' => $total_pengajuan,
                'disetujui'       => $disetujui,
                'menunggu'        => $menunggu,
                'growth_total'    => $growth_total,
                'growth_approve'  => $growth_approve,
                'growth_wait'     => $growth_wait,
            ],
            'rows'         => $rows,
        ]);
    }

    /** Export CSV */
    public function export(Request $request)
    {
        [$periodStart, $periodEnd] = $this->resolveRange($request->query('range', '12b'));

        $rows = $this->buildReportQuery($request, $periodStart, $periodEnd)->get();

        $filename = 'laporan-budget-' . now()->format('Ymd_His') . '.xls';
        return response()
            ->view('dashboard.report_export', [
                'rows'        => $rows,
                'periodStart' => $periodStart,
                'periodEnd'   => $periodEnd,
            ])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /** Partial rows buat filter tanggal dari tombol kalender */
    public function tableData(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->get('start'))->startOfDay() : null;
        $end   = $request->filled('end')   ? Carbon::parse($request->get('end'))->endOfDay()   : null;

        $q = Budget::query()
            ->with([
                'template',
                'approvals' => fn($qr) => $qr->latest(),
            ])
            ->latest('created_at');

        if ($start) $q->where('created_at', '>=', $start);
        if ($end)   $q->where('created_at', '<=', $end);

        $budgets = $q->limit(100)->get();

        return view('dashboard._rows', compact('budgets'));
    }

    // ==========================
    // Helpers
    // ==========================

    /** Latest approval per budget (pakai approved_at) */
    private function latestApprovalSub()
    {
        return DB::table('budget_approvals as a')
            ->join(
                DB::raw('(SELECT budget_id, MAX(approved_at) AS max_approved_at FROM budget_approvals GROUP BY budget_id) last'),
                function ($join) {
                    $join->on('last.budget_id', '=', 'a.budget_id')
                         ->on('last.max_approved_at', '=', 'a.approved_at');
                }
            )
            ->select('a.budget_id', 'a.status', 'a.approved_at');
    }

    /** Persentase perubahan (prev -> curr) */
    private function pctChange(int $prev, int $curr): float
    {
        if ($prev === 0) return $curr > 0 ? 100.0 : 0.0;
        return round((($curr - $prev) / $prev) * 100, 1);
    }

    /** Rolling N bulan berakhir di $now */
    private function rollingMonths(int $n, Carbon $now): array
    {
        $start = $now->copy()->startOfMonth()->subMonths($n - 1);
        $keys = [];
        for ($i = 0; $i < $n; $i++) $keys[] = $start->copy()->addMonths($i)->format('Y-m');
        return ['from' => $start, 'keys' => $keys];
    }

    /**
     * Window untuk setiap range:
     * - 12b  : 12 bulan terakhir
     * - 30h  : 30 hari terakhir
     * - 7h   : 7 hari terakhir
     * - 24j  : 24 jam terakhir
     */
    private function getRangeWindows(string $range, Carbon $now): array
    {
        if ($range === '12b') {
            $from = $now->copy()->startOfMonth()->subMonths(11);
            $to   = $now->copy()->endOfMonth();
            $prev_from = $from->copy()->subMonths(12);
            $prev_to   = $from->copy()->subSecond();
        } elseif ($range === '30h') {
            $from = $now->copy()->subDays(29)->startOfDay();
            $to   = $now->copy()->endOfDay();
            $prev_from = $from->copy()->subDays(30);
            $prev_to   = $from->copy()->subSecond();
        } elseif ($range === '7h') {
            $from = $now->copy()->subDays(6)->startOfDay();
            $to   = $now->copy()->endOfDay();
            $prev_from = $from->copy()->subDays(7);
            $prev_to   = $from->copy()->subSecond();
        } else { // 24j
            $from = $now->copy()->subHours(23)->startOfHour();
            $to   = $now->copy()->endOfHour();
            $prev_from = $from->copy()->subHours(24);
            $prev_to   = $from->copy()->subSecond();
        }

        return compact('from','to','prev_from','prev_to');
    }

    /** Total amount per budget dari budget_items.amount */
    private function sumBudgetItems(array $budgetIds): array
    {
        if (empty($budgetIds)) return [];
        $rows = DB::table('budget_items')
            ->select('budget_id', DB::raw('SUM(amount) AS total_amount'))
            ->whereIn('budget_id', $budgetIds)
            ->groupBy('budget_id')
            ->pluck('total_amount', 'budget_id');

        return $rows ? $rows->toArray() : [];
    }

    /** Format Rupiah */
    private function formatRupiah($angka): string
    {
        return 'Rp' . number_format((float)$angka, 0, ',', '.');
    }

    private function resolveRange(string $range): array
    {
        $now = Carbon::now();
        switch ($range) {
            case '30h': return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            case '7h' : return [$now->copy()->subDays(6)->startOfDay(),  $now->copy()->endOfDay()];
            case '24j': return [$now->copy()->subHours(23)->startOfHour(),$now->copy()->endOfHour()];
            case '12b':
            default   : return [$now->copy()->subMonths(11)->startOfMonth(),$now->copy()->endOfMonth()];
        }
    }

    private function buildReportQuery(Request $request, $periodStart, $periodEnd)
    {
        $q      = trim((string)$request->query('q', ''));
        $status = $request->query('status', 'all');
        $dept   = trim((string)$request->query('dept', ''));

        return DB::table('budgets as b')
            ->leftJoin('templates as t', 't.id_template', '=', 'b.template_id')
            ->leftJoin('users as u', 'u.id_user', '=', 'b.created_by')
            ->leftJoin('users as pic', 'pic.id_user', '=', 't.pic_user_id')
            ->whereBetween('b.created_at', [$periodStart, $periodEnd])
            ->when($status !== 'all', fn($w) => $w->where('b.status', $status))
            ->when($dept !== '', fn($w) => $w->where('b.dept', 'like', "%{$dept}%"))
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('b.budget_name', 'like', "%{$q}%")
                    ->orWhere('t.name', 'like', "%{$q}%")
                    ->orWhere('b.description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('b.created_at')
            ->select([
                'b.id_budget',
                DB::raw('COALESCE(b.budget_name, t.name, "(Tanpa Nama)") as budget_name'),
                DB::raw('COALESCE(b.description, "") as description'),
                DB::raw('COALESCE(b.dept, "") as dept'),
                'b.periode_from',
                'b.periode_to',
                DB::raw('COALESCE(t.name, "-") as template_name'),
                DB::raw('COALESCE(pic.name, "—") as pic_name'),
                DB::raw('COALESCE(u.name, "—") as creator_name'),
                DB::raw('COALESCE(b.status, "Pending") as status'),
                'b.created_at',
                DB::raw('(SELECT COALESCE(SUM(bi.amount),0) FROM budget_items bi WHERE bi.budget_id = b.id_budget) as total_amount'),
            ]);
    }

    public function report(Request $request)
    {
        [$periodStart, $periodEnd] = $this->resolveRange($request->query('range', '12b'));
        $rows = $this->buildReportQuery($request, $periodStart, $periodEnd)
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.report', [
            'rows'        => $rows,
            'periodStart' => $periodStart,
            'periodEnd'   => $periodEnd,
            'filters'     => [
                'q'      => $request->query('q', ''),
                'status' => $request->query('status', 'all'),
                'dept'   => $request->query('dept', ''),
                'range'  => $request->query('range', '12b'),
            ],
        ]);
    }

}
