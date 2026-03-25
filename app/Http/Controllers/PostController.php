<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\Author;
class PostController extends Controller
{
    private function ensureAuthorForUser(Request $request): Author
    {
        $user = $request->user();

        $name = trim((string) ($user?->name ?? ''));
        $parts = $name !== '' ? preg_split('/\s+/', $name) : [];
        $firstName = $parts[0] ?? 'Unknown';
        $lastName = $parts[1] ?? ($parts[count($parts) - 1] ?? 'Unknown');

        // Authors table requires date_of_birth; we use a safe default.
        $defaultDob = now()->subYears(30)->toDateString();

        return Author::query()->firstOrCreate(
            ['user_id' => $user?->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $defaultDob,
            ],
        );
    }

    public function index()
    {
        return Inertia::render('posts/Index', [
            'posts' => Post::query()
                ->select(['id', 'title', 'description', 'created_at', 'updated_at'])
                ->paginate(30),
        ]);
    }

    public function create()
    {
        return Inertia::render('posts/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $author = $this->ensureAuthorForUser($request);

        Post::create([
            ...$validated,
            'author_id' => $author->id,
        ]);

        return redirect()->route('posts.index');


    }

    public function show(Post $post)
    {
        return Inertia::render('posts/View', [
            'post' => $post->loadMissing([
                'comments.user',
            ]),
        ]);
    }

    public function edit(Post $post)
    {
        return Inertia::render('posts/Edit', [
            'post' => $post,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Keep posts owned by the current user.
        $author = $this->ensureAuthorForUser($request);

        $post->update([
            ...$validated,
            'author_id' => $author->id,
        ]);
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->back()->with('success', 'Postitus kustutatud.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        Post::query()->whereIn('id', $validated['ids'])->delete();

        return redirect()->route('posts.index')->with('success', 'Posts deleted.');
    }

    public function deleteAll(): RedirectResponse
    {
        Post::query()->delete();

        return redirect()->route('posts.index')->with('success', 'All posts deleted.');
    }


}
