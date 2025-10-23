<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Akses ditolak: user tidak terautentikasi.');
        }

        // Ambil nama level user (Admin, Staff, Manager, Director, dll)
        $levelName = DB::table('user_levels')
            ->where('id_level', $user->user_level_id)
            ->value('level_name');

        // Normalisasi huruf biar case-insensitive
        $levelName = strtolower(trim($levelName));

        // Daftar role yang diperbolehkan (misal 'admin','manager','director')
        $allowedRoles = array_map('strtolower', $allowedRoles);

        if (!in_array($levelName, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
