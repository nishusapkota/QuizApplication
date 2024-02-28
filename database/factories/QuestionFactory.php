<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "category_id" => $this->faker->randomElement([1,2,3]),
            "description" => $this->faker->sentence(),
            "title" => $this->faker->sentence(),
            "slug" => $this->faker->slug,
            "options" => ["A","B","C","D"],
            "answer" => "A",
            "weightage" => $this->faker->randomElement(['5','10','15'])
        ];
    }
}
