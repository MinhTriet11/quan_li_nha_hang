<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill', function (Blueprint $table) {
            $table->bigIncrements('id_bill');
            $table->unsignedBigInteger('id_order');
            $table->decimal('total', 14, 2)->default(0);
            $table->string('pay_method', 30)->nullable();
            $table->timestamp('pay_time')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chưa TT, 1=đã TT, 2=hủy');

            $table->foreign('id_order')->references('id_order')->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->index(['status', 'pay_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill');
    }
};