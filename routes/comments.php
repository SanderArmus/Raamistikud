<?php

use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');

