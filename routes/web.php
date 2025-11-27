<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::middleware(['auth','verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    require __DIR__.'/settings.php';
    require __DIR__.'/auth.php';
    require __DIR__.'/posts.php';
    require __DIR__.'/authors.php';
    require __DIR__.'/comments.php';
    Route::get("test", function () {
        return 'tere';
    });
});
