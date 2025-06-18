<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OmrSheet;
use Inertia\Inertia;

class OMRController extends Controller
{
    function index(){
        return Inertia::render('OMR/index');
    }

    function view (Request $request, $id) {
        $user = $request->user();
        $omrSheet = OmrSheet::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();
        return view('omr_sheet_view', compact('omrSheet'));
    }

    function create() {
        return view('omr_create');
    }
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
            'description' => 'nullable|string',
            'fileJSON' => 'required|json',
        ]);

        OmrSheet::create([
            'owner_id' => $request->user()->id,
            'title' => $validated['title'],
            'json_data' => $validated['fileJSON'], 
            'description'=>$validated['description'],
        ]);

        return response()->json([
            'message' => 'OMR Sheet created successfully',
        ]);
    }

}
