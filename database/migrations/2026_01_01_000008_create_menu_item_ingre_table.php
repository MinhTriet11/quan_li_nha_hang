<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_ingre', function (Blueprint $table) {
            $table->bigIncrements('id_mii');
            $table->unsignedBigInteger('id_food');
            $table->unsignedBigInteger('id_ingre');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->text('note')->nullable();

            $table->foreign('id_food')->references('id_food')->on('menu_items')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['id_food', 'id_ingre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_ingre');
    }
};