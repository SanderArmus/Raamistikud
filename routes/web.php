<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Auth and guest routes must stay outside the auth-protected group to avoid
// redirect loops when visiting login/register pages.
require __DIR__.'/auth.php';

Route::middleware(['auth','verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('/markers', [MarkerController::class, 'index'])->name('markers.index');
    Route::post('/markers', [MarkerController::class, 'store'])->name('markers.store');
    Route::get('/markers/{marker}', [MarkerController::class, 'show'])->name('markers.show');
    Route::put('/markers/{marker}', [MarkerController::class, 'update'])->name('markers.update');
    Route::delete('/markers/{marker}', [MarkerController::class, 'destroy'])->name('markers.destroy');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    require __DIR__.'/settings.php';
    require __DIR__.'/posts.php';
    require __DIR__.'/authors.php';
    require __DIR__.'/comments.php';
    Route::get("test", function () {
        return 'tere';
    });
});
