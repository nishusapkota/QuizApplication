<?php

namespace App\Http\Repositories;

use App\Models\Quiz;

use App\Models\Result;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exports\QuizzesExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class QuizRepository
{

    /**
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function getFilteredQuizzesForStudents(array $data): LengthAwarePaginator
    {

        $query = Quiz::with(['category:id,title', 'result'])
            ->active()
            ->whereDoesntHave('result', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('passed', true);
            });

        if (!empty($data['title'])) {
            $query = $query->where('title', 'like', '%' . $data['title'] . '%');
        }

        if (!empty($data['category_id'])) {
            $categoryIds = is_array($data['category_id']) ? $data['category_id'] : explode(',', $data['category_id']);
            $query->whereIn('category_id', $categoryIds);
        }
        return $query->paginate(12);
    }

    /**
     *
     * @param array $data, $export
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredQuizzes(array $data, $export): LengthAwarePaginator|Collection
    {
        $query = Quiz::with('category:id,title');

        if (isset($data['title'])) {
            $query->where('title', 'like', '%' . $data['title'] . '%')->get();
        }

        if (isset($data['status'])) {
            $query->where('status', $data['status'])->get();
        }

        if (isset($data['description'])) {
            $query->where('description', 'like', '%' . $data['description'] . '%')->get();
        }

        if (isset($data['category_id'])) {
            $categoryIds = is_array($data['category_id']) ? $data['category_id'] : explode(',', $data['category_id']);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($export) {
            return $query->get();
        }

        return $query->paginate(10);
    }

    /**
     * @param Quiz $quiz
     * @return Quiz
     */
    public function show(Quiz $quiz): Quiz
    {
        return $quiz->load(['category:id,title', 'questionCategories:id,title']);
    }

    /**
     * @param array $data
     * @return Quiz
     */
    public function store(array $data): Quiz
    {
        $name = date('ymd') . time() . '.' . $data['thumbnail']->extension();
        $data['thumbnail'] = $data['thumbnail']->storeAs('images/quizzes', $name);
        $quiz = Quiz::create($data);
        $quiz->questionCategories()->sync($data['question_categories']);
        return $quiz->fresh();
    }


    /**
     * @param Quiz $quiz
     * @param array $data
     * @return Quiz
     */
    public function update(Quiz $quiz, array $data): Quiz
    {
        if (isset($data['thumbnail'])) {
            Storage::delete('app/images/quizzes/' . $quiz->thumbnail);
            $name = date('ymd') . time() . '.' . $data['thumbnail']->extension();
            $data['thumbnail'] = $data['thumbnail']->storeAs('images/quizzes', $name);
        }
        $quiz->questionCategories()->sync($data['question_categories']);
        $quiz->update($data);

        return $quiz;
    }


    /**
     * @param Quiz $quiz
     * @return JsonResponse|Response
     */
    public function destroy(Quiz $quiz): Response|JsonResponse
    {
        if ($quiz->results()->exists()) {
            return response()->json(['error' => 'Could not delete the Quiz.']);
        }

        $quiz->questionCategories()->detach();
        $quiz->delete();
        return response()->noContent();
    }

    /**
     * @param Collection $quizzes
     * @return JsonResponse
     */
    public function exportQuizzes($quizzes): JsonResponse
    {
        $exportFilePath = 'exports/quizzes.xlsx';
        Storage::delete($exportFilePath);
        $status = Excel::store(new QuizzesExport($quizzes), $exportFilePath);

        if ($status) {
            $storagePath = asset($exportFilePath);
            return response()->json(['export_url' => $storagePath]);
        }

        return response()->json(['message' => "Could not generate export file. Please try again later"], 503);
    }

    /**
     * @return Collection
     */
    public function getPassedQuizzes(): Collection
    {
        return Quiz::select('quizzes.*')
            ->join('results', 'quizzes.id', '=', 'results.quiz_id')
            ->where('results.user_id', auth()->id())
            ->where('results.passed', true)
            ->orderBy('results.created_at', 'desc')
            ->with(['category:id,title', 'result'])
            ->get();
    }
}
