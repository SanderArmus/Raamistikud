<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFormatedDate;
class Comment extends Model
{
    use HasFactory;
    use HasFormatedDate;
    protected $fillable = [
        'user_id',
        'post_id',
        'author_id',
        'content',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
