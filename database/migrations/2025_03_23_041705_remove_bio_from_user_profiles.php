<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to drop the 'bio' column.
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }

    /**
     * Reverse the migration (optional, if you want to re-add the column).
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->text('bio')->nullable();
        });
    }
};
