<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\PersonDictionary;
use App\Models\PersonDictionaryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OmrSheet;

class DictionaryController extends Controller
{
    //Views
    public function index(){
        return Inertia::render('Dictionary/index');
    }

    public function view(Request $request, $id)
    {
        $user = $request->user();

        // Check if user has access to the dictionary
        $access = PersonDictionaryAccess::where('user_id', $user->id)
            ->where('person_dictionary_id', $id)
            ->first();

        if (!$access) {
            abort(403, 'You do not have access to this dictionary.');
        }

        // Load dictionary
        $dictionary = PersonDictionary::findOrFail($id);

        // Decode combined persons_data
        $personsData = $dictionary->persons_data ? json_decode($dictionary->persons_data, true) : [
            'meta_columns' => [],
            'data' => [],
        ];

        return Inertia::render('Dictionary/view', [
            'dictionary' => [
                'id' => $dictionary->id,
                'name' => $dictionary->name,
                'description' => $dictionary->description,
                'meta_columns' => $personsData['meta_columns'] ?? [],
                'persons' => collect($personsData['data'] ?? [])->map(function ($data, $i) {
                    return [
                        'data' => $data,
                    ];
                }),
            ],
            'access_level' => $access->access_level,
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'persons' => 'required|array',
            'id'=>'required'
        ]);

        DB::transaction(function () use ($request) {
            $dictionary = PersonDictionary::findOrFail($request->id);

            // Structure the persons_data field to include both meta_columns and person data
            $dictionary->persons_data = json_encode([
                'meta_columns' => $request->columns,
                'data' => collect($request->persons)
                    ->map(fn($p) => $p['data'])
                    ->values()
                    ->all(),
            ]);

            $dictionary->save();
        });

        return response()->json(['message' => 'Dictionary updated successfully.']);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:person_dictionaries,id'
        ]);

        DB::transaction(function () use ($request) {
            $dictionary = PersonDictionary::findOrFail($request->id);
            $dictionary->delete();
        });

        return response()->json([
            'message' => 'Dictionary deleted successfully'
        ]);
    }

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
            'persons_data' => json_encode([
                'meta_columns' => ['Id', 'Name'],
                'data' => [],
            ]),
            'member_count' => 0,
        ]);

        PersonDictionaryAccess::create([
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
