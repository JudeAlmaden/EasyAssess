<?php

namespace App\Http\Controllers;

use App\Models\PersonDictionary;
use Illuminate\Http\Request;
use App\Models\PersonDictionaryAccess;

class DictionaryController extends Controller
{
    public function getUserDictionaries(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $dictionaries = $user->personDictionaries()->get();

        return response()->json([
            'dictionaries' => $dictionaries,
        ]);
    }

    public function createDictionary(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $dictionary = PersonDictionary::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'member_count' => 0,
        ]);

        $dictionaryAccess = PersonDictionaryAccess::create([
            'user_id' => $user->id,
            'person_dictionary_id' => $dictionary->id,
            'access_level' => 'admin',
        ]);

        return response()->json([
            'message' => 'Dictionary created successfully.',
            'dictionary' => $dictionary,
        ]);
    }
}
