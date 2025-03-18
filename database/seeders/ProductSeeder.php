<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'name' => 'Keychain',
            'description' => 'Beautiful handmade keychain',
            'price' => 50.00,
            'stock' => 10,
            'availability' => true,
            'image' => 'Keychains/kins.jpg', // Make sure you have this file
        ]);

        Product::create([
            'name' => 'Ribbons',
            'description' => 'Decorative flower vase',
            'price' => 30.00,
            'stock' => 5,
            'availability' => true,
            'image' => 'Keychains/ribbons.jpg',
        ]);
    }
}
