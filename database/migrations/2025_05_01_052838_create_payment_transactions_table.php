<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_id');  // Xendit payment ID
            $table->string('external_id');  // Your reference ID
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->string('payment_method');
            $table->string('payment_channel');
            $table->string('payment_url')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->text('payload')->nullable();  // Store the full response
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}