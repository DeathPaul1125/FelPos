<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DenominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('denominations')->insert([
            ['value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['value' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['value' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['value' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['value' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['value' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
