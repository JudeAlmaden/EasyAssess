<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentAccess; // Ensure this is imported

use App\Models\PersonDictionary;
use App\Models\PersonDictionaryAccess;
use App\Models\OmrSheet;

class AssessmentController extends Controller
{

    public function index(){
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
                        'answers' => [],
                    ];

                    for ($item = 1; $item <= $block['items']; $item++) {
                        // Set default answer to [0]
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
            $dictionarySnapshot = $dictionary->persons_data;
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



    function getAssessments(Request $request) {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);

        $assessments = AssessmentAccess::with('assessment')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($assessments);
    }

    public function saveAnswer(Request $request, $id){
        $user = $request->user();

        // Validate structure of incoming data
        $validated = $request->validate([
            'answer_key' => 'required|array',
        ]);

        // Check user access
        $access = AssessmentAccess::where('user_id', $user->id)
            ->where('assessment_id', $id)
            ->first();

        if (!$access || !in_array($access->access_level, ['owner', 'editor'])) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Update the assessment
        $assessment = Assessment::findOrFail($id);
        $assessment->answer_key = json_encode($validated['answer_key']);
        $assessment->save();

        return response()->json(['message' => 'Answer key saved successfully.']);
    }
}