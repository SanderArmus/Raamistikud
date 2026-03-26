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

    private function userIsAdmin(Request $request): bool
    {
        return (bool) ($request->user()?->is_admin ?? false);
    }

    private function canEditPost(Request $request, Post $post): bool
    {
        if ($this->userIsAdmin($request)) {
            return true;
        }

        $author = $this->ensureAuthorForUser($request);

        return (string) $post->author_id === (string) $author->id;
    }

    public function index(Request $request)
    {
        $isAdmin = $this->userIsAdmin($request);
        $author = $this->ensureAuthorForUser($request);

        return Inertia::render('posts/Index', [
            'posts' => tap(
                Post::query()
                    ->select(['id', 'title', 'description', 'author_id', 'created_at', 'updated_at'])
                    ->paginate(30),
                function ($paginator) use ($isAdmin, $author) {
                    $paginator->getCollection()->transform(function (Post $post) use ($isAdmin, $author) {
                        $canEdit = $isAdmin || (string) $post->author_id === (string) $author->id;

                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'created_at' => $post->created_at,
                            'updated_at' => $post->updated_at,
                            'created_at_formatted' => $post->created_at_formatted,
                            'updated_at_formatted' => $post->updated_at_formatted,
                            'can_edit' => $canEdit,
                        ];
                    });
                }
            ),
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

    public function show(Request $request, Post $post)
    {
        return Inertia::render('posts/View', [
            'post' => $post->loadMissing([
                'comments.user',
            ]),
            'can_edit' => $this->canEditPost($request, $post),
        ]);
    }

    public function edit(Request $request, Post $post)
    {
        if (! $this->canEditPost($request, $post)) {
            abort(403, 'Only the post author (or admin) can edit this post.');
        }

        return Inertia::render('posts/Edit', [
            'post' => $post,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        if (! $this->canEditPost($request, $post)) {
            abort(403, 'Only the post author (or admin) can edit this post.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $post->update([
            ...$validated,
        ]);
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if (! $this->userIsAdmin($request)) {
            abort(403, 'Only admins can delete posts.');
        }

        $post->delete();

        return redirect()->back()->with('success', 'Postitus kustutatud.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        if (! $this->userIsAdmin($request)) {
            abort(403, 'Only admins can delete posts.');
        }

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        Post::query()->whereIn('id', $validated['ids'])->delete();

        return redirect()->route('posts.index')->with('success', 'Posts deleted.');
    }

    public function deleteAll(Request $request): RedirectResponse
    {
        if (! $this->userIsAdmin($request)) {
            abort(403, 'Only admins can delete posts.');
        }

        Post::query()->delete();

        return redirect()->route('posts.index')->with('success', 'All posts deleted.');
    }


}
