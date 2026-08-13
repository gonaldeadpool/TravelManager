<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ViaggioController;
use App\Http\Controllers\AmministrazioneController;
use App\Http\Controllers\PraticaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

Route::get('/viaggi/search', [ViaggioController::class, 'search'])
    ->middleware(['auth'])
    ->name('viaggi.search');

Route::get('/viaggi/{viaggio}/locandina', [ViaggioController::class, 'downloadLocandina'])
    ->middleware(['auth'])
    ->name('viaggi.locandina');

Route::get('/calendario', [CalendarioController::class, 'index'])
    ->middleware(['auth'])
    ->name('calendario');

Route::get('/calendario/eventi', [CalendarioController::class, 'eventi'])
    ->middleware(['auth'])
    ->name('calendario.eventi');

Route::resource('viaggi', ViaggioController::class)
    ->middleware(['auth'])
    ->parameters(['viaggi' => 'viaggio']);

Route::post('/viaggi/{viaggio}/posti', [ViaggioController::class, 'assegnaPosto'])
    ->middleware(['auth'])
    ->name('viaggi.posti.store');

Route::get('/pratiche/creazione/clienti', [PraticaController::class, 'selectClientiCreazione'])
    ->middleware(['auth'])
    ->name('pratiche.creazione.clienti.select');

Route::post('/pratiche/creazione/bozza', [PraticaController::class, 'storeBozzaCreazione'])
    ->middleware(['auth'])
    ->name('pratiche.creazione.bozza');

Route::post('/pratiche/creazione/clienti', [PraticaController::class, 'storeClientiCreazione'])
    ->middleware(['auth'])
    ->name('pratiche.creazione.clienti.store');

Route::get('/pratiche/search', [PraticaController::class, 'search'])
    ->middleware(['auth'])
    ->name('pratiche.search');

Route::resource('pratiche', PraticaController::class)
    ->middleware(['auth'])
    ->parameters(['pratiche' => 'pratica'])
    ->except(['show']);

Route::get('/pratiche/{pratica}/clienti', [PraticaController::class, 'selectClienti'])
    ->middleware(['auth'])
    ->name('pratiche.clienti.select');

Route::post('/pratiche/{pratica}/clienti', [PraticaController::class, 'storeClienti'])
    ->middleware(['auth'])
    ->name('pratiche.clienti.store');

Route::delete('/pratiche/{pratica}/clienti/{cliente}', [PraticaController::class, 'destroyCliente'])
    ->middleware(['auth'])
    ->name('pratiche.clienti.destroy');

Route::get('/pratiche/{pratica}/documenti/{documento}', [PraticaController::class, 'downloadDocument'])
    ->middleware(['auth'])
    ->name('pratiche.documenti.download');

Route::post('/pratiche/{pratica}/documenti', [PraticaController::class, 'storeDocument'])
    ->middleware(['auth'])
    ->name('pratiche.documenti.store');

Route::delete('/pratiche/{pratica}/documenti/{documento}', [PraticaController::class, 'destroyDocument'])
    ->middleware(['auth'])
    ->name('pratiche.documenti.destroy');

Route::get('/amministrazione', [AmministrazioneController::class, 'edit'])
    ->middleware(['auth'])
    ->name('amministrazione');

Route::resource('utenti', UserManagementController::class)
    ->middleware(['auth'])
    ->parameters(['utenti' => 'utente'])
    ->except(['show']);

Route::put('/amministrazione', [AmministrazioneController::class, 'update'])
    ->middleware(['auth'])
    ->name('amministrazione.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
