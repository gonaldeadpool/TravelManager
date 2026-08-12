<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ViaggioController;
use App\Http\Controllers\AmministrazioneController;
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

Route::get('/clienti/search', [ClienteController::class, 'search'])
    ->middleware(['auth'])
    ->name('clienti.search');

Route::get('/clienti/nuovo', [ClienteController::class, 'create'])
    ->middleware(['auth'])
    ->name('clienti.create');

Route::get('/clienti/{id}/modifica', [ClienteController::class, 'edit'])
    ->middleware(['auth'])
    ->name('clienti.edit');

Route::get('/clienti/{cliente}/documenti/{documento}', [ClienteController::class, 'downloadDocument'])
    ->middleware(['auth'])
    ->name('clienti.documenti.download');

Route::post('/clienti/{cliente}/documenti', [ClienteController::class, 'storeDocument'])
    ->middleware(['auth'])
    ->name('clienti.documenti.store');

Route::delete('/clienti/{cliente}/documenti/{documento}', [ClienteController::class, 'destroyDocument'])
    ->middleware(['auth'])
    ->name('clienti.documenti.destroy');

Route::put('/clienti/{id}', [ClienteController::class, 'update'])
    ->middleware(['auth'])
    ->name('clienti.update');

Route::delete('/clienti/{id}', [ClienteController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('clienti.destroy');

Route::post('/clienti', [ClienteController::class, 'store'])
    ->middleware(['auth'])
    ->name('clienti.store');

Route::resource('viaggi', ViaggioController::class)
    ->middleware(['auth'])
    ->parameters(['viaggi' => 'viaggio'])
    ->except(['show']);

Route::get('/viaggi/search', [ViaggioController::class, 'search'])
    ->middleware(['auth'])
    ->name('viaggi.search');

Route::get('/viaggi/{viaggio}/locandina', [ViaggioController::class, 'downloadLocandina'])
    ->middleware(['auth'])
    ->name('viaggi.locandina');

Route::view('/pratiche', 'pratiche')
    ->middleware(['auth'])
    ->name('pratiche');

Route::get('/amministrazione', [AmministrazioneController::class, 'edit'])
    ->middleware(['auth'])
    ->name('amministrazione');

Route::put('/amministrazione', [AmministrazioneController::class, 'update'])
    ->middleware(['auth'])
    ->name('amministrazione.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
