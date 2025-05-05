<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_number');
            $table->foreignId('delivery_area_id')->constrained();
            $table->string('shipping_address');
            $table->string('landmark')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // One user can only have one default address
            $table->unique(['user_id', 'is_default'], 'user_address_default_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_addresses');
    }
};