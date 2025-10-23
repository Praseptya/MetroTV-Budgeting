<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:admin-only']);
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    public function index(Request $request)
    {
        $resetUserId = $request->query('reset_user_id');

        $q = trim($request->get('q', ''));

        // dropdown level
        $levels = DB::table('user_levels')
            ->orderBy('id_level')
            ->get(['id_level', 'level_name']);

        // daftar user (paginate)
        $users = DB::table('users as u')
            ->leftJoin('user_levels as l', 'l.id_level', '=', 'u.user_level_id')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('u.name', 'like', "%{$q}%")
                      ->orWhere('u.email', 'like', "%{$q}%")
                      ->orWhere('u.username', 'like', "%{$q}%")
                      ->orWhere('l.level_name', 'like', "%{$q}%");
                });
            })
            ->selectRaw('u.id_user as id, u.name, u.email, u.username, u.user_level_id, l.level_name as role')
            ->orderByDesc('u.id_user')
            ->paginate(10)
            ->withQueryString();

        // opsi user lain untuk dropdown "Alihkan & Hapus"
        $userOptions = DB::table('users')
            ->orderBy('name')
            ->get(['id_user as id', 'name', 'email', 'username']);

        return view('users.index', [
            'users'       => $users,
            'q'           => $q,
            'levels'      => $levels,
            'userOptions' => $userOptions,
            'resetUserId' => $resetUserId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:100', Rule::unique('users', 'email')],
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')],
            'role'      => ['required', 'integer', Rule::exists('user_levels', 'id_level')],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'role.required' => 'Level/Role wajib dipilih.',
        ]);

        DB::table('users')->insert([
            'name'          => $request->name,
            'email'         => $request->email,
            'username'      => $request->username,
            'password'      => Hash::make($request->password),
            'user_level_id' => (int) $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $id = (int) $id;

        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($id, 'id_user')],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id, 'id_user')],
            'role'     => ['required', 'integer', Rule::exists('user_levels', 'id_level')],
        ]);

        DB::table('users')
            ->where('id_user', $id)
            ->update([
                'name'          => $request->name,
                'email'         => $request->email,
                'username'      => $request->username,
                'user_level_id' => (int) $request->role,
            ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    public function resetPassword(Request $request, $id)
    {
        $id = (int) $id;

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::table('users')
            ->where('id_user', $id)
            ->update(['password' => Hash::make($request->password)]);

        return redirect()->route('users.index')->with('success', 'Password user berhasil direset.');
    }

    public function destroy(Request $request, $id)
    {
        $id = (int) $id;

        // Cegah hapus diri sendiri
        if ((int)auth()->id() === $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek referensi di budgets.created_by
        $refCount = DB::table('budgets')->where('created_by', $id)->count();
        if ($refCount > 0) {
            return back()->with('error',
                "User masih dipakai sebagai pembuat pada {$refCount} budget. Gunakan fitur 'Alihkan & Hapus' untuk memindahkan ownership terlebih dahulu.");
        }

        try {
            DB::table('users')->where('id_user', $id)->delete();
        } catch (QueryException $e) {
            // 1451: cannot delete due to FK
            if ((int)$e->getCode() === 23000) {
                return back()->with('error',
                    'User masih direferensikan oleh data lain. Gunakan "Alihkan & Hapus" atau rapikan relasi terlebih dahulu.');
            }
            throw $e;
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    /** Alihkan semua budgets.created_by dari user sumber -> user target, lalu hapus user sumber */
    public function reassignAndDestroy(Request $request, $id)
    {
        $sourceId = (int) $id;

        $request->validate([
            'target_user_id' => ['required', 'integer', Rule::exists('users', 'id_user')],
        ], [
            'target_user_id.required' => 'Pilih user tujuan.',
        ]);

        $targetId = (int) $request->target_user_id;

        if ($targetId === $sourceId) {
            return back()->with('error', 'User tujuan tidak boleh sama dengan user yang akan dihapus.');
        }

        // Cegah ambil diri sendiri
        if ((int)auth()->id() === $sourceId) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($sourceId, $targetId) {
            // Alihkan ownership budgets
            DB::table('budgets')
                ->where('created_by', $sourceId)
                ->update(['created_by' => $targetId]);

            // Hapus user sumber
            DB::table('users')->where('id_user', $sourceId)->delete();
        });

        return redirect()->route('users.index')->with('success', 'Budget berhasil dialihkan dan user berhasil dihapus.');
    }
}
