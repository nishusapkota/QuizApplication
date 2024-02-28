<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\QuestionCategory;
use App\Models\Result;
use Illuminate\Testing\Fluent\AssertableJson;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case to verify that the admin can view the list of students.
     *
     * @return void
     */
    public function test_admin_can_view_list_of_students(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->get(route('students.index'));

        $response->assertStatus(200);
    }


    /**
     * Test case to verify that the admin can view student details with a valid student ID.
     *
     * @return void
     */
    public function test_admin_can_view_student_details_with_student_id(): void
    {
        $this->createAdminUser();

        $user = User::factory()->create();

        $profile = Profile::factory()->create([
            'user_id' => $user
        ]);

        Storage::delete($profile->avatar);

        QuizCategory::factory()->create();
        $questionCategory = QuestionCategory::factory()->create();

        $quiz = Quiz::factory()->create();
        Storage::delete($quiz->thumbnail);
        $quiz->questionCategories()->attach(QuestionCategory::first());

        $result = Result::factory()->create([
            'user_id' => $user, 'quiz_id' => $quiz
        ]);



        $response = $this->actingAs($this->user)->getJson(route('students.show', $user));

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->first(
                        fn (AssertableJson $json) => $json->where('id', $user->id)
                            ->where('name', $user->name)
                            ->where('email', $user->email)
                            ->where('role', 'student')
                            ->has(
                                'profile',
                                fn (AssertableJson $json) => $json->where('id', $profile->id)
                                    ->where('skills', "[\"PHP\",\"Laravel\",\"JavaScript\"]")
                                    ->where('education', $profile->education)
                                    ->where('experience', $profile->experience)
                                    ->where('career', $profile->career)
                                    ->where('avatar_url', $profile->getAvatarUrlAttribute())
                            )
                            ->has(
                                'results.0',
                                fn (AssertableJson $json) => $json->where('id', $result->id)
                                    ->where('passed', $result->passed == 0 ? 0 : 1)
                                    ->where('total_question', $result->total_question)
                                    ->where('total_answered', $result->total_answered)
                                    ->where('total_right_answer', $result->total_right_answer)
                                    ->where('total_time', $result->total_time)
                                    ->has(
                                        'quiz_id',
                                        fn (AssertableJson $json) => $json->where('id', $quiz->id)
                                            ->where('title', $quiz->title)
                                            ->where('slug', $quiz->slug)
                                            ->where('thumbnail', $quiz->getThumbnailUrlAttribute())
                                            ->where('description', $quiz->description)
                                            ->where('time', $quiz->time)
                                            ->where('retry_after', $quiz->retry_after)
                                            ->where('status', $quiz->status)
                                            ->where('pass_percentage', $quiz->pass_percentage)
                                            ->has(
                                                'question_categories.0',
                                                fn (AssertableJson $json) => $json->where('id', $questionCategory->id)
                                                    ->where('title', $questionCategory->title)
                                                    ->where('slug', $questionCategory->slug)
                                            )
                                    )->etc('next_retry')
                            )
                    )
            );
    }


    /**
     * Test case to verify that the admin cannot view student details with a non-existing student ID.
     *
     * @return void
     */
    public function test_admin_cannot_view_student_details_with_non_existing_student_id(): void
    {
        $this->createAdminUser();

        $response = $this->actingAs($this->user)->getJson(route('students.show', 2));

        $response->assertStatus(404)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('message', 'No query results for model [App\\Models\\User] 2')
                    ->etc()
            );
    }
}
