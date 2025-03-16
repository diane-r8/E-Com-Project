<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'fname' => 'Admin',
            'lname' => 'User',
            'username' => 'admin',
            'email' => 'cnwray14@gmail.com',
            'password' => Hash::make('cnwrayadmins'), // Hash the password
            'user_type' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
