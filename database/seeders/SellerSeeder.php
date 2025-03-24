<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerSeeder extends Seeder
{
    public function run()
    {
        // Email: craftsnwraps24@gmail.com 
        // Pass: jfpdcnw2024

        // Seller account
        User::updateOrCreate(
            ['email' => 'craftsnwraps24@gmail.com'],
            [
                'fname' => 'Crafts',
                'lname' => 'N Wraps',
                'username' => 'seller123',
                'password' => bcrypt('jfpdcnw2024'),
                'user_type' => 'seller',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
