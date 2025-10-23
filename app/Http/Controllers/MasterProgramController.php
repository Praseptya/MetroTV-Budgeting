<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    /** List + form create */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $programs = DB::table('event_programs as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.pic_user_id')
            ->when($q !== '', function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('p.name', 'like', "%{$q}%")
                      ->orWhere('p.category', 'like', "%{$q}%")
                      ->orWhere('p.description', 'like', "%{$q}%");
                });
            })
            ->orderBy('p.name', 'asc')
            ->select([
                'p.id_event_program',
                'p.name',
                'p.category',
                'p.description',     // <-- include description
                'p.pic_user_id',
                DB::raw('COALESCE(u.name, "-") as pic_name'),
                'p.created_at',
            ])
            ->paginate(12)
            ->withQueryString();

        $users = DB::table('users')
            ->orderBy('name', 'asc')
            ->get(['id_user', 'name']);

        return view('master.program.index', compact('programs', 'users'));
    }

    /** Simpan baru */
    public function store(Request $request)
    {
        if ($this->isStaff()) { return back()->with('error','Akses ditolak untuk Staff.'); }
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'pic_user_id' => 'required|integer|exists:users,id_user',
            'category'    => 'required|in:Off Air,On Air',
            'description' => 'nullable|string|max:255',
        ], [
            'pic_user_id.required' => 'Penanggung Jawab wajib dipilih.',
            'pic_user_id.exists'   => 'Penanggung Jawab tidak valid, pilih dari daftar.',
        ]);

        DB::table('event_programs')->insert([
            'name'        => $data['name'],
            'pic_user_id' => $data['pic_user_id'],
            'category'    => $data['category'],
            'description' => $data['description'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Program berhasil ditambahkan.');
    }

    /** Form edit */
    public function edit($id)
    {
        if ($this->isStaff()) { return back()->with('error','Akses ditolak untuk Staff.'); }
        $program = DB::table('event_programs')->where('id_event_program', $id)->first();
        if (!$program) {
            return redirect()->route('master.program.index')->with('error', 'Program tidak ditemukan.');
        }

        $users = DB::table('users')->orderBy('name', 'asc')->get(['id_user', 'name']);

    }

    /** Update */
    public function update(Request $r, $id) {
        if ($this->isStaff()) { return back()->with('error','Akses ditolak untuk Staff.'); }
        $data = $r->validate([
            'name'        => 'required|string|max:150',
            'pic_user_id' => 'required|integer|exists:users,id_user',
            'category'    => 'required|in:Off Air,On Air',
            'description' => 'nullable|string|max:255',
        ], [
            'pic_user_id.required' => 'Penanggung Jawab wajib dipilih.',
            'pic_user_id.exists'   => 'Penanggung Jawab tidak valid, pilih dari daftar.',
        ]);

        DB::table('event_programs')
        ->where('id_event_program', $id)
        ->update([
            'name'        => $data['name'],
            'pic_user_id' => $data['pic_user_id'],
            'category'    => $data['category'],
            'description' => $data['description'] ?? null,
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Program berhasil diperbarui.');
    }

    /** Hapus */
    public function destroy($id)
    {
        if ($this->isStaff()) { return back()->with('error','Akses ditolak untuk Staff.'); }
        // lindungi bila dipakai template
        $used = DB::table('templates')->where('event_program_id', $id)->exists();
        if ($used) {
            return back()->with('error', 'Tidak dapat menghapus: Program sudah dipakai pada Template.');
        }

        DB::table('event_programs')->where('id_event_program', $id)->delete();

        return back()->with('success', 'Program berhasil dihapus.');
    }

    private function isStaff(): bool
    {
        $u = auth()->user();
        if (!$u) return true;
        $role = strtolower((string)($u->level_name ?? $u->level ?? $u->role ?? ''));
        return in_array($role, ['staff','staf']);
    }
}
