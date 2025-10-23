<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BudgetStatusNotification;
use Illuminate\Support\Facades\Notification;


class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $budgets = DB::table('budgets as b')
            ->leftJoin('users as u', 'u.id_user', '=', 'b.created_by')
            ->select(
                'b.id_budget',
                'b.budget_name',
                'u.name as created_by',
                'b.periode_from',
                'b.periode_to',
                'b.status',
                DB::raw('COALESCE(SUM(bi.amount), 0) as total')
            )
            ->leftJoin('budget_items as bi', 'bi.budget_id', '=', 'b.id_budget')
            ->groupBy('b.id_budget', 'b.budget_name', 'u.name', 'b.periode_from', 'b.periode_to', 'b.status')
            ->orderBy('b.created_at', 'desc')
            ->get();

        return view('approval.index', compact('budgets'));
    }

    public function approve($id)
    {
        $budget = DB::table('budgets')->where('id_budget', $id)->first();

        DB::table('budgets')->where('id_budget', $id)->update([
            'status' => 'Approved',
            'updated_at' => now(),
        ]);

        // Kirim notifikasi ke pembuat budget
        $creator = DB::table('users')->where('id_user', $budget->created_by)->first();
        if ($creator) {
            Notification::route('mail', $creator->email)
                ->notify(new BudgetStatusNotification($budget, 'Approved'));
        }

        return back()->with('success', 'Budget berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $budget = DB::table('budgets')->where('id_budget', $id)->first();
        $reason = $request->input('reason');

        DB::table('budgets')->where('id_budget', $id)->update([
            'status' => 'Rejected',
            'rejection_reason' => $reason,
            'updated_at' => now(),
        ]);

        // Kirim notifikasi ke pembuat budget
        $creator = DB::table('users')->where('id_user', $budget->created_by)->first();
        if ($creator) {
            Notification::route('mail', $creator->email)
                ->notify(new BudgetStatusNotification($budget, 'Rejected', $reason));
        }

        return back()->with('error', 'Budget ditolak.');
    }


}

