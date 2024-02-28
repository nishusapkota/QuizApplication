<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Repositories\QuizRepository;
use App\Http\Requests\GetQuizzesFilterRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class GetQuizzesController extends Controller
{
    protected QuizRepository $quizRepository;

    public function __construct(QuizRepository $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    /**
     * @param GetQuizzesFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function __invoke(GetQuizzesFilterRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $quizzes=$this->quizRepository->getFilteredQuizzesForStudents($data);

        return QuizResource::collection($quizzes);
    }
}
