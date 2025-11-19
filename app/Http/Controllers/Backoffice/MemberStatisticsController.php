<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\MemberStatisticsService;

class MemberStatisticsController extends BaseController
{
    public function __construct(
        private MemberStatisticsService $memberStatisticsService
    ) {}

    public function index()
    {
        $statistics = $this->memberStatisticsService->getStatistics();

        return $this->view('backoffice.member-statistics.index', $statistics);
    }
}

