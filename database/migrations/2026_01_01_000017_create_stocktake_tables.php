<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->bigIncrements('id_stock');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chờ duyệt, 1=đã duyệt, 2=từ chối');
            $table->timestamps();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->index(['id_br', 'created_at']);
        });

        Schema::create('stock_detail', function (Blueprint $table) {
            $table->bigIncrements('id_sto_detail');
            $table->unsignedBigInteger('id_stock');
            $table->unsignedBigInteger('id_ingre');
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('actual_quantity', 12, 2)->default(0);
            $table->text('note')->nullable();

            $table->foreign('id_stock')->references('id_stock')->on('stocktakes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['id_stock', 'id_ingre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_detail');
        Schema::dropIfExists('stocktakes');
    }
};