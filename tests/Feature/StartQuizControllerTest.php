<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\QuestionCategory;
use App\Models\QuizCategory;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Carbon;

class StartQuizControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @return void
     */
    public function test_authenticated_student_can_attempt_quiz(): void
    {
        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(1)->create()->pluck('id')->toArray();

        Question::factory(12)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '5'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '10'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '15'
        ]);

        $quiz = Quiz::factory()->create();

        $quiz->questionCategories()->attach($questionCategoryIds);

        Storage::delete($quiz->thumbnail);

        $student = User::factory()->create();

        $response = $this->actingAs($student)->getJson(route('start-quiz', $quiz));

        // dd($response->json());

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', $quiz->id)
                            ->where('title', $quiz->title)
                            ->where('slug', $quiz->slug)
                            ->where('thumbnail', asset($quiz->thumbnail))
                            ->where('description', $quiz->description)
                            ->where('time', $quiz->time)
                            ->where('retry_after', $quiz->retry_after)
                            ->where('status', $quiz->status)
                            ->where('pass_percentage', $quiz->pass_percentage)
                            ->has(
                                'category',
                                fn (AssertableJson $json) => $json->where('id', $quizCategory->id)
                                    ->where('title', $quizCategory->title)
                                    ->where('slug', $quizCategory->slug)
                            )
                            ->has(
                                'questions.data',
                                fn (AssertableJson $json) => $json->has(
                                    'questions',
                                    fn (AssertableJson $json) => $json->each(
                                        fn (AssertableJson $json) => $json->whereAllType([
                                            'options' => 'array',
                                            'weightage' => 'integer',
                                            'status' => 'integer'
                                        ])->etc()
                                    )
                                )->where('count', 14)
                            )
                    )
            );
    }

    /**
     * @return void
     */
    public function test_unauthenticated_student_cannnot_attempt_quiz(): void
    {
        $quizCategory = QuizCategory::factory()->create();
        $quiz = Quiz::factory()->create([
            'category_id' => $quizCategory
        ]);
        Storage::delete($quiz->thumbnail);
        $response = $this->getJson(route('start-quiz', $quiz));

        $response->assertStatus(401);
    }

    /**
     * @return void
     */
    public function test_passed_quiz_cannot_be_reattempted(): void
    {
        $user = User::factory()->create();
        $quizCategory = QuizCategory::factory()->create();
        $quiz = Quiz::factory()->create([
            'category_id' => $quizCategory,
            'retry_after' => 2
        ]);
        Storage::delete($quiz->thumbnail);
        Result::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'passed' => true,
            'created_at' => now()->subDays(6),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('start-quiz', $quiz));

        $response->assertJson([
            'message' => "You've already passed this quiz and cannot reattempt it.",
        ]);
    }

    /**
     * @return void
     */
    public function test_failed_quiz_can_be_reattempted_after_retry_period(): void
    {
        $user = User::factory()->create();
        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(1)->create()->pluck('id')->toArray();

        Question::factory(12)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '5'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '10'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '15'
        ]);

        $quiz = Quiz::factory()->create([
            'retry_after' => 5,
        ]);

        $quiz->questionCategories()->attach($questionCategoryIds);

        Storage::delete($quiz->thumbnail);

        Result::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'passed' => false,
            'created_at' => now()->subDays(6),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('start-quiz', $quiz));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', $quiz->id)
                            ->where('title', $quiz->title)
                            ->where('slug', $quiz->slug)
                            ->where('thumbnail', asset($quiz->thumbnail))
                            ->where('description', $quiz->description)
                            ->where('time', $quiz->time)
                            ->where('retry_after', $quiz->retry_after)
                            ->where('status', $quiz->status)
                            ->where('pass_percentage', $quiz->pass_percentage)
                            ->has(
                                'category',
                                fn (AssertableJson $json) => $json->where('id', $quizCategory->id)
                                    ->where('title', $quizCategory->title)
                                    ->where('slug', $quizCategory->slug)
                            )
                            ->etc()
                    )
            );
    }

    /**
     * @return void
     */
    public function test_failed_quiz_cannot_be_reattempted_before_retry_period(): void
    {
        $user = User::factory()->create();
        $quizCategory = QuizCategory::factory()->create();
        $quiz = Quiz::factory()->create([
            'retry_after' => 5,
            'category_id' => $quizCategory
        ]);
        Storage::delete($quiz->thumbnail);

        $result = Result::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'passed' => false,
            'created_at' => now()->subDays(4),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('start-quiz', $quiz));

        $response->assertJsonFragment([
            'message' => "You can reattempt this quiz after " . $result->created_at->addDays($quiz->retry_after)->diffForHumans(),
        ]);
    }

    /**
     * @return void
     */
    public function test_inactive_quiz_cannot_be_started(): void
    {
        QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(1)->create()->pluck('id')->toArray();

        Question::factory(12)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '5'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '10'
        ]);
        Question::factory(3)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '15'
        ]);

        $quiz = Quiz::factory()->create([
            'status' => false
        ]);

        $quiz->questionCategories()->attach($questionCategoryIds);

        Storage::delete($quiz->thumbnail);

        $student = User::factory()->create();

        $response = $this->actingAs($student)->getJson(route('start-quiz', $quiz));

        $response->assertStatus(403)
            ->assertJson(['message' => 'This quiz is currently not available for attempts.']);
    }

    /**
     * @return void
     */
    public function test_quiz_without_total_14_questions_cannot_be_started(): void
    {
        QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(1)->create()->pluck('id')->toArray();

        Question::factory(8)->create([
            'category_id' => $questionCategoryIds[0],
            'weightage' => '5'
        ]);

        $quiz = Quiz::factory()->create();

        $quiz->questionCategories()->attach($questionCategoryIds);

        Storage::delete($quiz->thumbnail);

        $student = User::factory()->create();

        $response = $this->actingAs($student)->getJson(route('start-quiz', $quiz));

        $response->assertStatus(403)
            ->assertJson(['message' => 'Quiz is not available now. Please try again later.']);
    }

    /**
     * @return void
     */
    public function test_invalid_quiz_id_returns_not_found(): void
    {
        $invalidQuizId = 9999;
        $response = $this->actingAs(User::factory()->create())
                        ->getJson(route('start-quiz', $invalidQuizId));

        $response->assertStatus(404);
    }

    /**
     * @return void
     */
    public function test_admin_cannot_start_quiz(): void
    {
        $this->createAdminUser();

        $quizcategory = QuizCategory::factory()->create();

        $quiz = Quiz::factory()->create(['category_id' => $quizcategory->id]);

        $response = $this->actingAs($this->user)->getJson(route('start-quiz', $quiz));

        $response->assertStatus(403)
            ->assertJson([ 'message' => 'Quiz is not available now. Please try again later.']);
    }

    /**
     * @return void
     */
    public function test_quiz_cannot_be_started_before_scheduled_start_time(): void
    {
        $quizCategory = QuizCategory::factory()->create();
        $scheduledStartTime = Carbon::now()->addHours(1);
        $quiz = Quiz::factory()->create([
            'category_id' => $quizCategory->id,
            'time' => $scheduledStartTime,
        ]);
        $student = User::factory()->create();
        $response = $this->actingAs($student)->getJson(route('start-quiz', $quiz));
        $response->assertStatus(403)
            ->assertJson([ 'message' => 'Quiz is not available now. Please try again later.']);
    }
}
