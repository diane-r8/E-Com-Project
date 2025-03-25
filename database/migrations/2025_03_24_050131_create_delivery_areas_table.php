<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id(); // delivery_area_id
            $table->string('area_name');
            $table->decimal('delivery_fee', 8, 2); // Delivery fee for the area
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_areas');
    }
};
