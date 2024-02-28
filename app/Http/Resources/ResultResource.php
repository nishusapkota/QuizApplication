<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\QuizResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => new UserResource($this->whenLoaded('user')),
            'quiz_id' => new QuizResource($this->whenLoaded('quiz')),
            'passed' => $this->passed,
            'total_question' => $this->total_question,
            'total_answered' => $this->total_answered,
            'total_right_answer' => $this->total_right_answer,
            'total_time' => $this->total_time,
            'next_retry' => $this->next_retry
        ];
    }
}
