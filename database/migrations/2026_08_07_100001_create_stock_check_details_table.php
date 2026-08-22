<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_check_details', function (Blueprint $table) {
            $table->bigIncrements('id_sc_detail');
            $table->unsignedBigInteger('id_sc');
            $table->unsignedBigInteger('id_ingre');
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('actual_quantity', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_sc')->references('id_sc')->on('stock_check_receipts')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onDelete('restrict');
            $table->unique(['id_sc', 'id_ingre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_check_details');
    }
};