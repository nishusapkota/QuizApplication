<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Imports\QuestionImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\QuestionResource;
use App\Http\Requests\QuestionStoreRequest;
use App\Http\Requests\QuestionFilterRequest;
use App\Http\Requests\QuestionImportRequest;
use App\Http\Requests\QuestionUpdateRequest;
use App\Http\Repositories\QuestionRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionController extends Controller
{
    private QuestionRepository $questionRepository;

    /**
     * @param QuestionRepository $questionRepository
     */
    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }


    /**
     * @param QuestionFilterRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(QuestionFilterRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $params = $request->validated();
        $export = $request->has("export");
        $questions = $this->questionRepository->getFilteredQuestions($params, $export);

        if ($export) {
            return $this->questionRepository->exportQuestions($questions);
        }

        return QuestionResource::collection($questions);
    }

    /**
     * @param QuestionImportRequest $request
     * @return JsonResponse
     */
    public function importQuestion(QuestionImportRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $import = new QuestionImport;
            Excel::import($import, $request->file('file'));
            $request->file->store('imports');
            if (!empty($import->getFailures())) {
                DB::rollBack();
                return response()->json(['errors' => $import->getFailures()], 422);
            }


            DB::commit();
            return response()->json([
                'message' =>
                $import->getRowCount() . " imported Successfully. " .
                    $import->getDuplicateCount() . " duplicate record found"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param QuestionStoreRequest $request
     * @return QuestionResource
     */
    public function store(QuestionStoreRequest $request): QuestionResource
    {
        $data = $request->validated();
        $question = Question::create($data)->fresh();
        return (new QuestionResource($question))->additional(ResponseHelper::stored());
    }


    /**
     * @param Question $question
     * @return QuestionResource
     */
    public function show(Question $question): QuestionResource
    {
        $question->load('category:id,title');
        return new QuestionResource($question);
    }


    /**
     * @param QuestionUpdateRequest $request
     * @param Question $question
     * @return QuestionResource
     */
    public function update(QuestionUpdateRequest $request, Question $question): QuestionResource
    {
        $data = $request->validated();
        $question->update($data);
        return (new QuestionResource($question))->additional(ResponseHelper::updated($question));
    }


    /**
     * @param Question $question
     * @return Response
     */
    public function destroy(Question $question): Response
    {
        $question->delete();
        return response()->noContent();
    }
}
