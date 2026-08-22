<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột id_role (FK) vào bảng users để liên kết với bảng roles.
     * Mặc định id_role = 6 (Nhân viên phục vụ) cho user mới tạo sau này.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_role')
                ->nullable()
                ->after('status')
                ->comment('FK -> roles.id_role');

            $table->foreign('id_role')
                ->references('id_role')->on('roles')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        // Gán role mặc định cho user đã tồn tại:
        //  - email chứa 'admin' -> Quản trị viên (1)
        //  - còn lại -> Nhân viên phục vụ (6)
        \DB::table('users')->where('email', 'like', '%admin%')->update(['id_role' => 1]);
        \DB::table('users')->whereNull('id_role')->update(['id_role' => 6]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
            $table->dropColumn('id_role');
        });
    }
};
