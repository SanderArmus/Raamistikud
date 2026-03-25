<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marker extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'added',
        'edited',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'added' => 'datetime',
        'edited' => 'datetime',
    ];

    public $timestamps = false;
}

