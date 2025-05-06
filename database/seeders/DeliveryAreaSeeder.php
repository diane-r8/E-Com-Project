<?php
namespace Database\Seeders; 




use Illuminate\Database\Seeder;
use App\Models\DeliveryArea;

class DeliveryAreaSeeder extends Seeder
{
    public function run()



    {
        DeliveryArea::truncate();
        DeliveryArea::insert([
            ['area_name' => 'Sto. Domingo', 'delivery_fee' => 50.00],
            ['area_name' => 'Tabaco',        'delivery_fee' => 100.00],
            ['area_name' => 'Bacacay',       'delivery_fee' => 80.00],
            ['area_name' => 'Legazpi',       'delivery_fee' => 150.00],
            ['area_name' => 'Daraga',        'delivery_fee' => 200.00],
        ]);
    }
}
