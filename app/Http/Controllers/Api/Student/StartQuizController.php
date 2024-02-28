<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Repositories\ResultRepository;
use App\Models\Quiz;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Http\Repositories\QuestionRepository;
use Illuminate\Http\JsonResponse;

class StartQuizController extends Controller
{
    /**
     * @var QuestionRepository
     */
    protected QuestionRepository $questionRepository;

    /**
     * @var ResultRepository
     */
    private ResultRepository $resultRepository;

    /**
     * @param QuestionRepository $questionRepository
     * @param ResultRepository $resultRepository
     */
    public function __construct(QuestionRepository $questionRepository, ResultRepository $resultRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @param Quiz $quiz
     * @return QuizResource|JsonResponse
     */
    public function __invoke(Quiz $quiz): QuizResource | JsonResponse
    {
        if (!$quiz->status) {
            return response()->json([
                'message' => 'This quiz is currently not available for attempts.',
            ], 403);
        }

        $user = auth()->user();

        $result = $user->results()->where('quiz_id', $quiz->id)->latest()->first();
        if($result && $result->passed){
            return response()->json([
                'message' => 'You\'ve already passed this quiz and cannot reattempt it.',
            ],403);
        }
        if($result && !$result->passed){
            $retryDate = $result->created_at->addDays($quiz->retry_after);
            if(!now()->gte($retryDate)){
                return response()->json([
                    'message' => "You can reattempt this quiz after " . $retryDate->diffForHumans(),
                ],403);
            }
        }

        $quiz->load('category');
        $question_categories = $quiz->questionCategories()->select("question_category_id")->pluck("question_category_id");
        $questionResource = $this->questionRepository->getQuizQuestions($question_categories);
        $total_questions = $questionResource['data']['count'];
        if($total_questions != 14){
            return response()->json([
                'message' => "Quiz is not available now. Please try again later.",
            ],403);
        }
        $this->resultRepository->store($quiz->id,$total_questions);
        return (new QuizResource($quiz))->additional([
            'data' => [
                "questions" => $questionResource,
            ]
        ]);
    }
}
