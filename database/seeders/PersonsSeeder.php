<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonDictionary;
use App\Models\PersonDictionaryAccess;

class PersonsSeeder extends Seeder
{
    public function run()
    {
        // Create some dictionaries
        $dict1 = PersonDictionary::create([
            'name' => 'Grade 10 - Section A',
            'description' => 'This is the Grade 10 Section A group.',
            'member_count' => 30,
        ]);

        $dict2 = PersonDictionary::create([
            'name' => 'Grade 11 - Section B',
            'description' => 'This is the Grade 11 Section B group.',
            'member_count' => 25,
        ]);

        // Create access entries (adjust user_id to your actual users)
        PersonDictionaryAccess::create([
            'user_id' => 1,
            'person_dictionary_id' => $dict1->id,
            'access_level' => 'admin',
        ]);

        PersonDictionaryAccess::create([
            'user_id' => 2,
            'person_dictionary_id' => $dict1->id,
            'access_level' => 'read',
        ]);

        PersonDictionaryAccess::create([
            'user_id' => 1,
            'person_dictionary_id' => $dict2->id,
            'access_level' => 'write',
        ]);
    }
}
