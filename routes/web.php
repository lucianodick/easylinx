<?php

use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LibraryVersionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::delete('dashboard/clear-logs', [DashboardController::class, 'clearLogs'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.clear-logs');

// Rotas de gerenciamento de bibliotecas e versões
Route::middleware(['auth', 'verified'])->group(function () {
    // Bibliotecas
    Route::resource('libraries', LibraryController::class)->except(['create', 'edit']);
    
    // Versões de uma biblioteca específica
    Route::post('libraries/{library}/versions', [LibraryVersionController::class, 'store'])->name('libraries.versions.store');
    Route::put('libraries/{library}/versions/{version}', [LibraryVersionController::class, 'update'])->name('libraries.versions.update');
    Route::delete('libraries/{library}/versions/{version}', [LibraryVersionController::class, 'destroy'])->name('libraries.versions.destroy');
});

require __DIR__.'/settings.php';
