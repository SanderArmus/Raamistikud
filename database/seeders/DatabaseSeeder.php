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
        $email = 'test@test.ee';
        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            $existing->update([
                'name' => 'Test User',
                'password' => Hash::make($email),
                'is_admin' => true,
            ]);
        } else {
            User::factory()->create([
                'name' => 'Test User',
                'email' => $email,
                'password' => Hash::make($email),
                'is_admin' => true,
            ]);
        }

        $this->call([
            AuthorSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            CommentSeeder::class,
            PostSeeder::class,
        ]);
    }


}
