<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\AccessStatisticsService;
use Illuminate\Http\Request;

class AccessStatisticsController extends BaseController
{
    public function __construct(
        private AccessStatisticsService $accessStatisticsService
    ) {}

    /**
     * 접속통계 메인 페이지
     */
    public function index(Request $request)
    {
        $statistics = $this->accessStatisticsService->getStatistics($request);

        return $this->view('backoffice.access-statistics.index', $statistics);
    }

    /**
     * AJAX 통계 조회 API
     */
    public function getStatistics(Request $request)
    {
        $type = $request->get('type'); // year, month, date, hour
        $date = $request->get('date'); // YYYY, YYYY-MM, YYYY-MM-DD

        $statistics = $this->accessStatisticsService->getStatisticsByType($type, $date);

        return response()->json($statistics);
    }
}

