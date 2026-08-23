<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_hour', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->bigIncrements('id_table');
            $table->unsignedBigInteger('id_br');
            $table->string('table_name');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('table_type_id')->nullable();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('table_type_id')->references('id')->on('table_types')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
        Schema::dropIfExists('table_types');
    }
};