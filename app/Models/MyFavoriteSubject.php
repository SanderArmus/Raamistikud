<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyFavoriteSubject extends Model
{
    protected $table = 'my_favorite_subjects';

    protected $fillable = [
        'title',
        'image',
        'description',
        'director',
        'release_year',
        'genre',
    ];

    protected $casts = [
        'release_year' => 'integer',
    ];
}

