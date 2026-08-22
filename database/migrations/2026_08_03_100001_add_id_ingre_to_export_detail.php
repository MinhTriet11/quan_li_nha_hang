<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('export_detail', function (Blueprint $table) {
            // Xuất kho gắn với nguyên liệu (id_ingre). id_food giữ để truy vết từ recipe.
            $table->unsignedInteger('id_ingre')->nullable()->after('id_food');
        });
    }

    public function down()
    {
        Schema::table('export_detail', function (Blueprint $table) {
            $table->dropColumn('id_ingre');
        });
    }
};
