<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Repositories\StatisticsRepository;
use App\Http\Resources\StatisticsResource;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;

class StatisticsController extends Controller
{
    /**
     * @var StatisticsRepository
     */
    private StatisticsRepository $statisticsRepository;

    /**
     * @param  StatisticsRepository  $statisticsRepository
     */
    public function __construct(StatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }
    public function index(){
        $statistics = $this->statisticsRepository->getStatistics();

        return new StatisticsResource($statistics);
    }

}
