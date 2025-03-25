<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id(); // custom_orders_id
            $table->unsignedBigInteger('order_id');
            $table->text('details'); // Custom order details
            $table->decimal('budget_max', 10, 2); // Customer's budget
            $table->decimal('final_price', 10, 2)->nullable(); // Final agreed price
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_orders');
    }
};
