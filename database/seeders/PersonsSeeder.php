<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonDictionary;
use App\Models\PersonDictionaryAccess;
use App\Models\User; // Don't forget to import the User model
use Illuminate\Support\Facades\Hash; // Import the Hash facade for password hashing

class PersonsSeeder extends Seeder
{
    public function run()
    {
        // Create a default user
        $user1 = User::create([
            'name' => 'Justine',
            'email' => 'judealmaden2045@gmail.com',
            'password' => Hash::make('Password123'), // Always hash passwords!
            'email_verified_at' => now(), // Optionally set email as verified
        ]);

        // You can create another user if needed for testing access levels
        $user2 = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}