<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ResultRepository;
use App\Http\Requests\SubmitQuizStoreRequest;
use App\Models\Quiz;
use Illuminate\Http\Response;

class SubmitQuizController extends Controller
{
    /**
     * @var ResultRepository
     */
    protected ResultRepository $resultRepository;

    /**
     * @param ResultRepository $resultRepository
     */
    public function __construct(ResultRepository $resultRepository)
    {
        $this->resultRepository = $resultRepository;
    }

    /**
     * @param Quiz $quiz
     * @param SubmitQuizStoreRequest $request
     * @return Response
     */
    public function __invoke(Quiz $quiz, SubmitQuizStoreRequest $request):Response
    {
        $data = $request->validated();
        $this->resultRepository->calculateAndUpdateResult($quiz,$data);
        return response()->noContent();
    }
}
