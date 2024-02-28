<?php

namespace App\Http\Repositories;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;

class StatisticsRepository
{
    /**
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_students' => User::where('user_type', 'student')->count(),
            'total_verified_students' => User::where('email_verified_at', '!=', null)
                ->where('user_type', 'student')
                ->count(),
            'total_quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::where('status', true)->count(),
            'total_questions' => Question::count(),
            'active_questions' => Question::where('status', true)->count(),
            'total_passed_students' => Result::where('passed', true)->count(),
        ];
    }
}
