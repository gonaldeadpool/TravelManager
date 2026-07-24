<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/clienti', [ClienteController::class, 'index'])
    ->middleware(['auth'])
    ->name('clienti');

Route::get('/clienti/nuovo', [ClienteController::class, 'create'])
    ->middleware(['auth'])
    ->name('clienti.create');

Route::post('/clienti', [ClienteController::class, 'store'])
    ->middleware(['auth'])
    ->name('clienti.store');

Route::view('/viaggi', 'viaggi')
    ->middleware(['auth'])
    ->name('viaggi');

Route::view('/pratiche', 'pratiche')
    ->middleware(['auth'])
    ->name('pratiche');

Route::view('/amministrazione', 'amministrazione')
    ->middleware(['auth'])
    ->name('amministrazione');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
