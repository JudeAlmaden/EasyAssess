<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\OMRController;
use App\Http\Controllers\AssessmentController;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Landing page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('landing');

//Dashboard
Route::get('Homepage', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Dictionary
Route::get('/dictionary', [DictionaryController::class, 'index'] )->middleware(['auth', 'verified'])->name('dictionaries');
Route::get('/dictionary/{id}', [DictionaryController::class, 'view'])->middleware(['auth', 'verified'])->name('dictionary.view');       //Singular view

Route::get('api/dictionaries/get', [DictionaryController::class, 'getUserDictionaries'])->name('dictionaries.get');                                 //All associated dictionaries
Route::post('api/dictionary/create', [DictionaryController::class, 'createDictionary'])->name('dictionary.create');
Route::post('api/dictionary/update', [DictionaryController::class, 'update'])->name('dictionary.update');
Route::post('api/dictionary/delete', [DictionaryController::class, 'delete'])->middleware(['auth', 'verified'])->name('dictionary.delete');

//Questionnaires
Route::get('/omr-sheets', [OMRController::class, 'index'])->middleware(['auth', 'verified'])->name('omr_sheets');
Route::get('/omr-sheet/{id}', [OMRController::class, 'view'])->middleware(['auth', 'verified'])->name('omr_sheet.view');                //Singular view
Route::get('/omr-sheet/create/tool', [OMRController::class, 'createView'])->middleware(['auth', 'verified'])->name('omr_sheet.make');    //Link for making a new questionnaire

Route::get('api/omr-sheets/get', [OMRController::class, 'getUserOMRSheets'])->name('omr_sheets.get');                        //All associated questionnaires    
Route::post('api/omr-sheet/create', [OMRController::class, 'createUserOMRSheet'])->name('omr_sheet.create');                                    
Route::post('api/omr-sheet/delete', [OMRController::class, 'delete'])->name('omr_sheet.delete');

//Assessment
Route::get('/assessments', [AssessmentController::class, 'index'])->middleware(['auth', 'verified'])->name('assessments');
Route::get('/assessments/{id}', [AssessmentController::class, 'view'])->middleware(['auth', 'verified'])->name('assessment.view');
Route::get('/assessments/{id}/record', [AssessmentController::class, 'recording'])->middleware(['auth', 'verified'])->name('assessment.record.view');
Route::get('/assessments/{id}/record/{code}', [AssessmentController::class, 'recordingAssistantView'])->name('assessment.record.assistant.view');

Route::get('/assessments/{id}/download', [AssessmentController::class, 'downloadSheet'])->middleware(['auth', 'verified'])->name('assessment.download');


Route::post('/assessments/{id}/sharing/generate', [AssessmentController::class, 'regenerateShareCode'])->middleware(['auth', 'verified'])->name('assessment.code.generate');
Route::post('/assessments/{id}/sharing/disable', [AssessmentController::class, 'disableSharing'])->middleware(['auth', 'verified'])->name('assessment.code.disable');
Route::post('/assessments/{id}/sharing', [AssessmentController::class, 'getCode'])->middleware(['auth', 'verified'])->name('assessment.code.get');


Route::get('/api/user/assessments/get', [AssessmentController::class, 'getAssessments'])->middleware(['auth', 'verified'])->name('assessments.get');    //all associated assessments
Route::post('/api/assessments/create', [AssessmentController::class, 'create'])->middleware(['auth', 'verified'])->name('assessment.create');           //Create and elete assessment
Route::post('/api/assessments/delete', [AssessmentController::class, 'delete'])->middleware(['auth', 'verified'])->name('assessment.delete');


Route::post('/api/assessments/{id}/save-answer-key', [AssessmentController::class, 'saveAnswer'])->middleware(['auth', 'verified'])->name('answers.save');
Route::post('/api/assessments/{id}/record', [AssessmentController::class, 'saveRecord'])->name('assessment.record.create');
Route::post('/api/dictionaries/record/delete', [AssessmentController::class, 'deleteRecord'])->middleware(['auth', 'verified'])->name('assessment.record.delete');
Route::post('/api/assessment/unrecorded/delete', [AssessmentController::class, 'deleteUnrecordedRespondent'])->middleware(['auth', 'verified'])->name('assessment.unrecorded.delete');
Route::post('/api/dictionaries/respondent/update', [AssessmentController::class, 'updateRespondents'])->middleware(['auth', 'verified'])->name('assessment.record.update');


//Payment 
// Route::get('/payment',function(){
    
//     return Inertia::render('Payment/index');
    
// } )->middleware(['auth', 'verified'])->name('payment');
// When payment feature is back, uncomment this section and the one in AppSidebar.vue, Uncomment The landing page pricing teaser, and the payment section in the user settings page


Route::fallback(function (Request $request) {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }else{
        return route(name: 'landing');
    }
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
