<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates', function (Blueprint $table) {
            // INT(11) signed AUTOINCREMENT agar cocok dengan budgets.template_id (INT)
            $table->integer('id_template')->autoIncrement();

            $table->string('name');

            // FK ke event_programs.id_event_program (BIGINT UNSIGNED)
            $table->unsignedBigInteger('event_program_id');
            $table->foreign('event_program_id')
                  ->references('id_event_program')
                  ->on('event_programs')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            // FK ke users.id_user (INT signed)
            $table->integer('pic_user_id');
            $table->foreign('pic_user_id')
                  ->references('id_user')
                  ->on('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->enum('category', ['off_air', 'on_air'])->default('off_air');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('templates');
    }
};
