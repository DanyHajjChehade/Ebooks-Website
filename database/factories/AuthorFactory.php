<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'bio' => fake()->paragraph(3),
            'photo_path' => null,
        ];
    }
}
