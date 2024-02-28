<?php

namespace Tests\Feature;


use Tests\TestCase;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Result;
use App\Models\QuizCategory;
use App\Http\Resources\QuizResource;
use App\Http\Requests\QuizFilterRequest;
use App\Http\Repositories\QuizRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class QuizzesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test filtering quizzes for a student in student dashboard who hasn't passed quizzes and has not attempted quizzes
     * It exclude all the passed quizzes
     */
    public function test_student_dashboard_filters_quizzes_not_passed_attempted_but_not_passed_exclude_passed_quizzes(): void
    {

        $category = QuizCategory::factory()->create();

        $student = User::factory()->create();

        $quiz1 = Quiz::factory()->create(['title' => 'Quiz 1', 'category_id' => $category->id]);
        $quiz2 = Quiz::factory()->create(['title' => 'Quiz 2', 'category_id' => $category->id]);

        Storage::delete($quiz1->thumbnail);
        Storage::delete($quiz2->thumbnail);
        Result::factory()->create([
            'quiz_id' => $quiz1->id,
            'user_id' => $student->id,
            'passed' => true,
            'total_question' => 14,
            'total_answered' => 14,
        ]);

        $repositoryMock = $this->getMockBuilder(QuizRepository::class)
            ->onlyMethods(['getFilteredQuizzesForStudents'])
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getFilteredQuizzesForStudents')
            ->willReturn(new LengthAwarePaginator([$quiz2], 1, 10));

        $this->app->instance(QuizRepository::class, $repositoryMock);

        $response = $this->actingAs($student)
            ->getJson('api/student/quizzes/all');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Quiz 2']);
        $response->assertJsonMissing(['title' => 'Quiz 1']);
    }

    /**
     * when admin pass export params in get quizzes it exports excel file with data of quizess
     *
     */
    public function test_quizzes_can_be_export_when_export_is_send_in_params()
    {
        $this->createAdminUser();

        Storage::fake();

        $response = $this->actingAs($this->user)
            ->get('/api/admin/quizzes?export');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'export_url'
            ]);
        $exportUrl = $response->json('export_url');

        $this->assertNotEmpty($exportUrl);
        $exportFilePath = 'exports/quizzes.xlsx';

        Storage::assertExists($exportFilePath);
    }
}

