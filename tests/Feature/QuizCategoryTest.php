<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class QuizCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the functionality to view a list of quiz categories by the admin.
     *
     * @return void
     */
    public function test_admin_can_view_list_of_quiz_categories(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->getJson(route('quiz-categories.index'));

        $response->assertStatus(200);
    }

    /**
     * Test the functionality to create a quiz category with valid data by the admin.
     *
     * @return void
     */
    public function test_admin_can_create_quiz_category_with_valid_data(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->make();

        $response = $this->actingAs($this->user)->postJson(route('quiz-categories.store'), [
            'title' => $quizCategory->title,
            'slug' => $quizCategory->slug,
        ]);

        $responseQuizCategory = QuizCategory::latest()->first();

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->has(2)
                    ->first(
                        fn (AssertableJson $json) => $json->has(3)
                            ->where('id', $responseQuizCategory->id)
                            ->where('title', $responseQuizCategory->title)
                            ->where('slug', $responseQuizCategory->slug)
                    )
                    ->etc()
        );

        $this->assertDatabaseCount('quiz_categories', 1);
    }

    /**
     * Test the functionality to prevent the admin from creating a quiz category with an invalid title.
     *
     * @return void
     */
    public function test_admin_cannot_create_quiz_category_with_invalid_title(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->make();

        $response = $this->actingAs($this->user)->postJson(route('quiz-categories.store'), [
            'title' => 100,
            'slug' => $quizCategory->slug,
        ]);

        $response->assertUnprocessable();
    }

    /**
     * Test the functionality to prevent the admin from creating a quiz category with an invalid slug.
     *
     * @return void
     */
    public function test_admin_cannot_create_quiz_category_with_invalid_slug(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->make();

        $response = $this->actingAs($this->user)->postJson(route('quiz-categories.store'), [
            'title' => $quizCategory->title,
            'slug' => 'abcd/1234',
        ]);

        $response->assertUnprocessable();
    }

    /**
     * Test the functionality to prevent the admin from creating a quiz category with a duplicate slug.
     *
     * @return void
     */
    public function test_admin_cannot_create_quiz_category_with_duplicate_slug(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();

        $response = $this->actingAs($this->user)->postJson(route('quiz-categories.store'), [
            'title' => $quizCategory->title,
            'slug' => $quizCategory->slug,
        ]);

        $response->assertUnprocessable();
    }

    /**
     * Test the functionality to view a single quiz category by the admin with a valid quiz category ID.
     *
     * @return void
     */
    public function test_admin_can_view_single_quiz_category_with_valid_quiz_category_id(): void
    {
        $this->createAdminUser();
        $quizCategory = QuizCategory::factory()->create();

        $response = $this->actingAs($this->user)->getJson(route('quiz-categories.show', $quizCategory));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data', 3)
                    ->where('data.id', $quizCategory->id)
                    ->where('data.title', $quizCategory->title)
                    ->where('data.slug', $quizCategory->slug)
        );
    }

    /**
     * Test the functionality to update a quiz category by the admin with a valid quiz category ID.
     *
     * @return void
     */
    public function test_admin_can_update_quiz_category_with_valid_quiz_category_id(): void
    {
        $this->createAdminUser();
        $quizCategory = QuizCategory::factory()->create();
        $newQuizCategory = QuizCategory::factory()->make();

        $response = $this->actingAs($this->user)->putJson(route('quiz-categories.update', $quizCategory), [
            'title' => $newQuizCategory->title,
            'slug' => $newQuizCategory->slug,
        ]);

        $response->assertStatus(200)
            ->assertJson(
            fn (AssertableJson $json) => $json->has(2)
                ->first(
                    fn (AssertableJson $json) => $json->has(3)
                        ->where('id', $quizCategory->id)
                        ->where('title', $newQuizCategory->title)
                        ->where('slug', $newQuizCategory->slug)
                )
                ->etc()
        );
    }

    /**
     * Test the functionality to delete a quiz category by the admin with a valid quiz category ID.
     *
     * @return void
     */
    public function test_admin_can_delete_quiz_category_with_valid_quiz_category_id(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson(route('quiz-categories.destroy', $quizCategory));

        $response->assertStatus(204);
        $this->assertEquals(QuizCategory::count(), 0);
    }

    /**
     * Test that quiz category deletion fails if the category is attached to a quiz.
     *
     * @return void
     */
    public function test_quiz_category_deletion_fails_if_category_is_attached_to_quiz(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();

        $quiz = Quiz::factory()->create();
        Storage::delete($quiz->thumbnail);

        $response = $this->actingAs($this->user)->deleteJson(route('quiz-categories.destroy', $quizCategory));

        $response->assertStatus(200)->assertJson(
            fn (AssertableJson $json) => $json->has('error')
                ->where('error', 'Could not delete the category.')
        );
        $this->assertEquals(QuizCategory::count(), 1);
    }
    /**
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_view_list_of_quiz_categories(): void
    {
        $response = $this->getJson(route('quiz-categories.index'));

        $response->assertStatus(401);
    }

    /**
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_quiz_category(): void
    {
        $quizCategory = QuizCategory::factory()->make();

        $response = $this->postJson(route('quiz-categories.store'), [
            'title' => $quizCategory->title,
            'slug' => $quizCategory->slug,
        ]);

        $response->assertStatus(401);
    }

    /**
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_update_quiz_category(): void
    {
        $quizCategory = QuizCategory::factory()->create();
        $newQuizCategory = QuizCategory::factory()->make();

        $response = $this->putJson(route('quiz-categories.update', $quizCategory), [
            'title' => $newQuizCategory->title,
            'slug' => $newQuizCategory->slug,
        ]);

        $response->assertStatus(401);
    }
    
    /**
     *
     * @return void
     */
    public function test_quiz_category_cannot_be_deleted_if_associated_with_quiz(): void
    {
        $this->createAdminUser();

        $quizCategory = QuizCategory::factory()->create();

        $quiz = Quiz::factory()->create(['category_id' => $quizCategory->id]);
        Storage::delete($quiz->thumbnail);

        $response = $this->actingAs($this->user)->deleteJson(route('quiz-categories.destroy', $quizCategory));

        $response->assertStatus(200)
                 ->assertJson(
                    fn (AssertableJson $json) => $json->has('error')
                                                        ->where('error', 'Could not delete the category.')
                 );

        $this->assertDatabaseHas('quiz_categories', ['id' => $quizCategory->id]);
    }

}
