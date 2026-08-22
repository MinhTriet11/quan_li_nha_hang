<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Customer extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            'name' => 'Minh Triết',
            'phone' => '0769814787',
            'password' => Hash::make('123456'),
            'address' => 'Q1',
            'status' => '1',
            'email' => 'trietnnp@gmail.com'
        ]);
    }
}
