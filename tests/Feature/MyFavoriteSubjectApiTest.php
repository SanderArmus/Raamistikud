<?php

use App\Models\MyFavoriteSubject;
use App\Models\User;

it('supports limit', function () {
    MyFavoriteSubject::query()->create([
        'title' => 'A',
        'image' => null,
        'description' => 'Desc',
        'director' => 'Dir',
        'release_year' => 2000,
        'genre' => 'Drama',
    ]);
    MyFavoriteSubject::query()->create([
        'title' => 'B',
        'image' => null,
        'description' => 'Desc',
        'director' => 'Dir',
        'release_year' => 2001,
        'genre' => 'Drama',
    ]);

    $res = $this->getJson('/api/my-favorite-subjects?limit=1');
    $res->assertOk();
    $res->assertJsonCount(1, 'data');
});

it('supports search by title', function () {
    MyFavoriteSubject::query()->create([
        'title' => 'The Matrix',
        'image' => null,
        'description' => 'Desc',
        'director' => 'Lana Wachowski',
        'release_year' => 1999,
        'genre' => 'Sci-Fi',
    ]);
    MyFavoriteSubject::query()->create([
        'title' => 'Interstellar',
        'image' => null,
        'description' => 'Desc',
        'director' => 'Christopher Nolan',
        'release_year' => 2014,
        'genre' => 'Sci-Fi',
    ]);

    $res = $this->getJson('/api/my-favorite-subjects?search=matrix');
    $res->assertOk();
    expect($res->json('data'))->toHaveCount(1);
    expect($res->json('data.0.title'))->toBe('The Matrix');
});

it('supports sorting by release_year asc', function () {
    MyFavoriteSubject::query()->create([
        'title' => 'Old',
        'image' => null,
        'description' => 'Desc',
        'director' => 'X',
        'release_year' => 1990,
        'genre' => null,
    ]);
    MyFavoriteSubject::query()->create([
        'title' => 'New',
        'image' => null,
        'description' => 'Desc',
        'director' => 'X',
        'release_year' => 2020,
        'genre' => null,
    ]);

    $res = $this->getJson('/api/my-favorite-subjects?sort=release_year&direction=asc&limit=10');
    $res->assertOk();
    expect($res->json('data.0.release_year'))->toBe(1990);
    expect($res->json('data.1.release_year'))->toBe(2020);
});

it('invalidates api cache version after creating a new item', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $res1 = $this->getJson('/api/my-favorite-subjects?limit=50');
    $res1->assertOk();
    $v1 = (int) $res1->json('meta.cache_version');

    $this->actingAs($user)->post('/favorites', [
        'title' => 'Cache Test',
        'image' => '',
        'description' => 'Desc',
        'director' => 'Someone',
        'release_year' => 2022,
        'genre' => 'Drama',
    ])->assertRedirect('/favorites');

    $res2 = $this->getJson('/api/my-favorite-subjects?search=Cache%20Test&limit=50');
    $res2->assertOk();
    $v2 = (int) $res2->json('meta.cache_version');

    expect($v2)->toBeGreaterThan($v1);
    expect($res2->json('data.0.title'))->toBe('Cache Test');
});

