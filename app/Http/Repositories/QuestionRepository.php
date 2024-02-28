<?php

namespace App\Http\Repositories;

use App\Exports\QuestionsExport;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Collection;

class QuestionRepository
{
    /**
     * @param $categories
     * @return array
     */
    public function getQuizQuestions($categories): array
    {
        $basicQuestions =   $this->getQuestionByWeightage($categories, "5", 10);
        $intermediateQuestions =  $this->getQuestionByWeightage($categories, "10", 2);
        $advanceQuestions =  $this->getQuestionByWeightage($categories, "15", 2);
        $questionIds = $basicQuestions->merge($intermediateQuestions)->merge($advanceQuestions);
        $questions = Question::select("id", "title", "options", "weightage", "description", "status")
            ->whereIn("id", $questionIds)
            ->orderBy("weightage")
            ->get();
        return [
            'data' => [
                'questions' => QuestionResource::collection($questions),
                'count' => $questions->count()
            ]
        ];
    }

    /**
     * @param $categories
     * @param $weightage
     * @param $limit
     * @return mixed
     */
    public function getQuestionByWeightage($categories, $weightage, $limit): mixed
    {
        return Question::select('id', 'category_id', 'title', 'description', 'options', 'weightage', 'status')
            ->whereIn('category_id', $categories)
            ->active()
            ->inRandomOrder()
            ->where("weightage", $weightage)->limit($limit)->pluck("id");
    }


    /**
     * @param $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredQuestions($params, $export): LengthAwarePaginator|Collection
    {
        $query = Question::select(['id', 'title', 'slug', 'description', 'options', 'answer', 'weightage', 'category_id', 'status'])->with('category:id,title');

        if (isset($params['title'])) {
            $query->where('title', 'like', '%' . $params['title'] . '%');
        }

        if (isset($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if ($export) {
            return $query->get();
        }

        return $query->paginate(10);
    }

    /**
     * @param $questions
     * @return JsonResponse
     */
    public function exportQuestions($questions): JsonResponse
    {
        $exportFilePath = 'exports/questions.xlsx';
        Storage::delete($exportFilePath);
        $status =Excel::store(new QuestionsExport($questions), $exportFilePath);
        if ($status) {
            $storagePath = asset($exportFilePath);
            return response()->json(['export_url' => $storagePath]);
        }

        return response()->json(['message' => "Could not generate. Please try again later"], 503);

    }
}
