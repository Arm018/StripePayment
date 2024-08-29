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
        $notebooks = [
            ['name' => 'Acer Aspire 5'],
            ['name' => 'Dell Inspiron 15'],
            ['name' => 'HP Pavilion 14'],
            ['name' => 'Lenovo ThinkPad X1'],
            ['name' => 'Asus ZenBook 14'],
            ['name' => 'Apple MacBook Air'],
            ['name' => 'Microsoft Surface Laptop'],
            ['name' => 'Samsung Galaxy Book'],
            ['name' => 'Razer Blade Stealth'],
            ['name' => 'MSI GF63 Thin'],
        ];

        foreach ($notebooks as $notebook) {
            Product::create([
                'name' => $notebook['name'],
                'price' => fake()->randomFloat(2,500,3500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

}
