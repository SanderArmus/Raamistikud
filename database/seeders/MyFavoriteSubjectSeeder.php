<?php

namespace Database\Seeders;

use App\Models\MyFavoriteSubject;
use Illuminate\Database\Seeder;

class MyFavoriteSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'The Matrix',
                'image' => 'https://picsum.photos/seed/matrix/600/400',
                'description' => 'A hacker discovers reality is a simulation.',
                'director' => 'Lana Wachowski',
                'release_year' => 1999,
                'genre' => 'Sci-Fi',
            ],
            [
                'title' => 'Inception',
                'image' => 'https://picsum.photos/seed/inception/600/400',
                'description' => 'A thief enters dreams to steal secrets.',
                'director' => 'Christopher Nolan',
                'release_year' => 2010,
                'genre' => 'Sci-Fi',
            ],
            [
                'title' => 'Interstellar',
                'image' => 'https://picsum.photos/seed/interstellar/600/400',
                'description' => 'A journey beyond our galaxy to save humanity.',
                'director' => 'Christopher Nolan',
                'release_year' => 2014,
                'genre' => 'Sci-Fi',
            ],
            [
                'title' => 'The Dark Knight',
                'image' => 'https://picsum.photos/seed/dark-knight/600/400',
                'description' => 'Batman faces the Joker in Gotham City.',
                'director' => 'Christopher Nolan',
                'release_year' => 2008,
                'genre' => 'Action',
            ],
            [
                'title' => 'Parasite',
                'image' => 'https://picsum.photos/seed/parasite/600/400',
                'description' => 'A poor family infiltrates a wealthy household.',
                'director' => 'Bong Joon-ho',
                'release_year' => 2019,
                'genre' => 'Thriller',
            ],
            [
                'title' => 'Spirited Away',
                'image' => 'https://picsum.photos/seed/spirited-away/600/400',
                'description' => 'A girl enters a magical world of spirits.',
                'director' => 'Hayao Miyazaki',
                'release_year' => 2001,
                'genre' => 'Animation',
            ],
            [
                'title' => 'The Lord of the Rings: The Fellowship of the Ring',
                'image' => 'https://picsum.photos/seed/lotr-fellowship/600/400',
                'description' => 'A hobbit begins a quest to destroy a powerful ring.',
                'director' => 'Peter Jackson',
                'release_year' => 2001,
                'genre' => 'Fantasy',
            ],
            [
                'title' => 'Pulp Fiction',
                'image' => 'https://picsum.photos/seed/pulp-fiction/600/400',
                'description' => 'Interwoven stories of crime in Los Angeles.',
                'director' => 'Quentin Tarantino',
                'release_year' => 1994,
                'genre' => 'Crime',
            ],
            [
                'title' => 'Whiplash',
                'image' => 'https://picsum.photos/seed/whiplash/600/400',
                'description' => 'A drummer is pushed to the limit by an intense teacher.',
                'director' => 'Damien Chazelle',
                'release_year' => 2014,
                'genre' => 'Drama',
            ],
            [
                'title' => 'Mad Max: Fury Road',
                'image' => 'https://picsum.photos/seed/mad-max/600/400',
                'description' => 'A relentless chase across the wasteland.',
                'director' => 'George Miller',
                'release_year' => 2015,
                'genre' => 'Action',
            ],
        ];

        foreach ($items as $data) {
            MyFavoriteSubject::query()->updateOrCreate(
                [
                    'title' => $data['title'],
                    'director' => $data['director'],
                    'release_year' => $data['release_year'],
                ],
                [
                    'image' => $data['image'],
                    'description' => $data['description'],
                    'genre' => $data['genre'],
                ],
            );
        }
    }
}

