<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id_order');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_table')->nullable();
            $table->unsignedBigInteger('id_cus')->nullable();
            $table->timestamp('order_time')->useCurrent();
            $table->tinyInteger('status')->default(0)->comment('0=mới, 1=đang chế biến, 2=hoàn tất, 3=hủy');
            $table->text('note')->nullable();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_table')->references('id_table')->on('tables')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_cus')->references('id_cus')->on('customers')->onUpdate('cascade')->onDelete('set null');
            $table->index(['id_br', 'order_time']);
            $table->index('status');
        });

        Schema::create('order_detail', function (Blueprint $table) {
            $table->bigIncrements('id_ordetail');
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_food');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->tinyInteger('status')->default(0)->comment('0=chờ, 1=đang nấu, 2=xong, 3=hủy');

            $table->foreign('id_order')->references('id_order')->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_food')->references('id_food')->on('menu_items')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_detail');
        Schema::dropIfExists('orders');
    }
};