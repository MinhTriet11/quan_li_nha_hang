<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_check_receipts', function (Blueprint $table) {
            $table->bigIncrements('id_sc');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chờ duyệt, 1=đã duyệt, 2=từ chối');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('id_br')->references('id_br')->on('branches')->onDelete('cascade');
            $table->index(['id_br', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_check_receipts');
    }
};