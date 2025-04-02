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
    Schema::create('product_variations', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Stores variation name (size, color, etc.)
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('product_variations');
}

};
