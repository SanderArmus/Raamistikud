<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasFormatedDate;
class Post extends Model
{

    use HasFactory;
    use HasFormatedDate;

    protected $fillable = [
        'title',
        'content',
        'author_id',
        'published',
    ];

   protected $appends = [
    'created_at_formatted',
    'updated_at_formatted',
];


    protected function createdAtFormatted(): Attribute
    {
       return Attribute::make(
            get: fn() => $this->created_at?->diffForHumans()
        );
    }

    protected function updatedAtFormatted(): Attribute
    {
       return Attribute::make(
            get: fn() => $this->updated_at?->diffForHumans()
        );
    }


    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
