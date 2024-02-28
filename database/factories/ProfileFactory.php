<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'education' => $this->faker->sentence,
            'skills' => json_encode(['PHP', 'Laravel', 'JavaScript']),
            'experience' => $this->faker->sentence,
            'career' => $this->faker->word,
            'avatar' => str_replace(storage_path('app'), '', fake()->image(storage_path('/app/images/profiles'), 1440, 640, 'human', true)),
        ];
    }
}
