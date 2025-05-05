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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('delivery_area_id')->constrained()->onDelete('cascade'); // 🔹 Add this
        $table->string('shipping_address');
        $table->string('phone_number');
        $table->boolean('rush_order')->default(false);
        $table->decimal('total_price', 10, 2);
        $table->decimal('delivery_fee', 10, 2);
        $table->enum('status', ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])->default('Pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};