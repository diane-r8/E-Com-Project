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
         Schema::create('categories', function (Blueprint $table) {
             $table->id();
             $table->string('name');
             $table->timestamps();
         });
     
         // Insert default categories
         DB::table('categories')->insert([
             ['name' => 'Birthday Souvenir', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Bracelets', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Bundle Deals & Promos', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Graduation', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Keychains', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Mother\'s Day', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Rings', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Teachers Day', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Wedding Essential', 'created_at' => now(), 'updated_at' => now()],
             ['name' => 'Bouquets', 'created_at' => now(), 'updated_at' => now()],
         ]);
     }
     
};
