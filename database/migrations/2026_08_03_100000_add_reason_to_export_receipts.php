<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('export_receipts', function (Blueprint $table) {
            // Lý do xuất: sale (tự động khi bếp chế biến), waste (hư), broken (đổ/bể), adjustment (điều chỉnh)
            $table->string('reason', 30)->nullable()->after('note');
            // Mã tham chiếu tới order_detail khi xuất tự động để chống xuất trùng
            $table->unsignedInteger('source_ordetail')->nullable()->after('reason');
        });
    }

    public function down()
    {
        Schema::table('export_receipts', function (Blueprint $table) {
            $table->dropColumn(['reason', 'source_ordetail']);
        });
    }
};
