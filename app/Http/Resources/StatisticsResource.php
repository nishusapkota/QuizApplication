<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'total_students' => $this['total_students'],
            'total_verified_students' => $this['total_verified_students'],
            'total_quizzes' => $this['total_quizzes'],
            'active_quizzes' => $this['active_quizzes'],
            'total_questions' => $this['total_questions'],
            'active_questions' => $this['active_questions'],
            'total_passed_students' => $this['total_passed_students'],
        ];
    }
}
