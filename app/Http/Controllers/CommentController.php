<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
            'author_id' => $post->author_id,
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }
}
