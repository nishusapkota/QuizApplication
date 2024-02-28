<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Http\Requests\QuizStoreRequest;
use App\Http\Requests\QuizUpdateRequest;
use App\Http\Repositories\QuizRepository;
use App\Http\Requests\QuizFilterRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class QuizController extends Controller
{
    private QuizRepository $quizRepository;

    /**
     * @param  QuizRepository  $quizRepository
     */
    public function __construct(QuizRepository $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    /**
     * @param QuizFilterRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(QuizFilterRequest $request): AnonymousResourceCollection|Jsonresponse
    {
        $data = $request->validated();
        $export = $request->has("export");
        $quizzes = $this->quizRepository->getFilteredQuizzes($data, $export);

        if ($export) {
            return $this->quizRepository->exportQuizzes($quizzes);
        }

        return QuizResource::collection($quizzes);
    }


    /**
     * @param  QuizStoreRequest  $request
     * @return QuizResource
     */
    public function store(QuizStoreRequest $request): QuizResource
    {
        $data = $request->validated();
        $quiz = $this->quizRepository->store($data);
        return (new QuizResource($quiz))->additional(ResponseHelper::stored());
    }


    /**
     * @param  Quiz  $quiz
     * @return QuizResource
     */
    public function show(Quiz $quiz): QuizResource
    {
        $quiz = $this->quizRepository->show($quiz);
        return new QuizResource($quiz);
    }


    /**
     * @param  Quiz  $quiz
     * @param  QuizUpdateRequest  $request
     * @return QuizResource
     */
    public function update(Quiz $quiz, QuizUpdateRequest $request): QuizResource
    {
        $data = $request->validated();
        $quiz = $this->quizRepository->update($quiz, $data);
        return (new QuizResource($quiz))->additional(ResponseHelper::updated($quiz));
    }

    /**
     * @param  Quiz  $quiz
     * @return JsonResponse|Response
     */
    public function destroy(Quiz $quiz): JsonResponse|Response
    {
         return $this->quizRepository->destroy($quiz);
    }
}
