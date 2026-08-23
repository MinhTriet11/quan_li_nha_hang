<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_ingredient', function (Blueprint $table) {
            $table->bigIncrements('id_br_inger');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_ingre');
            $table->decimal('quantity', 12, 2)->default(0);

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['id_br', 'id_ingre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_ingredient');
    }
};