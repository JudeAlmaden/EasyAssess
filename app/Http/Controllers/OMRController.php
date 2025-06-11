<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OmrSheet;
class OMRController extends Controller
{
    function getUserOMRSheets(Request $request){
        $user = $request->user();
        $omrSheets = OmrSheet::where('owner_id', $user->id)->get();

        return response()->json([
            'OMRSheets' => $omrSheets
        ]);
    }

    public function createUserOMRSheet(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sections' => 'nullable|array',
            'html_content' => 'nullable|string',
        ]);

        $omrSheet = OmrSheet::create([
            'owner_id' => $request->user()->id,
            'title' => $validated['title'],
            'sections' => $validated['sections'] ?? [],
            'html_content' => $validated['html_content'] ?? '',
        ]);

        return response()->json([
            'message' => 'OMR Sheet created successfully',
            'omr_sheet' => $omrSheet,
        ]);
    }
}
