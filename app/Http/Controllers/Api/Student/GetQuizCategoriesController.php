<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;
use App\Http\Repositories\QuizCategoryRepository;
use App\Http\Requests\QuizCategoryFilterRequest;
use App\Http\Resources\QuizCategoryResource;
use App\Models\QuizCategory;

class GetQuizCategoriesController extends Controller
{
    protected QuizCategoryRepository $quizCategoryRepository;

    public function __construct(QuizCategoryRepository $quizCategoryRepository)
    {
        $this->quizCategoryRepository = $quizCategoryRepository;
    }

    /**
     * @param QuizCategoryFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function __invoke(QuizCategoryFilterRequest $request): AnonymousResourceCollection
    {
        $data=$request->validated();

        $quizCategories = $this->quizCategoryRepository->getStudentFilteredQuizCategories($data);

        return QuizCategoryResource::collection($quizCategories);
    }
}
