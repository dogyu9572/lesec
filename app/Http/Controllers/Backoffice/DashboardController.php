<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\DashboardService;

class DashboardController extends BaseController
{
    /**
     * DashboardService 주입
     */
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * 대시보드 메인 페이지
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();
        
        return $this->view('backoffice.dashboard', $data);
    }

    /**
     * 방문객 통계 API
     */
    public function statistics()
    {
        return response()->json(
            $this->dashboardService->getStatisticsData()
        );
    }
}
