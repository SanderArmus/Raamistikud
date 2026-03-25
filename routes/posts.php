<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

    Route::resource('posts', PostController::class)->middleware(['auth', 'verified']);

    Route::post('/posts/bulk-delete', [PostController::class, 'bulkDestroy'])->name('posts.bulk-delete');

    Route::post('/posts/delete-all', [PostController::class, 'deleteAll'])->name('posts.delete-all');
    
