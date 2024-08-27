<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Product::create([
               'name' => fake()->word(),
                'price' => fake()->randomFloat(2,10,1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
