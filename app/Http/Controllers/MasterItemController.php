<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    /* =========================================================
     * Utilities
     * =======================================================*/

    private function unitColumns(): array
    {
        $idCol = Schema::hasColumn('units','id_unit') ? 'id_unit'
               : (Schema::hasColumn('units','id')     ? 'id'     : null);

        $nameCol = Schema::hasColumn('units','unit_name') ? 'unit_name'
                 : (Schema::hasColumn('units','name')     ? 'name'
                 : (Schema::hasColumn('units','nama')     ? 'nama' : null));

        return [$idCol, $nameCol];
    }

    private function toIntMoney($v): int
    {
        if ($v === null || $v === '') return 0;
        return (int) preg_replace('/[^\d]/', '', (string)$v);
    }

    private function findOrCreateUnitId(?string $unitText, ?int $selectedUnitId): ?int
    {
        // 1) Jika user memilih dari daftar, langsung pakai id itu
        if ($selectedUnitId) {
            return $selectedUnitId;
        }

        // 2) Jika user mengetik manual
        $name = trim((string)$unitText);
        if ($name === '') {
            return null; // biarkan null kalau benar-benar kosong
        }

        // cari case-insensitive
        $found = DB::table('units')
            ->whereRaw('LOWER(unit_name) = ?', [mb_strtolower($name)])
            ->first(['id_unit']);

        if ($found) {
            return (int)$found->id_unit;
        }

        // buat baru
        return DB::table('units')->insertGetId([
            'unit_name' => $name,
        ]);
    }

    private function parseMoney(?string $s): int
    {
        if ($s === null) return 0;
        return (int) preg_replace('/[^\d]/', '', $s);
    }

    /* =========================================================
     * Pages
     * =======================================================*/

    /** LIST + FORM */
    public function index()
    {
        $items = DB::table('master_items as mi')
            ->leftJoin('units as u', 'u.id_unit', '=', 'mi.unit_id')
            ->select(
                'mi.id_item',
                'mi.item_name',
                'mi.description',
                'mi.bottom_price',
                'mi.top_price',
                'mi.unit_id',
                DB::raw('u.id_unit as id_unit'),
                DB::raw('COALESCE(u.unit_name, "") as unit_name')
            )
            // pakai created_at kalau ada; kalau tidak, fallback ke id_item
            ->when(Schema::hasColumn('master_items', 'created_at'),
                fn($q) => $q->orderBy('mi.created_at', 'desc'),
                fn($q) => $q->orderBy('mi.id_item', 'desc')
            )
            ->paginate(10);

        $units = DB::table('units')
            ->select('id_unit', 'unit_name')
            ->orderBy('unit_name')
            ->get();

        return view('master.items.index', compact('items', 'units'));
    }

    /** CREATE */
    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name'    => 'required|string|max:150',
            'bottom_price' => 'nullable|string',
            'top_price'    => 'nullable|string',
            'unit_id'      => 'nullable|integer|exists:units,id_unit',
            'unit_text'    => 'nullable|string|max:50',
            'description'  => 'nullable|string|max:255',
        ]);

        $resolvedUnitId = $this->findOrCreateUnitId($data['unit_text'] ?? null, $data['unit_id'] ?? null);

        DB::table('master_items')->insert([
            'item_name'    => $data['item_name'],
            'bottom_price' => $this->parseMoney($data['bottom_price'] ?? null),
            'top_price'    => $this->parseMoney($data['top_price'] ?? null),
            'unit_id'      => $resolvedUnitId,               // ← bisa id baru
            'description'  => $data['description'] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'item_name'    => 'required|string|max:150',
            'bottom_price' => 'nullable|string',
            'top_price'    => 'nullable|string',
            'unit_id'      => 'nullable|integer|exists:units,id_unit',
            'unit_text'    => 'nullable|string|max:50',
            'description'  => 'nullable|string|max:255',
        ]);

        $resolvedUnitId = $this->findOrCreateUnitId($data['unit_text'] ?? null, $data['unit_id'] ?? null);

        DB::table('master_items')
            ->where('id_item', $id)
            ->update([
                'item_name'    => $data['item_name'],
                'bottom_price' => $this->parseMoney($data['bottom_price'] ?? null),
                'top_price'    => $this->parseMoney($data['top_price'] ?? null),
                'unit_id'      => $resolvedUnitId,            // ← bisa id baru
                'description'  => $data['description'] ?? null,
                'updated_at'   => now(),
            ]);

        return back()->with('success', 'Item berhasil diperbarui.');
    }

    /** DELETE */
    public function destroy($id)
    {
        DB::table('master_items')->where('id_item', $id)->delete();
        return back()->with('success', 'Item dihapus.');
    }
}
