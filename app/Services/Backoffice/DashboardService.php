<?php

namespace App\Services\Backoffice;

use App\Models\Board;
use App\Models\DailyVisitorStat;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * 대시보드 전체 데이터 가져오기
     */
    public function getDashboardData(): array
    {
        return [
            'boards' => $this->getBoardsOrderedByMenu(),
            'totalBoards' => Board::where('is_single_page', false)->count(),
            'totalPosts' => $this->getTotalPostsCount(),
            'activeBanners' => $this->getActiveBannersCount(),
            'activePopups' => $this->getActivePopupsCount(),
            'visitorStats' => $this->getVisitorStats(),
        ];
    }

    /**
     * 통계 데이터 가져오기
     */
    public function getStatisticsData(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'today_visitors' => DailyVisitorStat::where('visit_date', $today)
                ->value('visitor_count') ?? 0,
            'total_visitors' => DailyVisitorStat::sum('visitor_count'),
            'daily_stats' => $this->getDailyChartData(),
            'monthly_stats' => $this->getMonthlyChartData(),
        ];
    }

    /**
     * 방문객 통계 데이터 가져오기
     */
    public function getVisitorStats(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'today_visitors' => DailyVisitorStat::where('visit_date', $today)
                ->value('visitor_count') ?? 0,
            'total_visitors' => DailyVisitorStat::sum('visitor_count'),
            'daily_stats' => $this->getDailyChartData(),
            'monthly_stats' => $this->getMonthlyChartData(),
        ];
    }

    /**
     * 일별 차트 데이터 생성 (이번 달)
     */
    public function getDailyChartData(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $stats = DailyVisitorStat::whereBetween('visit_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderBy('visit_date')
            ->get()
            ->keyBy(function($item) {
                return $item->visit_date->format('Y-m-d');
            });
            
        $labels = [];
        $data = [];
        
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('m/d');
            
            $stat = $stats->get($dateStr);
            $data[] = $stat ? $stat->visitor_count : 0;
            
            $current->addDay();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * 월별 차트 데이터 생성 (이번 년도)
     */
    public function getMonthlyChartData(): array
    {
        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();
        
        $stats = DailyVisitorStat::whereBetween('visit_date', [$startOfYear->format('Y-m-d'), $endOfYear->format('Y-m-d')])
            ->get()
            ->groupBy(function($item) {
                return substr($item->visit_date, 0, 7);
            });
            
        $labels = [];
        $data = [];
        
        $current = $startOfYear->copy();
        while ($current->lte($endOfYear)) {
            $monthStr = $current->format('Y-m');
            $labels[] = $current->format('Y년 m월');
            
            $monthStats = $stats->get($monthStr, collect());
            $monthTotal = $monthStats->sum('visitor_count');
            $data[] = $monthTotal;
            
            $current->addMonth();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * 전체 게시글 수 계산 (실시간 집계)
     */
    public function getTotalPostsCount(): int
    {
        $boards = Board::where('is_active', true)
            ->where('is_single_page', false)
            ->get();
        
        $totalPosts = 0;
        
        foreach ($boards as $board) {
            $tableName = 'board_' . $board->slug;
            try {
                $count = DB::table($tableName)->count();
                $totalPosts += $count;
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $totalPosts;
    }

    /**
     * 활성 배너 수 계산
     */
    public function getActiveBannersCount(): int
    {
        try {
            return DB::table('banners')
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 활성 팝업 수 계산
     */
    public function getActivePopupsCount(): int
    {
        try {
            return DB::table('popups')
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * admin_menus의 1차 메뉴 순서대로 게시판 정렬
     */
    public function getBoardsOrderedByMenu()
    {
        try {
            $boards = Board::where('is_single_page', false)->get();
            
            $boardsWithMenu = $boards->map(function($board) {
                $adminMenu = $board->getAdminMenu();
                $board->adminMenu = $adminMenu;
                return $board;
            });
            
            $filteredBoards = $boardsWithMenu->filter(function($board) {
                return $board->adminMenu !== null;
            });
            
            $sortedBoards = $filteredBoards->sortBy(function($board) {
                return $board->adminMenu->parent->order;
            });
            
            return $sortedBoards->take(10)->values();
        } catch (\Exception $e) {
            return collect();
        }
    }
}

