<?php

namespace App\Http\Repositories;

use App\Exports\ResultExport;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ResultRepository
{

    /**
     * @param $data
     * @param $export
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredResult($data, $export): Collection|LengthAwarePaginator
    {
        $query = Result::with(['user','quiz']);
        if (isset($data['quiz'])) {
            $query->whereHas('quiz', function ($quizQuery) use ($data) {
                $quizQuery->where('title', 'like', '%' . $data['quiz'] . '%');
            });
        }
        if (isset($data['user'])) {
            $query->whereHas('user', function ($userQuery) use ($data) {
                $userQuery->where('name', 'like', '%' . $data['user'] . '%');
            });
        }

        if (isset($data['passed'])) {
            $query->where('passed', $data['passed']);
        }
        if ($export){
            return $query->with('user')->get();
        }
        return $query->paginate(10);
    }

    /**
     * @param $result
     * @return JsonResponse
     */
    public function exportResult($result): JsonResponse
    {
        $exportFilePath = 'exports/results.xlsx';
        Storage::delete($exportFilePath);

        $status = Excel::store(new ResultExport($result),$exportFilePath);
        if($status){
            $storagePath = asset($exportFilePath);
            return response()->json(['export_url' => $storagePath]);
        }

        return response()->json(['message' => "Could not generate. Please try again later"],503);

    }

    /**
     * @param int $quiz_id
     * @param int $totalQuestions
     * @return void
     */
    public function store(int $quiz_id,int $totalQuestions): void
    {
        $data = [
            'quiz_id' => $quiz_id,
            'total_question' => $totalQuestions,
            'user_id' => auth()->id(),
            'total_answered' => 0,
            "total_right_answer" => 0,
            "total_time" => 0,
            "passed" => false
        ];
        Result::create($data);
    }

    /**
     * @param Quiz $quiz
     * @param array $data
     * @return int
     */
    public function calculateAndUpdateResult(Quiz $quiz, array $data): int
    {
        $total_answered = count($data['answers']);
        $total_right_answered = 0;
        $total_weightage = 0;

        foreach ($data['answers'] as $qaData) {
            $question = Question::find($qaData['question_id']);
            if ($question && $question->answer === $qaData['answer']) {
                $total_weightage += $question->weightage;
                $total_right_answered++;
            }
        }
        $total_weightage_percentage = ($total_weightage / 100) * 100;

        return Result::where("quiz_id",$quiz->id)->where("user_id",auth()->id())->update([
            "passed" => $total_weightage_percentage >= $quiz->pass_percentage,
            "total_answered" => $total_answered,
            "total_right_answer" => $total_right_answered,
            "total_time" => $data['total_time'],
        ]);
    }
}
