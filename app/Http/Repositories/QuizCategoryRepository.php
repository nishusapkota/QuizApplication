<?php

namespace App\Http\Repositories;

use App\Models\QuizCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class QuizCategoryRepository
{
    /**
     * @param $data
     * @return LengthAwarePaginator
     */
    public function getFilteredQuizCategories($data): LengthAwarePaginator
    {
        $query =QuizCategory::select(['id', 'title']);

        if (isset($data['title'])) {
            $query->where('title', 'like', '%' . $data['title'] . '%');
        }

        return $query->paginate(10);
    }

    /**
     * @param $data
     * @return Collection
     */
    public function getStudentFilteredQuizCategories($data): Collection
    {
        $query =QuizCategory::select(['id', 'title']);

        if (isset($data['title'])) {
            $query->where('title', 'like', '%' . $data['title'] . '%');
        }

        return $query->get();
    }

    /**
     * @param $quizCategory
     * @return JsonResponse|Response
     */
    public function destroy($quizCategory): JsonResponse|Response
    {
        if ($quizCategory->quizzes()->exists()) {
            return response()->json(['error' => 'Could not delete the category.']);
        }

        $quizCategory->delete();
        return response()->noContent();
    }
}
