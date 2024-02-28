<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'slug' => $this->whenHas("slug"),
            'description' => $this->description,
            'options' => $this->options,
            'answer' => $this->whenHas('answer'),
            'weightage' => $this->weightage,
            'status' => $this->status,
            'category' => new QuestionCategoryResource($this->whenLoaded('category')),
        ];
    }
}
