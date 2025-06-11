<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\OMRController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Dictionary
Route::get('/dictionary', function () {
    return Inertia::render('Dictionary/Dictionary');
})->middleware(['auth', 'verified'])->name('dictionary');
Route::get('api/user/dictionaries/get', [DictionaryController::class, 'getUserDictionaries'])->name('dictionaries');
Route::post('api/user/dictionaries/create', [DictionaryController::class, 'createDictionary'])->name('dictionary.create');

//Questionnaires
Route::get('/questionnaires', function () {
    return Inertia::render('Questionnaires/Questionnaires');
})->middleware(['auth', 'verified'])->name('questionnaires');
    Route::get('api/questionnaires/get', [OMRController::class, 'getUserOMRSheets'])->name('questionnaires.get');
    Route::post('api/questionnaires/create', [OMRController::class, 'createUserOMRSheet'])->name('questionnaire.create');

Route::get('/questionnaires/View/Create', function () {
    return view('omr');
})->middleware(['auth', 'verified'])->name('questionnaire.make');
//Assessment
Route::get('/assessments', function () {
    return Inertia::render('Assessments/Assessments');
})->middleware(['auth', 'verified'])->name('assessments');


Route::get('/questionnaires/paper', function () {
    return view('paper');
})->middleware(['auth', 'verified'])->name('questionnaire.makes');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
