<?php

use App\Http\Controllers\MyFavoriteSubjectApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| JSON API for "my_favorite_subjects".
| Supports: filtering, sorting, limit, and search.
|
*/

Route::get('/my-favorite-subjects', [MyFavoriteSubjectApiController::class, 'index'])
    ->name('api.my-favorite-subjects.index');

