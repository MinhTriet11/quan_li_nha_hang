<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_receipts', function (Blueprint $table) {
            $table->bigIncrements('id_tran');
            $table->unsignedBigInteger('from_branch');
            $table->unsignedBigInteger('to_branch');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chờ, 1=đã duyệt, 2=từ chối');
            $table->timestamps();

            $table->foreign('from_branch')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('to_branch')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('tran_detail', function (Blueprint $table) {
            $table->bigIncrements('id_tran_detail');
            $table->unsignedBigInteger('id_tran');
            $table->unsignedBigInteger('id_ingre');
            $table->decimal('quantity', 12, 2)->default(0);

            $table->foreign('id_tran')->references('id_tran')->on('transfer_receipts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tran_detail');
        Schema::dropIfExists('transfer_receipts');
    }
};