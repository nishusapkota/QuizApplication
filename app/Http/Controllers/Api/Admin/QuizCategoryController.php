<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuizCategoryFilterRequest;
use App\Models\QuizCategory;
use App\Http\Resources\QuizCategoryResource;
use App\Http\Requests\QuizCategoryStoreRequest;
use App\Http\Requests\QuizCategoryUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Repositories\QuizCategoryRepository;
use Illuminate\Http\Response;

class QuizCategoryController extends Controller
{
    private QuizCategoryRepository $quizCategoryRepository;

    /**
     * @param  QuizCategoryRepository  $quizCategoryRepository
     */
    public function __construct(QuizCategoryRepository $quizCategoryRepository)
    {
        $this->quizCategoryRepository = $quizCategoryRepository;
    }

    /**
     * @param QuizCategoryFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(QuizCategoryFilterRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $quizCategories = $this->quizCategoryRepository->getFilteredQuizCategories($data);

        return QuizCategoryResource::collection($quizCategories);
    }

    /**
     * @param QuizCategoryStoreRequest $request
     * @return QuizCategoryResource
     */
    public function store(QuizCategoryStoreRequest $request): QuizCategoryResource
    {
        $data = $request->validated();
        $quizCategory = QuizCategory::create($data);
        return (new QuizCategoryResource($quizCategory))->additional(ResponseHelper::stored());
    }

    /**
     * @param QuizCategory $quizCategory
     * @return QuizCategoryResource
     */
    public function show(QuizCategory $quizCategory): QuizCategoryResource
    {
        return new QuizCategoryResource($quizCategory);
    }

    /**
     * @param QuizCategoryUpdateRequest $request
     * @param QuizCategory $quizCategory
     * @return QuizCategoryResource
     */
    public function update(QuizCategoryUpdateRequest $request, QuizCategory $quizCategory): QuizCategoryResource
    {
        $data = $request->validated();

        $quizCategory->update($data);

        return (new QuizCategoryResource($quizCategory))->additional(ResponseHelper::updated($quizCategory));

    }

    /**
     * @param QuizCategory $quizCategory
     * @return JsonResponse|Response
     */
    public function destroy(QuizCategory $quizCategory): JsonResponse|Response
    {
        return $this->quizCategoryRepository->destroy($quizCategory);
    }
}
