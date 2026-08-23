<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phiếu nhập kho
        Schema::create('import_receipts', function (Blueprint $table) {
            $table->bigIncrements('id_im');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chờ, 1=đã nhập, 2=hủy');
            $table->timestamps();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('import_detail', function (Blueprint $table) {
            $table->bigIncrements('id_import_detail');
            $table->unsignedBigInteger('id_im');
            $table->unsignedBigInteger('id_ingre');
            $table->unsignedBigInteger('id_su');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);

            $table->foreign('id_im')->references('id_im')->on('import_receipts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_su')->references('id_su')->on('supplier')->onUpdate('cascade')->onDelete('restrict');
        });

        // Phiếu xuất kho
        Schema::create('export_receipts', function (Blueprint $table) {
            $table->bigIncrements('id_ex');
            $table->unsignedBigInteger('id_br');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->text('note')->nullable();
            $table->string('reason', 30)->nullable()->comment('sale, waste, broken, adjustment');
            $table->unsignedInteger('source_ordetail')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=chờ, 1=đã duyệt, 2=từ chối');
            $table->timestamps();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->index(['id_br', 'created_at']);
        });

        Schema::create('export_detail', function (Blueprint $table) {
            $table->bigIncrements('id_export_detail');
            $table->unsignedBigInteger('id_ex');
            $table->unsignedBigInteger('id_food')->nullable();
            $table->unsignedBigInteger('id_ingre')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);

            $table->foreign('id_ex')->references('id_ex')->on('export_receipts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_food')->references('id_food')->on('menu_items')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_ingre')->references('id_ingre')->on('ingredients')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_detail');
        Schema::dropIfExists('export_receipts');
        Schema::dropIfExists('import_detail');
        Schema::dropIfExists('import_receipts');
    }
};