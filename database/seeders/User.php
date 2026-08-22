<?php

namespace Database\Seeders;

use Dotenv\Util\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Minh Triết',
            'phone' => '0769814787',
            'password' => Hash::make('123456'),
            'id_role' => 1,
            'id_br' => 1,
            'address' => 'Q1',
            'status' => '1'
        ]);
    }
}
