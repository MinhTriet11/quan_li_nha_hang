<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tableTypes = [
            [
                'name' => 'Thường',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gia Đình',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'View Đẹp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VIP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('table_types')->insert($tableTypes);
    }
}
