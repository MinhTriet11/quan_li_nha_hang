<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id_user');
            $table->unsignedBigInteger('id_br')->nullable();
            $table->unsignedBigInteger('id_role')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_br')->references('id_br')->on('branches')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_role')->references('id_role')->on('roles')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};