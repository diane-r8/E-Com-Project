<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // order_id
            $table->unsignedBigInteger('user_id')->nullable(); // Null for guest users
            $table->boolean('rush_order')->default(false); // If rush order, true or false
            $table->string('phone_number'); // Buyer's phone number
            $table->unsignedBigInteger('delivery_area_id'); // Reference to delivery_areas table
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); // Status options
            $table->decimal('total_price', 10, 2); // Total order price
            $table->timestamps();

            // ✅ Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('delivery_area_id')->references('id')->on('delivery_areas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
