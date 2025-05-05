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
        // Approach 1: Try dropping the unique constraint directly
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'is_default']);
            });
        } catch (\Exception $e) {
            // If direct drop fails, try alternative approaches
            
            // Approach 2: Drop the index by name
            try {
                DB::statement('ALTER TABLE orders DROP INDEX orders_user_id_is_default_unique');
            } catch (\Exception $e) {
                // Approach 3: Find and drop using information schema
                try {
                    // Get constraint name from information schema
                    $constraintName = DB::selectOne("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.TABLE_CONSTRAINTS
                        WHERE TABLE_NAME = 'orders'
                        AND CONSTRAINT_TYPE = 'UNIQUE'
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    ");
                    
                    if ($constraintName && isset($constraintName->CONSTRAINT_NAME)) {
                        DB::statement("ALTER TABLE orders DROP INDEX {$constraintName->CONSTRAINT_NAME}");
                    }
                } catch (\Exception $e) {
                    // Log the failure but allow migration to continue
                    error_log("Failed to drop unique constraint on orders table: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Don't re-add the constraint, as it's causing issues
    }
};