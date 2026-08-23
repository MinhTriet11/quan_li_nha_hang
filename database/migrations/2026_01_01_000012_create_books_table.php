<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->bigIncrements('id_book');
            $table->unsignedBigInteger('id_cus')->nullable();
            $table->unsignedBigInteger('id_table');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_table_type')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->string('guest_email')->nullable();
            $table->dateTime('time');
            $table->tinyInteger('status')->default(0)->comment('0=chờ, 1=đã xác nhận, 2=hủy');

            $table->foreign('id_cus')->references('id_cus')->on('customers')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_table')->references('id_table')->on('tables')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_table_type')->references('id')->on('table_types')->onUpdate('cascade')->onDelete('set null');
            $table->index(['id_br', 'time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};