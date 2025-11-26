<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Author;
class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('posts/Index', [
            'posts' => Post::with('author:id,first_name,last_name')->paginate(30),
        ]);
    }

    public function create()
    {

        return Inertia::render('posts/Create', [
            'authors' => Author::all()->mapWithKeys(fn($author) => [$author->id => $author->first_name . ' ' . $author->last_name]),
        ]);
    }

    public function store(Request $request)
    {
        Post::create($request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'published' => 'boolean',
        ]));
        return redirect()->route('posts.index');


    }

    public function show(Post $post)
    {
        return Inertia::render('posts/View', [
            'post' => $post,
        ]);
    }

    public function edit(Post $post)
    {
        return Inertia::render('posts/Edit', [
            'post' => $post->load('author:id,first_name,last_name'),
            'authors' => Author::all()->mapWithKeys(fn($author) => [$author->id => $author->first_name . ' ' . $author->last_name]),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $post->update($request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'published' => 'boolean',
        ]));
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->back()->with('success', 'Postitus kustutatud.');
    }


}
