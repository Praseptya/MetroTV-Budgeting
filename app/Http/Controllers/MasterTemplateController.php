<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MasterTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    /* =========================================================
     * Helpers
     * =======================================================*/

    private function unitColumns(): array
    {
        $idCol = Schema::hasColumn('units', 'id_unit') ? 'id_unit'
               : (Schema::hasColumn('units', 'id') ? 'id' : null);

        $nameCol = Schema::hasColumn('units', 'unit_name') ? 'unit_name'
                 : (Schema::hasColumn('units', 'name') ? 'name'
                 : (Schema::hasColumn('units', 'nama') ? 'nama' : null));

        return [$idCol, $nameCol];
    }

    private function userIdCol(): string
    {
        return Schema::hasColumn('users','id_user') ? 'id_user' : 'id';
    }

    private function userNameCol(): string
    {
        return Schema::hasColumn('users','name') ? 'name'
             : (Schema::hasColumn('users','nama') ? 'nama' : 'email');
    }

    private function fetchTemplateItems(int $templateId)
    {
        $q = DB::table('template_items as ti')
            ->leftJoin('master_items as i', 'i.id_item', '=', 'ti.item_id')
            ->where('ti.template_id', $templateId)
            ->orderBy('ti.id_template_item', 'asc');

        return $q->get([
            DB::raw('ti.id_template_item as row_id'),
            DB::raw('ti.item_id as item_id'),
            DB::raw('ti.item_name as item_name'),
            DB::raw('COALESCE(ti.short_desc, i.description, "") as description'),
            DB::raw('ti.unit as unit_name'),
            DB::raw('ti.qty as qty'),
            DB::raw('ti.unit_price as price'),
        ]);
    }

    /**
     * Daftar template untuk tabel List Template (index) — aman jika kolom 'status' TIDAK ada.
     */
    private function fetchTemplatesList()
    {
        $uidCol     = $this->userIdCol();     // biasanya: id_user
        $uname      = $this->userNameCol();   // biasanya: name
        $hasStatus  = Schema::hasColumn('templates','status');
        $statusExpr = $hasStatus ? 'COALESCE(t.status, \'Pending\')' : '\'Pending\'';

        return DB::table('templates as t')
            ->leftJoin('users as u', "u.$uidCol", '=', 't.pic_user_id')           // gunakan PIC sebagai "Dibuat Oleh"
            ->leftJoin('template_items as ti', 'ti.template_id', '=', 't.id_template')
            ->selectRaw("
                t.id_template,
                MIN(t.name) as template_name,
                MIN(COALESCE(u.$uname, '—')) as created_by_name,
                DATE(MIN(t.created_at)) as created_date,
                COALESCE(SUM(ti.qty * ti.unit_price), 0) as grand_total,
                MIN($statusExpr) as status,
                MIN(COALESCE(t.description, '')) as short_desc,
                MIN(t.category) as category
            ")
            ->groupBy('t.id_template')
            ->orderByDesc(DB::raw('MIN(t.created_at)'))
            ->get()
            ->map(function($r){
                $r->created_date_fmt = \Carbon\Carbon::parse($r->created_date)->isoFormat('D MMM');
                return $r;
            });
    }


    /* =========================================================
     * Pages
     * =======================================================*/

    /** INDEX: Form create + List Template */
    public function index()
    {
        $templates = $this->fetchTemplatesList();

        $users = DB::table('users')
            ->select('id_user', 'name')
            ->orderBy('name')
            ->get();

        $programs = DB::table('event_programs')
            ->select('id_event_program', 'name', 'category', 'description', 'pic_user_id')
            ->orderBy('name')
            ->get();

        return view('master.templates.create', [
            'template'      => null,
            'templateItems' => collect(),
            'users'         => $users,
            'programs'      => $programs,
            'grandTotal'    => 0,
            'openAddItem'   => false,
            'templates'     => $templates,
            'readonly'      => false,
        ]);
    }

    /** Form create → redirect ke index agar satu pintu */
    public function create(Request $request)
    {
        return redirect()->route('master.templates.index');
    }

    /** Simpan (buat / update) — flow setelah create tetap ke edit */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_template'      => 'nullable|integer',
            'name'             => 'required|string|max:150',
            'event_program_id' => 'required|integer|exists:event_programs,id_event_program',
            'pic_user_id'      => 'nullable|integer|exists:users,id_user',
            'category'         => 'required|string|max:20',   // On Air / Off Air
            'description'      => 'nullable|string|max:255',
        ]);

        // Validasi Event/Program
        $programId = $request->input('event_program_id');
        if (!$programId || !DB::table('event_programs')->where('id_event_program', $programId)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Event/Program tidak valid atau belum dipilih dari daftar.');
        }

        // Validasi Penanggung Jawab
        $picId = $request->input('pic_user_id');
        if (!$picId || !DB::table('users')->where('id_user', $picId)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Penanggung jawab tidak valid atau belum dipilih dari daftar.');
        }

        $cat = in_array($data['category'], ['On Air', 'Off Air'], true) ? $data['category'] : 'Off Air';

        if (!empty($data['id_template'])) {
            DB::table('templates')
                ->where('id_template', $data['id_template'])
                ->update([
                    'name'             => $data['name'],
                    'event_program_id' => $data['event_program_id'],
                    'pic_user_id'      => $data['pic_user_id'],
                    'category'         => $cat,
                    'description'      => $data['description'],
                    'updated_at'       => now(),
                ]);
            $request->session()->forget('_old_input');
            
            if ($request->has('done')) {
                return redirect()
                    ->route('master.templates.index')
                    ->with('success', 'Template disimpan & ditutup.');
            }
            
            return redirect()
                ->route('master.templates.edit', ['id' => $data['id_template']])
                ->with('success', 'Template diperbarui.');
        }

        $newId = DB::table('templates')->insertGetId([
            'name'             => $data['name'],
            'event_program_id' => $data['event_program_id'],
            'pic_user_id'      => $data['pic_user_id'],
            'category'         => $cat,
            'description'      => $data['description'],
            'created_at'       => now(),
            'updated_at'       => now(),
        ], 'id_template');

        if ($request->has('done')) {
            return redirect()
                ->route('master.templates.index')
                ->with('success', 'Template disimpan & ditutup.');
        }
        
        return redirect()
            ->route('master.templates.edit', $newId)
            ->with('success', 'Template dibuat. Silakan tambahkan item.')
            ->with('openAddItem', true);
    }

    /** Halaman edit (pakai Blade yang sama) */
    public function edit($id)
    {
        $template = DB::table('templates as t')
            ->leftJoin('event_programs as ep', 'ep.id_event_program', '=', 't.event_program_id')
            ->leftJoin('users as u', 'u.id_user', '=', 't.pic_user_id')
            ->where('t.id_template', $id)
            ->select(
                't.*',
                'ep.name as event_program_name',
                'u.name as pic_user_name'
            )
            ->first();

        $users = DB::table('users')
            ->select('id_user', 'name')
            ->orderBy('name')
            ->get();

        $programs = DB::table('event_programs')
            ->select('id_event_program', 'name', 'category', 'description', 'pic_user_id')
            ->orderBy('name')
            ->get();

        $items = $this->fetchTemplateItems((int)$id);

        $grandTotal = $items->reduce(function ($sum, $r) {
            return $sum + ((int)$r->qty * (float)$r->price);
        }, 0);

        return view('master.templates.create', [
            'template'      => $template,
            'templateItems' => $items,
            'users'         => $users,
            'programs'      => $programs,
            'grandTotal'    => $grandTotal,
            'openAddItem'   => session('openAddItem', false),
            'templates'     => collect(), // sembunyikan list saat edit
            'readonly'      => false,
        ]);
    }

    /** SHOW: Detail (read-only) → pakai Blade yang sama */
    public function show($id)
    {
        $template = DB::table('templates as t')
            ->leftJoin('event_programs as ep', 'ep.id_event_program', '=', 't.event_program_id')
            ->leftJoin('users as u', 'u.id_user', '=', 't.pic_user_id')
            ->where('t.id_template', $id)
            ->select(
                't.*',
                'ep.name as event_program_name',
                'u.name as pic_user_name'
            )
            ->first();

        $items = $this->fetchTemplateItems((int)$id);

        $grandTotal = $items->reduce(function ($sum, $r) {
            return $sum + ((int)$r->qty * (float)$r->price);
        }, 0);

        return view('master.templates.create', [
            'template'      => $template,
            'templateItems' => $items,
            'users'         => collect(),
            'programs'      => collect(),
            'grandTotal'    => $grandTotal,
            'openAddItem'   => false,
            'templates'     => collect(),  // sembunyikan list saat detail
            'readonly'      => true,
        ]);
    }

    /** DELETE template + items */
    public function destroy($id)
    {
        $used = DB::table('budgets')->where('template_id', $id)->exists();

        if($used){
            return redirect()->route('master.templates.index')
                ->with('error','Template tidak bisa dihapus karena sudah dipakai di Budget. Silahkan hapus Budget terlebih dahulu.');
        }

        DB::transaction(function() use ($id){
            DB::table('template_items')->where('template_id',$id)->delete();
            DB::table('templates')->where('id_template',$id)->delete();
        });

        return redirect()->route('master.templates.index')
            ->with('success','Template dihapus.');
    }

    /* =========================================================
     * Items in Template
     * =======================================================*/

    public function storeItem(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:master_items,id_item',
            'qty'     => 'required|integer|min:1',
        ]);

        // Ambil data master item + unit
        $mi = DB::table('master_items')
            ->leftJoin('units', 'units.id_unit', '=', 'master_items.unit_id')
            ->where('master_items.id_item', $request->item_id)
            ->first([
                'master_items.item_name',
                'master_items.description',
                'master_items.top_price',
                'master_items.bottom_price',
                'units.unit_name',
            ]);

        if (!$mi) {
            return redirect()
                ->route('master.templates.edit', $id)
                ->with('error', 'Master item tidak ditemukan.');
        }

        $unit      = $mi->unit_name ?? '';
        $unitPrice = (float)($mi->top_price ?? 0);
        if ($unitPrice <= 0) {
            $unitPrice = (float)($mi->bottom_price ?? 0);
        }

        // ✅ Cek apakah item sudah ada di template_items
        $existing = DB::table('template_items')
            ->where('template_id', (int)$id)
            ->where('item_id', (int)$request->item_id)
            ->first();

        if ($existing) {
            // Update qty saja
            DB::table('template_items')
                ->where('id_template_item', $existing->id_template_item)
                ->update([
                    'qty'       => $existing->qty + (int)$request->qty,
                    'updated_at'=> now(),
                ]);
        } else {
            // Insert baru
            DB::table('template_items')->insert([
                'template_id' => (int)$id,
                'item_id'     => (int)$request->item_id,
                'qty'         => (int)$request->qty,
                'item_name'   => (string)$mi->item_name,
                'unit'        => (string)$unit,
                'unit_price'  => (float)$unitPrice,
                'short_desc'  => (string)($mi->description ?? ''),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()
            ->route('master.templates.edit', $id)
            ->with('success', 'Item ditambahkan.')
            ->with('afterAddItem', true); // untuk scroll+modal auto buka lagi
    }


    public function destroyItem($id, $rowId)
    {
        DB::table('template_items')
            ->where('id_template_item', $rowId)
            ->where('template_id', $id)
            ->delete();

        return redirect()
            ->route('master.templates.edit', $id)
            ->with('success', 'Item dihapus.');
    }

    /* =========================================================
     * AJAX Endpoints
     * =======================================================*/

    public function programDetail($id)
    {
        $p = DB::table('event_programs')
            ->where('id_event_program', $id)
            ->first(['id_event_program', 'name', 'category', 'description', 'pic_user_id']);

        if (!$p) {
            return response()->json(['ok' => false, 'message' => 'Program not found'], 404);
        }

        return response()->json([
            'ok'      => true,
            'program' => [
                'id'          => $p->id_event_program,
                'name'        => $p->name,
                'type'        => $p->category,
                'category'    => $p->category,
                'description' => $p->description,
                'pic_user_id' => $p->pic_user_id,
            ],
            'category_suggested' => $p->category,
            'description'        => $p->description,
            'pic_user_id'        => $p->pic_user_id,
        ]);
    }

    public function itemSearch(Request $request)
    {
        try {
            $q = trim($request->get('q', ''));

            $query = DB::table('master_items as i')
                ->leftJoin('units as u', 'u.id_unit', '=', 'i.unit_id')
                ->orderBy('i.item_name')
                ->limit(20);

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('i.item_name', 'like', "%{$q}%")
                      ->orWhere('i.description', 'like', "%{$q}%");
                });
            }

            $rows = $query->get([
                DB::raw('i.id_item as id'),
                DB::raw('i.item_name as item_name'),
                DB::raw('i.description as description'),
                DB::raw('i.bottom_price as bottom_price'),
                DB::raw('i.top_price as top_price'),
                DB::raw('COALESCE(u.unit_name, "") as unit_name'),
            ]);

            return response()->json([
                'ok'    => true,
                'items' => $rows,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'items'   => [],
            ], 500);
        }
    }

    public function updateItemQty(Request $request, $id, $rowId)
    {
        // Validasi: boleh kirim delta=inc/dec ATAU qty langsung
        $data = $request->validate([
            'delta' => 'nullable|in:inc,dec',
            'qty'   => 'nullable|integer|min:1',
        ]);

        $row = DB::table('template_items')
            ->where('id_template_item', $rowId)
            ->where('template_id', $id)
            ->first(['qty','unit_price']);

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'Row not found'], 404);
        }

        $newQty = (int) $row->qty;

        if (!empty($data['delta'])) {
            $newQty += ($data['delta'] === 'inc') ? 1 : -1;
        }
        if (isset($data['qty'])) {
            $newQty = (int) $data['qty'];
        }
        if ($newQty < 1) {
            $newQty = 1; // minimal 1
        }

        DB::table('template_items')
            ->where('id_template_item', $rowId)
            ->update([
                'qty'        => $newQty,
                'updated_at' => now(),
            ]);

        $rowTotal = (int) $newQty * (float) $row->unit_price;

        $grand = (float) DB::table('template_items')
            ->where('template_id', $id)
            ->selectRaw('COALESCE(SUM(qty * unit_price),0) as gt')
            ->value('gt');

        return response()->json([
            'ok'           => true,
            'qty'          => $newQty,
            'row_total'    => $rowTotal,
            'grand_total'  => $grand,
        ]);
    }

}
