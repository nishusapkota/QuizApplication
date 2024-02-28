<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResultFilterRequest;
use App\Http\Resources\ResultResource;
use App\Http\Repositories\ResultRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResultController extends Controller
{
    /**
     * @var ResultRepository
     */
    private ResultRepository $resultRepository;

    /**
     * @param  ResultRepository  $resultRepository
     */
    public function __construct(ResultRepository $resultRepository)
    {
        $this->resultRepository = $resultRepository;
    }

    /**
     * @param ResultFilterRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(ResultFilterRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $data = $request->validated();

        $export = $request->has("export");
        $results = $this->resultRepository->getFilteredResult($data, $export);

        if ($export){
            return $this->resultRepository->exportResult($results);
        }
        return ResultResource::collection($results);
    }
}
