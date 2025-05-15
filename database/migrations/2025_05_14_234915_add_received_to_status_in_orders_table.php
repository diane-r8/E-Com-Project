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
        // Update the enum column to add 'Received'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Received') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
       // Roll back the enum change if needed
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending'");
    }
};