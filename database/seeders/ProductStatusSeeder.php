<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productStatuses = [
            ['name' => 'Enabled', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Disabled', 'created_at' => now(), 'updated_at' => now()],
        ];

        /* Save The Data */
        DB::table('product_statuses')
            ->insert($productStatuses);
    }
}
