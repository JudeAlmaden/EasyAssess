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
            'description' => 'nullable|string',
            'paper_size'=> 'required|string',
            'HTML' => 'required|string',
            'fileJSON' => 'required|json',
        ]);

        
        OmrSheet::create([
            'owner_id' => $request->user()->id,
            'title' => $validated['title'],
            "paper_size" => $validated ['paper_size'],
            'json_data' => json_decode($validated['fileJSON'], true), 
            'description'=>$validated['description'],
            'html_content' => $validated['HTML'],
        ]);

        return response()->json([
            'message' => 'OMR Sheet created successfully',
        ]);
    }

}
