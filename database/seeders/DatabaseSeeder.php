<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Review;
use App\Models\Author;
use App\Models\Post;
use App\Models\Comment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.ee',
            'password' => Hash::make('test@test.ee'),
        ]);

        $this->call([
            AuthorSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            CommentSeeder::class,
            PostSeeder::class,
        ]);
    }


}
