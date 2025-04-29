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
    Schema::table('orders', function (Blueprint $table) {
        // Adding the new 'full_name' column after 'shipping_address'
        $table->string('full_name')->after('shipping_address');

        // Adding the new 'is_default' column after 'rush_order' (set default to false)
        $table->boolean('is_default')->default(false)->after('rush_order');

        // Ensure only one default address per user
        $table->unique(['user_id', 'is_default']);
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        // Dropping the new columns if we roll back
        $table->dropColumn('full_name');
        $table->dropColumn('is_default');

        // Dropping the unique constraint
        $table->dropUnique(['user_id', 'is_default']);
    });
}

};
