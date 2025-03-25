<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // payment_id
            $table->unsignedBigInteger('order_id');
            $table->enum('payment_method', ['GCash', 'COD']); // Only GCash or COD
            $table->string('proof_of_payment')->nullable(); // Image proof (optional for GCash)
            $table->decimal('amount_paid', 10, 2); // Amount paid by buyer
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending'); // Payment status
            $table->timestamps();

            // ✅ Corrected foreign key constraint
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
