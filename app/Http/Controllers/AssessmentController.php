<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentShareCode;
use App\Models\AssessmentAccess; 
use Illuminate\Support\Str;
use App\Models\PersonDictionary;
use App\Models\PersonDictionaryAccess;
use App\Models\OmrSheet;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssessmentController extends Controller
{

    public function index()
    {
        return Inertia::render('Assessments/index');
    }

    public function view($id)
    {
        $user = Auth::user();

        // Check if the user has access to the assessment
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access) {
            abort(403, 'You do not have access to this assessment.');
        }

        // Load the assessment
        $assessment = Assessment::findOrFail($id);

        // Decode all JSON columns
        $assessment->answer_key = json_decode($assessment->answer_key, true);
        $assessment->answers = json_decode($assessment->answers, true);
        $assessment->person_dictionary_snapshot = json_decode($assessment->person_dictionary_snapshot, true);
        $assessment->omr_sheet_snapshot = json_decode($assessment->omr_sheet_snapshot, true);

        return Inertia::render('Assessments/view', [
            'assessment' => $assessment,
            'access_level' => $access->access_level,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sheet' => 'nullable|integer',
            'respondents' => 'nullable|integer',
        ]);

        // Load and snapshot the OMR sheet
        $omrSnapshot = null;
        $defaultAnswerKey = [];

        if (!empty($validated['sheet'])) {
            $sheet = OmrSheet::where('owner_id', $user->id)
                ->where('id', $validated['sheet'])
                ->first();

            if (!$sheet) {
                return response()->json(['error' => 'OMR Sheet not found or not owned by user.'], 404);
            }

            $omrSnapshot = $sheet->json_data;

            // Build the default answer key
            $omrData = json_decode($sheet->json_data, true);
            if (!empty($omrData['OMRSheet']['MCQ'])) {
                foreach ($omrData['OMRSheet']['MCQ'] as $i => $block) {
                    $blockId = "mcq_block_" . ($i + 1);

                    $defaultAnswerKey[$blockId] = [
                        'x' => $block['x'] ?? 0,
                        'y' => $block['y'] ?? 0,
                        'numberOfChoices' => $block['choices'] ?? 4,
                        'answers' => [],
                    ];

                    for ($item = 1; $item <= $block['items']; $item++) {
                        $defaultAnswerKey[$blockId]['answers']["$item"] = [0];
                    }
                }
            }
        }

        // Load and snapshot the person dictionary
        $dictionarySnapshot = null;
        if (!empty($validated['respondents'])) {
            $hasAccess = PersonDictionaryAccess::where('user_id', $user->id)
                ->where('person_dictionary_id', $validated['respondents'])
                ->exists();

            if (!$hasAccess) {
                return response()->json(['error' => 'You do not have access to the selected respondent group.'], 403);
            }

            $dictionary = PersonDictionary::find($validated['respondents']);

            $decoded = json_decode($dictionary->persons_data, true); // Ensure it's decoded

            $dictionarySnapshot = json_encode([
                'name' => $dictionary->name,
                'meta_columns' => $decoded['meta_columns'] ?? [],
                'data' => $decoded['data'] ?? [],
            ]);
        }else{
            $dictionarySnapshot = json_encode([
                'name' => "N/A",
                'meta_columns' => ['Id', 'Name'],
                'data' => []
            ]);
        }

        // Create the assessment with the generated default structure
        $assessment = Assessment::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'omr_sheet_snapshot' => $omrSnapshot,
            'person_dictionary_snapshot' => $dictionarySnapshot,
            'answer_key' => json_encode((object)$defaultAnswerKey),
            'answers' => json_encode((object)[]),
        ]);

        // Assign ownership
        AssessmentAccess::create([
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'access_level' => 'owner',
        ]);

        return response()->json([
            'message' => 'Assessment created successfully.',
            'assessment' => $assessment,
        ]);
    }



    function getAssessments(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);

        $assessments = AssessmentAccess::with('assessment')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($assessments);
    }


    public function saveAnswer(Request $request, $id)
    {
        $user = $request->user();

        $validated = $request->validate([
            'answer_key' => 'required|array',
        ]);

        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'editor'])) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $assessment = Assessment::findOrFail($id);

        // Decode the snapshot to access MCQ definitions
        $snapshot = json_decode($assessment->omr_sheet_snapshot, true);
        $mcqBlocks = collect($snapshot['OMRSheet']['MCQ'] ?? [])->keyBy('id');

        $enrichedAnswerKey = [];

        foreach ($validated['answer_key'] as $blockId => $blockData) {
            if (!isset($blockData['answers'])) {
                continue;
            }

            $choicesCount = $mcqBlocks[$blockId]['choices'] ?? 4;

            $enrichedAnswerKey[$blockId] = [
                'x' => $blockData['x'] ?? 0,
                'y' => $blockData['y'] ?? 0,
                'numberOfChoices' => $choicesCount,
                'answers' => [],
            ];

            foreach ($blockData['answers'] as $item => $values) {
                $enrichedAnswerKey[$blockId]['answers'][(int)$item] = is_array($values) ? $values : [0];
            }
        }

        // Save enriched answer key
        $assessment->answer_key = json_encode($enrichedAnswerKey);
        $assessment->save();

        // Decode for evaluation
        $answers = json_decode($assessment->answers, true);
        $answerKey = $enrichedAnswerKey;

        // Re-evaluate MCQ scores
        foreach ($answers as &$response) {
            foreach ($response['answers']['mcq'] as &$block) {
                $blockId = $block['id'];
                $answerKeyBlock = $answerKey[$blockId] ?? null;

                if (!$answerKeyBlock) continue;

                $score = 0;

                foreach ($block['bubbles'] as $rowIndex => &$row) {
                    $questionNumber = $rowIndex + 1; // 1-based for answerKey
                    $correctAnswers = $answerKeyBlock['answers'][$questionNumber] ?? [];

                    // Get shaded choices (1-based)
                    $shadedChoices = [];
                    foreach ($row as $colIndex => $bubble) {
                        if (!isset($bubble['shaded'])) {
                            $bubble['shaded'] = false;
                        }
                        if ($bubble['shaded']) {
                            $shadedChoices[] = $colIndex + 1;
                        }
                    }

                    // Evaluate each bubble
                    foreach ($row as $colIndex => &$bubble) {
                        $choiceIndex = $colIndex + 1;
                        $shaded = $bubble['shaded'] ?? false;
                        $bubble['shaded'] = $shaded; // Explicitly ensure it is preserved

                        if (count($shadedChoices) === 1) {
                            $bubble['isCorrect'] = $shaded && in_array($choiceIndex, $correctAnswers);
                        } else {
                            $bubble['isCorrect'] = false;
                        }
                    }

                    // Score if only one shaded and it's correct
                    if (count($shadedChoices) === 1 && in_array($shadedChoices[0], $correctAnswers)) {
                        $score++;
                    }
                }

                $block['score'] = $score;
            }
        }

        // Save updated answers
        $assessment->answers = json_encode($answers);
        $assessment->save();

        return response()->json(['message' => 'Answer key saved and responses evaluated.']);
    }




    //Recording asnwers
    public function recording(Request $request, $id)
    {
        $user = $request->user();

        $access = AssessmentAccess::with('assessment')
            ->where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->firstOrFail();

        $assessment = $access->assessment;

        // Decode JSON columns so Blade can use them directly
        $assessment->answer_key = json_decode($assessment->answer_key, true);
        $assessment->person_dictionary_snapshot = json_decode($assessment->person_dictionary_snapshot, true);
        $assessment->omr_sheet_snapshot = json_decode($assessment->omr_sheet_snapshot, true);

        return view('camera', [
            'assessment' => $assessment,
        ]);
    }


    public function saveRecord(Request $request, $id)
    {
        $user = $request->user();
        $code = $request->input('code'); // optional assistant code
        $scoredBlocks = $request->input('scoredBlocks');
        $personId = $request->input('personId');
        $personName = $request->input('personName');

        $assessment = Assessment::findOrFail($id);

        // Check authenticated user access
        $hasAccess = false;

        if ($user) {
            $access = AssessmentAccess::where('user_id', $user->id)
                ->where('assessment_id', $id)
                ->first();

            if ($access && in_array($access->access_level, ['owner', 'admin', 'editor'])) {
                $hasAccess = true;
            }
        }

        // If no user access, check for valid assistant code
        if (!$hasAccess && $code) {
            $shareCode = \App\Models\AssessmentShareCode::where('assessment_id', $id)
                ->where('code', $code)
                ->where('enabled', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($shareCode) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Decode person dictionary snapshot
        $snapshotRaw = $assessment->person_dictionary_snapshot;
        $snapshot = json_decode($snapshotRaw, true);

        if (!$snapshot || !isset($snapshot['data']) || !is_array($snapshot['data'])) {
            return response()->json(['message' => 'Invalid person dictionary snapshot'], 422);
        }

        $metaColumns = $snapshot['meta_columns'] ?? [];
        if (is_string($metaColumns)) {
            $metaColumns = json_decode($metaColumns, true);
        }

        if (!is_array($metaColumns)) {
            return response()->json(['message' => 'Invalid meta_columns format'], 422);
        }

        // Generate new ID if personId is missing
        if (empty($personId)) {
            $existingIds = collect($snapshot['data'])->pluck('Id')->map(fn($id) => (int) $id)->filter()->all();
            $personId = count($existingIds) ? max($existingIds) + 1 : 1;
            $personId = (string) $personId;
        }

        // Check if person already exists
        $foundPerson = collect($snapshot['data'])->first(function ($row) use ($personId) {
            return (string) ($row['Id'] ?? '') === (string) $personId;
        });

        // Add new person if not found
        if (!$foundPerson) {
            $newPerson = ['Id' => $personId, 'Name' => $personName];
            foreach ($metaColumns as $column) {
                if (!in_array($column, ['Id', 'Name'])) {
                    $newPerson[$column] = null;
                }
            }

            $snapshot['data'][] = $newPerson;
            $assessment->person_dictionary_snapshot = json_encode($snapshot);
            $foundPerson = $newPerson;
        }

        // Merge metadata
        $personData = [
            'id' => $personId,
            'name' => $personName,
        ];
        foreach ($metaColumns as $column) {
            if (!in_array($column, ['Id', 'Name'])) {
                $personData[$column] = $foundPerson[$column] ?? null;
            }
        }

        // Decode answers
        $existingAnswers = $assessment->answers;
        if (is_string($existingAnswers)) {
            $existingAnswers = json_decode($existingAnswers, true);
        }
        if (!is_array($existingAnswers)) {
            $existingAnswers = [];
        }

        // Add new answer
        $existingAnswers[] = [
            'person' => $personData,
            'answers' => $scoredBlocks,
        ];

        $assessment->answers = $existingAnswers;
        $assessment->save();

        return response()->json([
            'message' => 'Record saved successfully',
            'personId' => $personId,
            'personName' => $personName,
        ]);
    }

    public function updateRespondents(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'persons' => 'required|array',
            'id' => 'required|integer'
        ]);

        $user = $request->user();

        $access = AssessmentAccess::with('assessment')
            ->where('user_id', $user->id)
            ->where('assessment_id', $request->input('id'))
            ->firstOrFail();

        $assessment = $access->assessment;

        // Decode snapshot and answers
        $snapshot = json_decode($assessment->person_dictionary_snapshot ?? '{}', true);
        $snapshot['meta_columns'] = $snapshot['meta_columns'] ?? ['Id', 'Name'];
        $snapshot['data'] = $snapshot['data'] ?? [];

        $answers = json_decode($assessment->answers ?? '[]', true);

        // Logging: Initial state
        Log::debug('Incoming request', $request->only('columns', 'persons', 'id'));
        Log::debug('Original snapshot', $snapshot);
        Log::debug('Original answers', $answers);

        $existingColumns = $snapshot['meta_columns'];
        $incomingColumns = $request->input('columns');

        // ✅ Flatten nested 'data' if it exists
        $incomingPersons = collect($request->input('persons'))->map(function ($p) {
            $flattened = $p['data'] ?? $p;
            $flattened['id'] = (string)($flattened['Id'] ?? $flattened['id'] ?? '');
            return array_change_key_case($flattened, CASE_LOWER); // normalize all keys to lowercase
        });

        // Logging: After flattening and normalization
        Log::debug('Normalized incoming persons', $incomingPersons->toArray());

        // Determine column changes
        $columnsToAdd = array_diff($incomingColumns, $existingColumns);
        $columnsToRemove = array_diff($existingColumns, $incomingColumns);
        $finalColumns = array_values(array_diff(
            array_merge($existingColumns, $columnsToAdd),
            $columnsToRemove
        ));

        Log::debug('Columns', [
            'existing' => $existingColumns,
            'incoming' => $incomingColumns,
            'to_add' => $columnsToAdd,
            'to_remove' => $columnsToRemove,
            'final' => $finalColumns
        ]);

        // Update snapshot data
        $snapshot['meta_columns'] = $finalColumns;

        foreach ($snapshot['data'] as &$person) {
            $match = $incomingPersons->firstWhere('id', (string)($person['Id'] ?? $person['id'] ?? null));

            if ($match) {
                // Update all fields based on final columns
                foreach ($finalColumns as $col) {
                    $person[$col] = $match[strtolower($col)] ?? null;
                }
            }

            foreach ($columnsToRemove as $col) {
                unset($person[$col]);
            }
        }

        unset($person);

        Log::debug('Updated snapshot', $snapshot);


        // Update each person in answers
        foreach ($answers as &$entry) {
            $entryId = (string)($entry['person']['id'] ?? $entry['person']['Id'] ?? null);
            if (!$entryId) continue;

            $match = $incomingPersons->firstWhere('id', $entryId);

            if ($match) {
                $personUpdate = [];

                foreach ($finalColumns as $col) {
                    $lowerCol = strtolower($col);
                    if (array_key_exists($lowerCol, $match)) {
                        $personUpdate[$lowerCol] = $match[$lowerCol];
                    }
                }

                $entry['person'] = $personUpdate;

                Log::debug('Updated answer entry', [
                    'person_id' => $entryId,
                    'updated_person' => $entry['person']
                ]);
            } else {
                foreach ($columnsToRemove as $col) {
                    unset($entry['person'][strtolower($col)]);
                }
            }
        }
        unset($entry);

        unset($entry);

        Log::debug('Final saved snapshot', ['snapshot' => $snapshot]);
        Log::debug('Final saved answers', ['answers' => $answers]);

        // Save back
        $assessment->person_dictionary_snapshot = json_encode($snapshot);
        $assessment->answers = json_encode($answers);
        $assessment->save();

        return response()->json([
            'message' => 'Dictionary updated successfully.',
            'all' => $answers,
        ]);
    }


    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:assessments,id',
        ]);

        $assessment = Assessment::findOrFail($request->id);

        // Check if the user has access to delete this assessment
        $access = AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'editor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the assessment and its access records
        $assessment->delete();
        AssessmentAccess::where('assessment_id', $assessment->id)->delete();

        return response()->json(['message' => 'Assessment deleted successfully']);
    }

    public function deleteRecord(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',     // assessment ID
            'index' => 'required|integer',  // index of respondent in answers array
        ]);

        $user = $request->user();

        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $request->id)
            ->firstOrFail();

        $assessment = $access->assessment;

        // Decode answers JSON
        $answers = json_decode($assessment->answers, true);

        // Check index bounds
        if (!isset($answers[$request->index])) {
            return response()->json(['message' => 'Invalid index'], 404);
        }

        // Remove the respondent at the given index
        array_splice($answers, $request->index, 1);

        // Save updated array
        $assessment->answers = json_encode($answers);
        $assessment->save();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    public function deleteUnrecordedRespondent(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|integer',
            'person_id' => 'required|string',
        ]);

        $user = $request->user();

        // Check access
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $request->assessment_id)
            ->firstOrFail();

        $assessment = $access->assessment;

        // Decode person dictionary snapshot
        $snapshot = json_decode($assessment->person_dictionary_snapshot, true);

        if (!$snapshot || !isset($snapshot['data'])) {
            return response()->json(['message' => 'Invalid person dictionary snapshot'], 422);
        }

        // Filter out the person with the given ID
        $snapshot['data'] = array_values(array_filter($snapshot['data'], function ($person) use ($request) {
            return (string)($person['Id'] ?? '') !== (string)$request->person_id;
        }));

        // Save updated snapshot
        $assessment->person_dictionary_snapshot = json_encode($snapshot);
        $assessment->save();

        return response()->json([
            'message' => 'Unrecorded respondent deleted successfully',
            'person_dictionary_snapshot' => $snapshot
        ]);
    }

    public function downloadSheet(Request $request, $id)
    {
        $user = $request->user();

        // Check access
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access) {
            abort(403, 'You do not have access to this assessment.');
        }

        // Get assessment
        $assessment = Assessment::findOrFail($id);

        if (!$assessment->omr_sheet_snapshot) {
            abort(404, 'OMR Sheet snapshot not found.');
        }

        // Decode snapshot
        $snapshot = $assessment->omr_sheet_snapshot;

        // Wrap into an object and attach fields
        $omrSheet = new \stdClass();
        $omrSheet->json_data = ($snapshot);
        $omrSheet->paper_size = $snapshot['OMRSheet']['Format'] ?? 'Unknown';

        return view('omr_sheet_view', compact('omrSheet'));
    }


    public function regenerateShareCode(Request $request, $id)
    {
        $user = $request->user();

        // Check access
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'admin'])) {
            abort(403, 'You do not have permission to regenerate share codes for this assessment.');
        }

        $validated = $request->validate([
            'expires_in_minutes' => 'nullable|integer|min:1',
            'enabled' => 'nullable|boolean',
        ]);

        // Disable all previous share codes for this assessment
        AssessmentShareCode::where('assessment_id', $id)->update(['enabled' => false]);

        // Generate new code
        $newCode = Str::random(10);

        // Calculate expiration if provided
        $expiresAt = isset($validated['expires_in_minutes'])
            ? Carbon::now()->addMinutes((int) $validated['expires_in_minutes'])
            : null;

        // Create new share code
        $shareCode = AssessmentShareCode::create([
            'assessment_id' => $id,
            'user_id' => $user->id,
            'code' => $newCode,
            'expires_at' => $expiresAt,
            'enabled' => $validated['enabled'] ?? true,
        ]);

        return response()->json([
            'message' => 'Share code regenerated successfully.',
            'code' => $newCode,
        ]);
    }
    public function getCode(Request $request, $id)
    {
        $user = $request->user();

        // Verify that the user has access to the assessment
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'admin'])) {
            abort(403, 'You do not have permission to access this assessment\'s share code.');
        }

        // Retrieve the latest share code for the given assessment
        $latestShareCode = AssessmentShareCode::where('assessment_id', $id)
            ->latest()
            ->first(['code', 'enabled', 'expires_at', 'created_at']);

        return response()->json([
            'share_code' => $latestShareCode ?? null,
        ]);
    }

    public function disableSharing(Request $request, $id)
    {
        $user = $request->user();

        // Check if the user has permission to disable the code
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'admin'])) {
            abort(403, 'You do not have permission to disable sharing for this assessment.');
        }

        // Disable all active share codes for the assessment
        AssessmentShareCode::where('assessment_id', $id)
            ->where('enabled', true)
            ->update(['enabled' => false]);

        return response()->json(['message' => 'Sharing disabled.']);
    }

    public function recordingAssistantView($id, $code)
    {
        // Check if a valid, enabled share code exists for this assessment
        $shareCode = AssessmentShareCode::where('assessment_id', $id)
            ->where('code', $code)
            ->where('enabled', true)
            ->when(now(), function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
                });
            })
            ->first();

        if (!$shareCode) {
            abort(403, 'Invalid or expired assistant code.');
        }

        // Load the related assessment
        $assessment = $shareCode->assessment;

        // Decode JSON columns for use in Blade
        $assessment->answer_key = json_decode($assessment->answer_key, true);
        $assessment->person_dictionary_snapshot = json_decode($assessment->person_dictionary_snapshot, true);
        $assessment->omr_sheet_snapshot = json_decode($assessment->omr_sheet_snapshot, true);

        return view('camera', [
            'assessment' => $assessment,
            'assistant' => true,
            'code'=> $code,
        ]);
    }
}
