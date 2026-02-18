<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('salons', SalonController::class);
    Route::post('/salons/{salon}/join', [SalonController::class, 'join'])->name('salons.join');
    Route::post('/salons/{salon}/leave', [SalonController::class, 'leave'])->name('salons.leave');
    
    Route::post('/salons/{salon}/game/start', [GameController::class, 'start'])->name('game.start');
    Route::post('/games/{game}/select-ticket', [GameController::class, 'selectTicket'])->name('game.selectTicket');
    Route::get('/games/{game}/status', [GameController::class, 'getStatus'])->name('game.status');
    
    // Test broadcast
    Route::get('/test-broadcast/{salon}', function(\App\Models\Salon $salon) {
        broadcast(new \App\Events\UserJoinedSalon(auth()->user(), $salon));
        return 'Broadcast sent!';
    });
});

require __DIR__.'/auth.php';
