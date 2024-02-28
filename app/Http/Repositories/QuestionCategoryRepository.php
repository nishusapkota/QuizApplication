<?php

namespace App\Http\Repositories;
use App\Models\QuestionCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;


class QuestionCategoryRepository
{
    /**
     * @param $data
     * @return LengthAwarePaginator
     */
    public function getFilteredQuestionCategories($data): LengthAwarePaginator
    {
        $query = QuestionCategory::select(['id', 'title']);

        if (isset($data['title'])) {
            $query->where('title', 'like', '%' . $data['title'] . '%');
        }

        return $query->paginate(10);
    }

    /**
     * @param $questionCategory
     * @return JsonResponse|Response
    */
    public function destroy($questionCategory): JsonResponse|Response
    {
        if ($questionCategory->questions()->exists() || $questionCategory->quizzes()->exists()) {
            return response()->json(['error' => 'Could not delete the Question category.']);
        }

        $questionCategory->delete();
        return response()->noContent();
    }
}
