<?php
// app/Http/Controllers/BudgetController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    /** Form: Buat Budget Baru (tanpa tabel item) */
    public function create()
    {
        $templates = DB::table('templates')
            ->select('id_template','name','description','category')
            ->orderBy('name')
            ->get();

        $q = DB::table('budgets as b')
            ->leftJoin('budget_items as bi', 'bi.budget_id', '=', 'b.id_budget')
            ->leftJoin('templates as t', 't.id_template', '=', 'b.template_id')
            ->leftJoin('users as creator', 'creator.id_user', '=', 'b.created_by')
            ->selectRaw('
                b.id_budget,
                COALESCE(b.budget_name, "(Tanpa Nama)") as budget_name,
                COALESCE(creator.name, "—")             as created_by_name,
                COALESCE(b.dept, "")                    as dept_name,
                b.periode_from,
                b.periode_to,
                COALESCE(b.status, "Pending")           as status,
                COALESCE(b.description, "")             as description,
                COALESCE(SUM(bi.amount), 0)             as grand_total
            ')
            ->groupBy(
                'b.id_budget',
                'b.budget_name',
                'created_by_name',
                'b.dept',
                'b.periode_from',
                'b.periode_to',
                'b.status',
                'b.description'
            );

        if (Schema::hasColumn('budgets', 'created_at')) {
            $q->orderByDesc('b.created_at');
        } else {
            $q->orderByDesc('b.id_budget');
        }

        $budgets = $q->get()->map(function ($r) {
            $from = $r->periode_from ? \Carbon\Carbon::parse($r->periode_from) : null;
            $to   = $r->periode_to   ? \Carbon\Carbon::parse($r->periode_to)   : null;

            if ($from && $to) {
                if ($from->isSameDay($to))       $r->periode_fmt = $from->isoFormat('D MMM');
                elseif ($from->isSameMonth($to)) $r->periode_fmt = $from->isoFormat('D').'–'.$to->isoFormat('D MMM');
                elseif ($from->isSameYear($to))  $r->periode_fmt = $from->isoFormat('D MMM').' – '.$to->isoFormat('D MMM');
                else                              $r->periode_fmt = $from->isoFormat('D MMM YYYY').' – '.$to->isoFormat('D MMM YYYY');
            } elseif ($from) {
                $r->periode_fmt = $from->isoFormat('D MMM');
            } elseif ($to) {
                $r->periode_fmt = $to->isoFormat('D MMM');
            } else {
                $r->periode_fmt = '—';
            }
            return $r;
        });

        return view('budgets.create', [
            'templates' => $templates,
            'budgets'   => $budgets,
        ]);
    }


    /** Simpan budget baru: status default Pending, copy items dari template */
    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id'   => 'required|integer|exists:templates,id_template',
            'budget_name'   => 'required|string|max:150',
            'dept'          => 'required|string|max:100',
            'periode_from'  => 'required|date',
            'periode_to'    => 'required|date|after_or_equal:periode_from',
            'description'   => 'required|string|max:255',
        ]);

        // tentukan nama kolom yang tersedia di DB
        $deptCol = Schema::hasColumn('budgets','dept')         ? 'dept'
                : (Schema::hasColumn('budgets','department')   ? 'department'
                : (Schema::hasColumn('budgets','departement')  ? 'departement' : null));

        $fromCol = Schema::hasColumn('budgets','periode_from') ? 'periode_from'
                : (Schema::hasColumn('budgets','period_from')  ? 'period_from'
                : (Schema::hasColumn('budgets','start_date')   ? 'start_date' : null));

        $toCol   = Schema::hasColumn('budgets','periode_to')   ? 'periode_to'
                : (Schema::hasColumn('budgets','period_to')    ? 'period_to'
                : (Schema::hasColumn('budgets','end_date')     ? 'end_date' : null));

        $payload = [
            'budget_name'  => $data['budget_name'],
            'template_id'  => (int)$data['template_id'],
            'status'       => Schema::hasColumn('budgets','status') ? 'Pending' : null,
            'description'  => (string)$data['description'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        if ($deptCol) { $payload[$deptCol] = (string)$data['dept']; }
        if ($fromCol) { $payload[$fromCol] = $data['periode_from']; }
        if ($toCol)   { $payload[$toCol]   = $data['periode_to']; }

        if (Schema::hasColumn('budgets','created_by')) {
            $payload['created_by'] = optional(Auth::user())->id_user ?? Auth::id();
        }

        // buang key yang nilainya null agar tidak error di driver lama
        $payload = array_filter($payload, fn($v) => !is_null($v));

        $budgetId = DB::table('budgets')->insertGetId($payload, 'id_budget');

        // === copy items dari template_items → budget_items (sesuai patch kamu sebelumnya) ===
        $tplItems = DB::table('template_items as ti')
            ->leftJoin('master_items as mi','mi.id_item','=','ti.item_id')
            ->select([
                'ti.item_id','ti.item_name','ti.qty','ti.unit','ti.unit_price','ti.short_desc',
                'mi.item_name as mi_item_name','mi.description as mi_description',
            ])
            ->where('ti.template_id', $data['template_id'])
            ->get();

        foreach ($tplItems as $it) {
            $name  = $it->item_name ?: ($it->mi_item_name ?? '');
            $desc  = $it->short_desc ?: ($it->mi_description ?? '');
            $price = (float)($it->unit_price ?? 0);
            $qty   = (int)($it->qty ?? 0);
            $amt   = $qty * $price;

            DB::table('budget_items')->insert([
                'budget_id'     => $budgetId,
                'item_id'       => (int)$it->item_id,
                'item_name'     => (string)$name,
                'short_desc'    => (string)$desc,
                'qty'           => $qty,
                'unit'          => (string)($it->unit ?? ''),
                'unit_price'    => $price,
                'amount'        => $amt,
                // opsional isi kolom lama jika masih ada di DB kamu
                'top_price'     => Schema::hasColumn('budget_items','top_price') ? $price : null,
                'bottom_price'  => Schema::hasColumn('budget_items','bottom_price') ? $price : null,
                'created_at'    => Schema::hasColumn('budget_items','created_at') ? now() : null,
                'updated_at'    => Schema::hasColumn('budget_items','updated_at') ? now() : null,
            ]);
        }

        return redirect()->route('budgets.show', $budgetId)
            ->with('success', 'Budget baru berhasil dibuat.');
    }


    /** Detail budget (tampilkan tabel item setelah dibuat) */
    public function show($id)
    {
        $budget = DB::table('budgets as b')
            ->leftJoin('templates as t', 't.id_template', '=', 'b.template_id')
            ->leftJoin('event_programs as ep', 'ep.id_event_program', '=', 't.event_program_id')
            ->leftJoin('users as uCreator', 'uCreator.id_user', '=', 'b.created_by')
            ->leftJoin('users as uProg', 'uProg.id_user', '=', 'ep.pic_user_id')
            ->leftJoin('users as uTpl',  'uTpl.id_user',  '=', 't.pic_user_id')
            ->selectRaw('
                b.*,
                t.name as template_name,
                uCreator.name as created_by_name,
                COALESCE(ep.name, "")                         as program_name,
                COALESCE(ep.category, t.category, "Off Air")  as program_category,
                COALESCE(uProg.name, uTpl.name, "—")          as program_pic_name
            ')
            ->where('b.id_budget', $id)
            ->first();

        if (!$budget) {
            return redirect()->route('budgets.create')->with('error','Budget tidak ditemukan.');
        }

        // departemen + periode (biarkan seperti versi kamu)
        $deptName = $budget->dept ?? ($budget->department ?? ($budget->departement ?? null));
        $rawFrom  = $budget->periode_from ?? ($budget->period_from ?? ($budget->start_date ?? null));
        $rawTo    = $budget->periode_to   ?? ($budget->period_to   ?? ($budget->end_date   ?? null));
        $rawFrom  = ($rawFrom === '0000-00-00' || $rawFrom === '') ? null : $rawFrom;
        $rawTo    = ($rawTo   === '0000-00-00' || $rawTo   === '') ? null : $rawTo;

        $periodeFmt = '—';
        try {
            $from = $rawFrom ? Carbon::parse($rawFrom) : null;
            $to   = $rawTo   ? Carbon::parse($rawTo)   : null;
            if ($from && $to) {
                if ($from->isSameDay($to))       $periodeFmt = $from->isoFormat('D MMM YYYY');
                elseif ($from->isSameMonth($to)) $periodeFmt = $from->isoFormat('D').'–'.$to->isoFormat('D MMM YYYY');
                elseif ($from->isSameYear($to))  $periodeFmt = $from->isoFormat('D MMM').' – '.$to->isoFormat('D MMM YYYY');
                else                              $periodeFmt = $from->isoFormat('D MMM YYYY').' – '.$to->isoFormat('D MMM YYYY');
            } elseif ($from) {
                $periodeFmt = $from->isoFormat('D MMM YYYY');
            } elseif ($to) {
                $periodeFmt = $to->isoFormat('D MMM YYYY');
            }
        } catch (\Throwable $e) {}

        // format tanggal dibuat untuk ditampilkan
        $createdAtFmt = $budget->created_at ? Carbon::parse($budget->created_at)->isoFormat('D MMM YYYY, HH.mm') : '—';

        $items = DB::table('budget_items')
            ->where('budget_id', $id)
            ->orderBy('id_budget_item')
            ->get(['item_id','item_name','short_desc','qty','unit','unit_price','amount']);

        $grandTotal = $items->sum('amount');

        return view('budgets.show', [
            'budget'        => $budget,
            'items'         => $items,
            'grandTotal'    => $grandTotal,
            'deptName'      => $deptName,
            'periodeFmt'    => $periodeFmt,
            'createdAtFmt'  => $createdAtFmt,   // 🔹 kirim ke blade
        ]);
    }


    /** AJAX: detail template (grand total & description) untuk autofill di create */
    public function templateDetail($id)
    {
        $tpl = DB::table('templates as t')
            ->leftJoin('event_programs as ep', 'ep.id_event_program', '=', 't.event_program_id')
            ->leftJoin('users as uProg', 'uProg.id_user', '=', 'ep.pic_user_id')
            ->leftJoin('users as uTpl',  'uTpl.id_user',  '=', 't.pic_user_id')
            ->where('t.id_template', $id)
            ->first([
                't.id_template',
                't.name',
                DB::raw('COALESCE(ep.name, "")                        as program_name'),
                DB::raw('COALESCE(ep.category, t.category, "Off Air") as program_category'),
                DB::raw('COALESCE(uProg.name, uTpl.name, "—")         as program_pic_name'),
                DB::raw('COALESCE(t.description, ep.description, "")  as description'),
            ]);

        if (!$tpl) {
            return response()->json(['ok' => false, 'message' => 'Template not found'], 404);
        }

        $grand = (float) DB::table('template_items')
            ->where('template_id', $id)
            ->selectRaw('COALESCE(SUM(qty * unit_price),0) as gt')
            ->value('gt');

        return response()->json([
            'ok'   => true,
            'data' => [
                'id'               => $tpl->id_template,
                'name'             => $tpl->name,
                'program_name'     => $tpl->program_name,
                'program_category' => $tpl->program_category,
                'program_pic_name' => $tpl->program_pic_name,
                'description'      => $tpl->description,
                'grand_total'      => $grand,
            ],
        ]);
    }

    /** AJAX: cari master items (untuk modal tambah item) */
    public function itemSearch(Request $request)
    {
        $q = trim($request->get('q',''));

        $base = DB::table('master_items as i')
            ->leftJoin('units as u', 'u.id_unit', '=', 'i.unit_id')
            ->orderBy('i.item_name')
            ->limit(20);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('i.item_name', 'like', "%{$q}%")
                  ->orWhere('i.description', 'like', "%{$q}%");
            });
        }

        $rows = $base->get([
            DB::raw('i.id_item as id'),
            DB::raw('i.item_name as item_name'),
            'i.description',
            'i.bottom_price',
            'i.top_price',
            DB::raw('COALESCE(u.unit_name, "") as unit_name'),
        ]);

        return response()->json(['items' => $rows, 'data' => $rows]);
    }

    public function storeItem(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:master_items,id_item',
            'qty'     => 'nullable|integer|min:1'
        ]);

        $qty = max(1, (int)($request->qty ?? 1));

        // ambil data master item + unit + harga
        $mi = DB::table('master_items as m')
            ->leftJoin('units as u', 'u.id_unit', '=', 'm.unit_id')
            ->where('m.id_item', $request->item_id)
            ->first([
                'm.id_item',
                'm.item_name',
                'm.description',
                'm.top_price',
                'm.bottom_price',
                DB::raw('COALESCE(u.unit_name, "") as unit_name'),
            ]);

        if (!$mi) {
            return back()->with('error','Master item tidak ditemukan.');
        }

        // tentukan unit price (pakai top_price jika ada, else bottom)
        $unitPrice = (float)($mi->top_price ?? 0);
        if ($unitPrice <= 0) $unitPrice = (float)($mi->bottom_price ?? 0);

        // cek apakah item ini sudah ada di budget → merge qty
        $exist = DB::table('budget_items')
            ->where('budget_id', $id)
            ->where('item_id',   $mi->id_item)
            ->first(['id_budget_item','qty','unit_price']);

        // deteksi kolom created_at/updated_at agar tidak error di schema lama
        $hasCreated = Schema::hasColumn('budget_items', 'created_at');
        $hasUpdated = Schema::hasColumn('budget_items', 'updated_at');

        if ($exist) {
            $newQty = (int)$exist->qty + $qty;
            $payload = [
                'qty'   => $newQty,
                'unit'  => (string)($mi->unit_name ?? ''),
                'amount'=> (float)$unitPrice * $newQty,
            ];
            if ($hasUpdated) $payload['updated_at'] = now();

            DB::table('budget_items')
                ->where('id_budget_item', $exist->id_budget_item)
                ->update($payload);
        } else {
            $payload = [
                'budget_id'  => (int)$id,
                'item_id'    => (int)$mi->id_item,
                'item_name'  => (string)$mi->item_name,
                'short_desc' => (string)($mi->description ?? ''),
                'qty'        => $qty,
                'unit'       => (string)($mi->unit_name ?? ''),
                'unit_price' => (float)$unitPrice,
                'amount'     => (float)$unitPrice * $qty,
                'top_price'    => (float)($mi->top_price ?? 0),
                'bottom_price' => (float)($mi->bottom_price ?? 0),
            ];
            if ($hasCreated) $payload['created_at'] = now();
            if ($hasUpdated) $payload['updated_at'] = now();

            DB::table('budget_items')->insert($payload);
        }

        // setelah tambah, balik ke edit & scroll ke bawah
        return redirect()
            ->route('budgets.edit', $id)
            ->with('success','Item ditambahkan.')
            ->with('scroll_bottom', true);
    }

    /** Hapus baris item pada budget */
    public function destroyItem($id, $rowId)
    {
        DB::table('budget_items')
            ->where('id_budget_item', $rowId)
            ->where('budget_id', $id)
            ->delete();

        return redirect()
            ->route('budgets.edit', $id)
            ->with('success','Item dihapus.')
            ->with('scroll_bottom', true);
    }

    /** AJAX: update qty (+/− atau set langsung) */
    public function updateItemQty(Request $request, $id, $rowId)
    {
        $data = $request->validate([
            'delta' => 'nullable|in:inc,dec',
            'qty'   => 'nullable|integer|min:1',
        ]);

        $row = DB::table('budget_items')
            ->where('id_budget_item', $rowId)
            ->where('budget_id', $id)
            ->first(['qty','unit_price']);

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'Row not found'], 404);
        }

        $newQty = (int)$row->qty;
        if (!empty($data['delta'])) {
            $newQty += ($data['delta'] === 'inc') ? 1 : -1;
        }
        if (isset($data['qty'])) {
            $newQty = (int)$data['qty'];
        }
        if ($newQty < 1) $newQty = 1;

        // deteksi kolom updated_at
        $hasUpdated = Schema::hasColumn('budget_items', 'updated_at');

        $payload = [
            'qty'    => $newQty,
            'amount' => (float)$row->unit_price * $newQty,
        ];
        if ($hasUpdated) $payload['updated_at'] = now();

        DB::table('budget_items')
            ->where('id_budget_item', $rowId)
            ->update($payload);

        $rowTotal = (int) $newQty * (float) $row->unit_price;

        $grand = (float) DB::table('budget_items')
            ->where('budget_id', $id)
            ->selectRaw('COALESCE(SUM(amount),0) as gt')
            ->value('gt');

        return response()->json([
            'ok'           => true,
            'qty'          => $newQty,
            'row_total'    => $rowTotal,
            'grand_total'  => $grand,
        ]);
    }


    public function edit($id)
    {
        $budget = DB::table('budgets as b')
            ->leftJoin('templates as t','t.id_template','=','b.template_id')
            ->leftJoin('users as u','u.id_user','=','t.pic_user_id')
            ->select(
                'b.*',
                't.id_template',
                't.name as template_name',
                't.category as template_category',
                'u.name as pic_name'
            )
            ->where('b.id_budget',$id)
            ->first();

        if(!$budget){
            return redirect()->route('budgets.create')->with('error','Budget tidak ditemukan.');
        }

        $templates = DB::table('templates')
            ->select('id_template','name','description','category')
            ->orderBy('name')
            ->get();

        // 🔧 Tambahan: ambil items untuk list di halaman edit
        $items = DB::table('budget_items')
            ->where('budget_id', $id)
            ->orderBy('id_budget_item')
            ->get(['id_budget_item','item_id','item_name','short_desc','qty','unit','unit_price','amount']);

        $periodeFrom = $budget && $budget->periode_from
            ? Carbon::parse($budget->periode_from)->format('Y-m-d')
            : '';

        $periodeTo = $budget && $budget->periode_to
            ? Carbon::parse($budget->periode_to)->format('Y-m-d')
            : '';

        return view('budgets.edit', [
            'budget'    => $budget,
            'templates' => $templates,
            'items'     => $items,
            'periodeFrom' => $periodeFrom,
            'periodeTo'   => $periodeTo,
        ]);
    }


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'template_id'   => 'required|integer|exists:templates,id_template',
            'budget_name'   => 'required|string|max:150',
            'dept'          => 'required|string|max:100',
            'periode_from'  => 'required|date',
            'periode_to'    => 'required|date|after_or_equal:periode_from',
            'description'   => 'required|string|max:255',
        ]);

        DB::table('budgets')->where('id_budget',$id)->update([
            'template_id'   => $data['template_id'],
            'budget_name'   => $data['budget_name'],
            'dept'          => $data['dept'],
            'periode_from'  => $data['periode_from'],
            'periode_to'    => $data['periode_to'],
            'description'   => $data['description'],
            'updated_at'    => now(),
        ]);

        return redirect()->route('budgets.show',$id)
            ->with('success','Budget berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::transaction(function() use ($id){
            DB::table('budget_items')->where('budget_id',$id)->delete();
            DB::table('budgets')->where('id_budget',$id)->delete();
        });

        return redirect()->route('budgets.create')->with('success','Budget berhasil dihapus.');
    }

}
