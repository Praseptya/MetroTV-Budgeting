<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('template_items', function (Blueprint $table) {
            // bebas: pakai INT signed autoincrement
            $table->integer('id_template_item')->autoIncrement();

            // FK ke templates.id_template (INT signed)
            $table->integer('template_id');
            $table->foreign('template_id')
                  ->references('id_template')
                  ->on('templates')
                  ->cascadeOnDelete();

            // FK ke master_items.id_item (INT signed)
            $table->integer('item_id');

            $table->integer('qty')->default(0);

            // Snapshot dari master_items
            $table->string('item_name');
            $table->string('unit')->nullable();           // dari master_items.unit
            $table->decimal('unit_price', 15, 2)->default(0); // ambil top_price (atau bottom_price)
            $table->text('short_desc')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('template_items');
    }
};
