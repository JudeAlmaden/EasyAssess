<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DictionaryController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dictionary', function () {
    return Inertia::render('Dictionary/Dictionary');
})->middleware(['auth', 'verified'])->name('dictionary');

Route::get('/questionnaires', function () {
    return Inertia::render('Questionnaires/Questionnaires');
})->middleware(['auth', 'verified'])->name('questionnaires');

Route::get('/assessments', function () {
    return Inertia::render('Assessments/Assessments');
})->middleware(['auth', 'verified'])->name('assessments');


//
Route::get('api/user/dictionaries/get', [DictionaryController::class, 'getUserDictionaries'])->name('User.dictionaries');
Route::post('api/user/dictionaries/create', [DictionaryController::class, 'createDictionary'])->name('User.dictionary.create');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
