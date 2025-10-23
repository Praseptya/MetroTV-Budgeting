<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin-only', function ($user) {
            // 1) Cek boolean langsung di tabel users
            foreach (['is_admin', 'is_superadmin', 'is_super_admin'] as $col) {
                if (Schema::hasColumn('users', $col) && (int)($user->{$col} ?? 0) === 1) {
                    return true;
                }
            }

            // 2) Cek kolom string role/level di users
            foreach (['role', 'level', 'user_level', 'user_role'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $val = strtolower((string)($user->{$col} ?? ''));
                    if (in_array($val, ['admin', 'administrator', 'superadmin', 'super admin'], true)) {
                        return true;
                    }
                }
            }

            // 3) Cek foreign key level di users lalu join ke tabel level/role yang ada
            $fkCols = ['level_id', 'user_level_id', 'id_user_level', 'id_level', 'role_id'];
            $levelId = null;
            foreach ($fkCols as $fk) {
                if (Schema::hasColumn('users', $fk) && !empty($user->{$fk})) {
                    $levelId = $user->{$fk};
                    break;
                }
            }

            if ($levelId) {
                // kandidat tabel referensi
                $tables = ['user_level', 'user_levels', 'levels', 'roles'];
                foreach ($tables as $table) {
                    if (!Schema::hasTable($table)) continue;

                    // cari kolom id yang tersedia di tabel referensi
                    $idCols = ['id_level', 'id', 'level_id', 'role_id'];
                    $nameCols = ['level_name', 'name', 'nama', 'title', 'role_name'];

                    $q = DB::table($table);
                    $bound = false;
                    foreach ($idCols as $idc) {
                        if (Schema::hasColumn($table, $idc)) { $q->where($idc, $levelId); $bound = true; break; }
                    }
                    if (!$bound) continue;

                    $row = $q->first();
                    if (!$row) continue;

                    foreach ($nameCols as $nc) {
                        if (Schema::hasColumn($table, $nc) && isset($row->{$nc})) {
                            $name = strtolower((string)$row->{$nc});
                            if (in_array($name, ['admin','administrator','superadmin','super admin'], true)) {
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        });

        // Staff tidak boleh edit/hapus master data
        Gate::define('master-editable', function ($user) {
            $levelName = null;

            // 1) Coba baca langsung dari kolom users
            foreach (['level','role','level_name'] as $col) {
                if (Schema::hasColumn('users', $col) && !empty($user->{$col})) {
                    $levelName = $user->{$col};
                    break;
                }
            }

            // 2) Jika pakai FK ke tabel user_level
            if (!$levelName) {
                $fkCol = null;
                foreach (['user_level_id','level_id','id_user_level'] as $fk) {
                    if (Schema::hasColumn('users', $fk) && !empty($user->{$fk})) {
                        $fkCol = $fk;
                        break;
                    }
                }
                if ($fkCol && Schema::hasTable('user_level')) {
                    $nameCol = Schema::hasColumn('user_level','level_name') ? 'level_name'
                              : (Schema::hasColumn('user_level','name') ? 'name' : null);
                    $idCol   = Schema::hasColumn('user_level','id_user_level') ? 'id_user_level'
                              : (Schema::hasColumn('user_level','id') ? 'id' : null);

                    if ($nameCol && $idCol) {
                        $row = DB::table('user_level')->where($idCol, $user->{$fkCol})->first([$nameCol]);
                        $levelName = $row->{$nameCol} ?? null;
                    }
                }
            }

            $lv = strtolower(trim((string)$levelName));
            // hanya non-staff yang boleh edit/hapus
            return !in_array($lv, ['staff','staf']);
        });

    }
}
