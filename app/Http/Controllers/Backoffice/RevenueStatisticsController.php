<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\RevenueStatisticsService;
use App\Models\RevenueStatistics;
use Illuminate\Http\Request;

class RevenueStatisticsController extends BaseController
{
    protected $revenueStatisticsService;

    public function __construct(RevenueStatisticsService $revenueStatisticsService)
    {
        $this->revenueStatisticsService = $revenueStatisticsService;
    }

    /**
     * 수익 통계 목록을 표시
     */
    public function index(Request $request)
    {
        $statistics = $this->revenueStatisticsService->getStatisticsWithFilters($request);
        return view('backoffice.revenue-statistics.index', compact('statistics'));
    }

    /**
     * 수익 통계 생성 폼 표시
     */
    public function create()
    {
        return view('backoffice.revenue-statistics.create');
    }

    /**
     * 수익 통계 저장
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.participants_count' => 'nullable|integer|min:0',
            'items.*.school_name' => 'nullable|string|max:255',
            'items.*.revenue' => 'nullable|integer|min:0',
        ]);

        try {
            $this->revenueStatisticsService->createStatistics($request->all());

            return redirect()->route('backoffice.revenue-statistics.index')
                ->with('success', '수익 통계가 등록되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '수익 통계 등록에 실패했습니다.');
        }
    }

    /**
     * 수익 통계 수정 폼 표시
     */
    public function edit(RevenueStatistics $revenue_statistic)
    {
        $statistics = $revenue_statistic->load('items');
        return view('backoffice.revenue-statistics.edit', compact('statistics'));
    }

    /**
     * 수익 통계 정보 업데이트
     */
    public function update(Request $request, RevenueStatistics $revenue_statistic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.participants_count' => 'nullable|integer|min:0',
            'items.*.school_name' => 'nullable|string|max:255',
            'items.*.revenue' => 'nullable|integer|min:0',
        ]);

        try {
            $this->revenueStatisticsService->updateStatistics($revenue_statistic, $request->all());

            return redirect()->route('backoffice.revenue-statistics.index')
                ->with('success', '수익 통계가 수정되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '수익 통계 수정에 실패했습니다.');
        }
    }

    /**
     * 수익 통계 삭제
     */
    public function destroy(RevenueStatistics $revenue_statistic)
    {
        try {
            $this->revenueStatisticsService->deleteStatistics($revenue_statistic);

            return redirect()->route('backoffice.revenue-statistics.index')
                ->with('success', '수익 통계가 삭제되었습니다.');
        } catch (\Exception $e) {
            return redirect()->route('backoffice.revenue-statistics.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * 수익 통계 일괄 삭제
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'statistics_ids' => 'required|array',
            'statistics_ids.*' => 'integer|exists:revenue_statistics,id'
        ]);

        $statisticsIds = $request->input('statistics_ids');
        
        try {
            $deletedCount = $this->revenueStatisticsService->bulkDelete($statisticsIds);

            return response()->json([
                'success' => true,
                'message' => $deletedCount . '개의 수익 통계가 삭제되었습니다.',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 수익 통계 CSV 다운로드
     */
    public function download(RevenueStatistics $revenueStatistics)
    {
        try {
            return $this->revenueStatisticsService->downloadStatistics($revenueStatistics);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '다운로드 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}

