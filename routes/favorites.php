<?php

use App\Http\Controllers\MyFavoriteSubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/favorites', [MyFavoriteSubjectController::class, 'index'])->name('favorites.index');
Route::get('/favorites/create', [MyFavoriteSubjectController::class, 'create'])->name('favorites.create');
Route::post('/favorites', [MyFavoriteSubjectController::class, 'store'])->name('favorites.store');

