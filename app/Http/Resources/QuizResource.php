<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail_url,
            'description' => $this->description,
            'time' => $this->time,
            'retry_after' => $this->retry_after,
            'status' => $this->status,
            'pass_percentage' => $this->pass_percentage,
            'category'=> new QuizCategoryResource($this->whenLoaded('category')),
            'question_categories' => QuizCategoryResource::collection($this->whenLoaded('questionCategories')),
            'result'=> new ResultResource($this->whenLoaded('result')),
        ];
    }
}
