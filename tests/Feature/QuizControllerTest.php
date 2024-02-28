<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\QuestionCategory;
use App\Models\QuizCategory;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class QuizControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_admin_can_view_list_of_quizzes(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $response = $this->actingAs($this->user)->getJson(route('quizzes.index'),);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data', 1)
                    ->has('links', 4)
                    ->has(
                        'meta',
                        fn (AssertableJson $json) => $json->where('per_page', 10)
                            ->etc()
                    )
            );
    }

    /**
     * @return void
     */
    public function test_admin_can_create_quiz_with_valid_data(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $response = $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', 1)
                            ->where('title', $data['title'])
                            ->where('slug', $data['slug'])
                            ->where('description', $data['description'])
                            ->where('time', $data['time'])
                            ->where('retry_after', $data['retry_after'])
                            ->where('status', $data['status'])
                            ->where('pass_percentage', $data['pass_percentage'])
                            ->etc()
                    )->where('message', "Successfully Created")
            );

        $this->assertDatabaseCount('quizzes', 1)
            ->assertDatabaseHas('quizzes', [
                'title' => 'Test Quiz',
                'slug' => 'test-quiz',
                'description' => 'This is a test quiz',
                'time' => 30,
                'retry_after' => 1,
                'status' => 1,
                'pass_percentage' => 70,
                'category_id' => 1,
            ]);

        $quiz = Quiz::where('title', 'Test Quiz')->first();
        $this->assertEquals($data['question_categories'], $quiz->questionCategories->pluck('id')->toArray());
        $this->assertDatabaseCount('question_category_quiz', 5);
        Storage::disk('local')->assertExists(str_replace('http://localhost', '', $quiz->thumbnail));
    }

    /**
     * @return void
     */
    public function test_admin_cannot_create_quiz_with_invalid_data(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();

        $data = [
            'title' => '',
            'slug' => '=-slug-34',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
        ];

        $response = $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function test_admin_can_view_single_quiz(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $quiz = Quiz::where('title', 'Test Quiz')->first();

        $response = $this->actingAs($this->user)->getJson(route('quizzes.show', $quiz));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data', 11)
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', $quiz->id)
                            ->where('title', $quiz->title)
                            ->where('slug', $quiz->slug)
                            ->where('description', $quiz->description)
                            ->where('time', $quiz->time)
                            ->where('retry_after', $quiz->retry_after)
                            ->where('status', $quiz->status)
                            ->where('pass_percentage', $quiz->pass_percentage)
                            ->has(
                                'category',
                                fn (AssertableJson $json) => $json->where('id', $quizCategory->id)
                                    ->where('title', $quizCategory->title)
                            )
                            ->has('question_categories', 5)
                            ->etc(['thumbnail'])
                    )
            );
    }

    /**
     * @return void
     */
    public function test_admin_can_update_quiz_with_valid_data(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $quiz = Quiz::where('title', 'Test Quiz')->first();

        $update_data = [
            'title' => 'Update Test Quiz title',
            'slug' => 'update-test-quiz',
            'description' => 'Update this is a test quiz description',
            'time' => 60,
            'retry_after' => 15,
            'status' => 1,
            'pass_percentage' => 90,
            'category_id' => $quizCategory->id,
            'question_categories' => [1, 3, 5],
        ];

        $response = $this->actingAs($this->user)->putJson(route('quizzes.update', $quiz), $update_data);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', 1)
                            ->where('title', $update_data['title'])
                            ->where('slug', $update_data['slug'])
                            ->where('description', $update_data['description'])
                            ->where('time', $update_data['time'])
                            ->where('retry_after', $update_data['retry_after'])
                            ->where('status', $update_data['status'])
                            ->where('pass_percentage', $update_data['pass_percentage'])
                            ->etc()
                    )->where('message', "Successfully Updated")
            );

        $this->assertDatabaseCount('quizzes', 1)
            ->assertDatabaseHas('quizzes', [
                'title' => $update_data['title'],
                'slug' => $update_data['slug'],
                'description' => $update_data['description'],
                'time' => $update_data['time'],
                'retry_after' => $update_data['retry_after'],
                'status' => $update_data['status'],
                'pass_percentage' => $update_data['pass_percentage'],
                'category_id' => $update_data['category_id'],
            ]);

        $this->assertDatabaseCount('question_category_quiz', 3);
    }

    /**
     * @return void
     */
    public function test_admin_can_update_quiz_with_valid_unchanged_data(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $quiz = Quiz::where('title', 'Test Quiz')->first();

        $response = $this->actingAs($this->user)->putJson(route('quizzes.update', $quiz), $data);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', 1)
                            ->where('title', $data['title'])
                            ->where('slug', $data['slug'])
                            ->where('description', $data['description'])
                            ->where('time', $data['time'])
                            ->where('retry_after', $data['retry_after'])
                            ->where('status', $data['status'])
                            ->where('pass_percentage', $data['pass_percentage'])
                            ->etc()
                    )->where('message', "No changes were made")
            );

        $this->assertDatabaseCount('quizzes', 1)
            ->assertDatabaseHas('quizzes', [
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'time' => $data['time'],
                'retry_after' => $data['retry_after'],
                'status' => $data['status'],
                'pass_percentage' => $data['pass_percentage'],
                'category_id' => $data['category_id'],
            ]);

        $this->assertDatabaseCount('question_category_quiz', 5);
    }

    /**
     * @return void
     */
    public function test_admin_can_delete_quiz_with_valid_quiz_id(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $quiz = Quiz::where('title', 'Test Quiz')->first();

        $response = $this->actingAs($this->user)->delete(route('quizzes.destroy', $quiz));

        $response->assertStatus(204);

        $this->assertEquals(Quiz::count(), 0);
        $this->assertDatabaseCount('question_category_quiz', 0);
    }

    /**
     * @return void
     */
    public function test_admin_cannnot_delete_quiz_with_non_existing_quiz_id(): void
    {
        Storage::fake('local');
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();
        $questionCategoryIds = QuestionCategory::factory(5)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $data = [
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'This is a test quiz',
            'time' => 30,
            'retry_after' => 1,
            'status' => 1,
            'pass_percentage' => 70,
            'category_id' => $quizCategory->id,
            'question_categories' => $questionCategoryIds,
            'thumbnail' => $thumbnail,
        ];

        $this->actingAs($this->user)->postJson(route('quizzes.store'), $data);

        $quiz = Quiz::where('title', 'Test Quiz')->first();

        $response = $this->actingAs($this->user)->deleteJson(route('quizzes.destroy', 2));

        $response->assertStatus(404);

        $this->assertEquals(Quiz::count(), 1);
        $this->assertDatabaseCount('question_category_quiz', 5);
    }
}
