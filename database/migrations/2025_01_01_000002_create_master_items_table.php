<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('master_items', function (Blueprint $table) {
            $table->bigIncrements('id_item');
            $table->string('item_name', 150);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit', 50)->nullable(); // fallback text unit
            $table->unsignedBigInteger('bottom_price')->default(0);
            $table->unsignedBigInteger('top_price')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id_unit')->on('units')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('master_items');
    }
};
