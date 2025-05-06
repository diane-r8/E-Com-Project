<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // First, set is_default = NULL for all orders where it's currently false (0)
        DB::statement('UPDATE orders SET is_default = NULL WHERE is_default = 0');
        
        // Drop the existing constraint - this should work now that we've removed the 0 values
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'is_default']);
        });
        
        // Add a new constraint that only applies when is_default is true
        Schema::table('orders', function (Blueprint $table) {
            // Make the column nullable first
            $table->boolean('is_default')->nullable()->change();
            
            // Add a partial unique index for true values only
            DB::statement('CREATE UNIQUE INDEX orders_user_id_true_unique ON orders (user_id) WHERE is_default = 1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the partial index
        DB::statement('DROP INDEX IF EXISTS orders_user_id_true_unique');
        
        // Set NULL values back to false
        DB::statement('UPDATE orders SET is_default = 0 WHERE is_default IS NULL');
        
        // Recreate the original constraint
        Schema::table('orders', function (Blueprint $table) {
            // Make it non-nullable again
            $table->boolean('is_default')->nullable(false)->default(false)->change();
            
            // Add back the original constraint
            $table->unique(['user_id', 'is_default']);
        });
    }
};