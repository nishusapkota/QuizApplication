<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionCategoryFilterRequest;
use App\Http\Resources\QuestionCategoryResource;
use App\Models\QuestionCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Repositories\QuestionCategoryRepository;

class GetQuestionCategoriesController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $quizCategories = QuestionCategory::select(['id', 'title'])->get();
        return QuestionCategoryResource::collection($quizCategories);
    }
}
