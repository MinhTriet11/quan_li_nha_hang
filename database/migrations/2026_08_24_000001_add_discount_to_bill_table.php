<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill', function (Blueprint $table) {
            // % giảm giá áp dụng cho hóa đơn (0–100)
            $table->decimal('discount_percent', 5, 2)
                  ->default(0)
                  ->after('total')
                  ->comment('Phần trăm giảm giá (0–100)');

            // Số tiền được giảm = subtotal * discount_percent / 100
            $table->decimal('discount_amount', 14, 2)
                  ->default(0)
                  ->after('discount_percent')
                  ->comment('Số tiền giảm (VND)');
        });
    }

    public function down(): void
    {
        Schema::table('bill', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount']);
        });
    }
};
