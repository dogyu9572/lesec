<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\MemberStatisticsService;
use Illuminate\Http\Request;

class MemberStatisticsController extends BaseController
{
    public function __construct(
        private MemberStatisticsService $memberStatisticsService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->query();
        if (empty($filters)) {
            $filters = $request->session()->get('backoffice.members.filters', []);
        }

        $statistics = $this->memberStatisticsService->getStatistics($request->duplicate($filters, null));

        return $this->view('backoffice.member-statistics.index', $statistics);
    }
}

