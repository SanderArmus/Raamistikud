<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Author;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */


class CommentFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'post_id' => Post::inRandomOrder()->first()->id ?? Post::factory(),
            'content' => $this->faker->sentence(),
            'author_id' => Author::factory(),
        ];
    }
}

