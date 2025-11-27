<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->text(),
            'author_id' => Author::factory(),
            'published' => $this->faker->boolean(),
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Post $post) {
            \App\Models\Comment::factory()->count(5)->create([
                'post_id' => $post->id,
                'author_id' => $post->author_id,
            ]);
        });
    }

}

