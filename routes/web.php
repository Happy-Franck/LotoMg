<?php

use App\Http\Controllers\MessageController;
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
    
    Route::get('/salons/{salon}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/salons/{salon}/messages', [MessageController::class, 'store'])->name('messages.store');
});

require __DIR__.'/auth.php';
