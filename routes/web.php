<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\OMRController;
use App\Http\Controllers\AssessmentController;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Dictionary
Route::get('/dictionary', [DictionaryController::class, 'index'] )->middleware(['auth', 'verified'])->name('dictionaries');
Route::get('/dictionary/{id}', [DictionaryController::class, 'view'])->middleware(['auth', 'verified'])->name('dictionary.view');

Route::get('api/user/dictionaries/get', [DictionaryController::class, 'getUserDictionaries'])->name('dictionaries.get');
Route::post('api/user/dictionaries/create', [DictionaryController::class, 'createDictionary'])->name('dictionary.create');
Route::post('api/dictionaries/', [DictionaryController::class, 'update'])->name('dictionary.update');

//Questionnaires
Route::get('/omr-sheets', [OMRController::class, 'index'])->middleware(['auth', 'verified'])->name('omr_sheets');
Route::get('/omr-sheets/{id}', [OMRController::class, 'view'])->middleware(['auth', 'verified'])->name('omr_sheet.view');

Route::get('/questionnaires/create', [OMRController::class, 'create'])->middleware(['auth', 'verified'])->name('questionnaire.make');
Route::get('api/omr-sheets/get', [OMRController::class, 'getUserOMRSheets'])->name('omr_sheets.get');
Route::post('api/omr-sheets/create', [OMRController::class, 'createUserOMRSheet'])->name('omr_sheet.create');


//Assessment
Route::get('/assessments', [AssessmentController::class, 'index'])->middleware(['auth', 'verified'])->name('assessments');
Route::get('/assessments/{id}', [AssessmentController::class, 'view'])->middleware(['auth', 'verified'])->name('assessment.view');

Route::get('api/user/assessments/get', [AssessmentController::class, 'getAssessments'])->middleware(['auth', 'verified'])->name('assessments.get');
Route::post('api/assessments/create', [AssessmentController::class, 'create'])->middleware(['auth', 'verified'])->name('assessment.create');
Route::post('/api/assessments/{id}/save-answer-key', [AssessmentController::class, 'saveAnswer'])->middleware(['auth', 'verified'])->name('answers.save');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
