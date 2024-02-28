<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quizCategoriesIDs = DB::table('quiz_categories')->pluck('id');

        return [
            'title' => fake()->sentence(),
            'slug' => fake()->slug(),
            'category_id' => fake()->randomElement($quizCategoriesIDs),
            'thumbnail' => str_replace(storage_path('app'), '', fake()->image(storage_path('/app/images/quizzes'), 1440, 640, 'cats', true)),
            'description' => fake()->paragraph(),
            'time' => fake()->numberBetween(15, 60),
            'retry_after' => fake()->numberBetween(0, 7),
            'status' => 1,
            'pass_percentage' => 50,
        ];
    }
}
