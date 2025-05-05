<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add payment-related columns if they don't exist
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('orders', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the columns if they exist
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            
            if (Schema::hasColumn('orders', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
        });
    }
}