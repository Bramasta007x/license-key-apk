<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Eficon Premium License',
            'price' => 15000000,
        ]);

        Product::create([
            'name' => 'Eficon Gold License',
            'price' => 25000000,
        ]);
    }
}
