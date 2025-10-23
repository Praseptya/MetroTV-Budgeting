<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah kolom dulu (SIGNED INT agar match dengan users.id_user yang umumnya INT)
        Schema::table('event_programs', function (Blueprint $table) {
            if (!Schema::hasColumn('event_programs', 'pic_user_id')) {
                $table->integer('pic_user_id')->nullable()->after('name');
            }
        });

        // Baru tambahkan FK di statement terpisah (lebih aman di MySQL)
        Schema::table('event_programs', function (Blueprint $table) {
            // users.pk di DB kamu = id_user
            $table->foreign('pic_user_id')
                  ->references('id_user')->on('users')
                  ->nullOnDelete(); // ON DELETE SET NULL
        });
    }

    public function down(): void
    {
        Schema::table('event_programs', function (Blueprint $table) {
            if (Schema::hasColumn('event_programs', 'pic_user_id')) {
                $table->dropForeign(['pic_user_id']);
                $table->dropColumn('pic_user_id');
            }
        });
    }
};
