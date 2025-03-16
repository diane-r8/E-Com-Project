<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('two_factor_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('otp'); // ✅ This column is required
            $table->timestamp('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('two_factor_codes');
    }
};