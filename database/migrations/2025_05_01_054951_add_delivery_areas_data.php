<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDeliveryAreasData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // These are the delivery areas we need to ensure exist
        $areas = [
            ['id' => 1, 'area_name' => 'Sto. Domingo', 'delivery_fee' => 50.00],
            ['id' => 2, 'area_name' => 'Tabaco', 'delivery_fee' => 100.00],
            ['id' => 3, 'area_name' => 'Bacacay', 'delivery_fee' => 80.00],
            ['id' => 4, 'area_name' => 'Legazpi', 'delivery_fee' => 150.00],
            ['id' => 5, 'area_name' => 'Daraga', 'delivery_fee' => 200.00],
        ];
        
        foreach ($areas as $area) {
            // Check if this area ID exists
            $exists = DB::table('delivery_areas')->where('id', $area['id'])->exists();
            
            if (!$exists) {
                // Add the area if it doesn't exist
                DB::table('delivery_areas')->insert([
                    'id' => $area['id'],
                    'area_name' => $area['area_name'],
                    'delivery_fee' => $area['delivery_fee'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optional: Remove the areas we added if desired
        // DB::table('delivery_areas')->whereIn('id', [1, 2, 3, 4, 5])->delete();
    }
}