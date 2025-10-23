<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Jika belum ada: buat persis seperti di dump
        if (!Schema::hasTable('event_programs')) {
            Schema::create('event_programs', function (Blueprint $table) {
                $table->increments('id_program');           // PK auto increment
                $table->string('name', 100);                // nama
                $table->enum('type', ['OFF AIR','ON AIR'])->nullable();
                $table->text('description')->nullable();
                // Tidak ada timestamps di skema dump
            });
            return;
        }

        // Jika sudah ada: pastikan kolom minimal sesuai
        Schema::table('event_programs', function (Blueprint $table) {
            if (!Schema::hasColumn('event_programs', 'name')) {
                $table->string('name', 100);
            }
            if (!Schema::hasColumn('event_programs', 'type')) {
                $table->enum('type', ['OFF AIR','ON AIR'])->nullable()->after('name');
            }
            if (!Schema::hasColumn('event_programs', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        // Hanya drop jika kamu ingin benar-benar rollback.
        Schema::dropIfExists('event_programs');
    }
};
