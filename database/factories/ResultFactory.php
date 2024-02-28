<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quiz_ids = DB::table('quizzes')->pluck('id');
        $user_ids = DB::table('users')->pluck('id');

        return [
            'user_id' => $this->faker->randomElement($user_ids),
            'quiz_id' => $this->faker->randomElement($quiz_ids),
            'passed' => $this->faker->boolean,
            'total_question' => $this->faker->numberBetween(1, 14),
            'total_answered' => $this->faker->numberBetween(1, 14),
            'total_right_answer' => $this->faker->numberBetween(1, 14),
            'total_time' => $this->faker->numberBetween(1, 60),
        ];
    }
}
