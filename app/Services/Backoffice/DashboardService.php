<?php

namespace App\Services\Backoffice;

use App\Models\Board;
use App\Models\DailyVisitorStat;
use App\Models\ProgramReservation;
use App\Models\GroupApplication;
use App\Models\IndividualApplication;
use Illuminate\Support\Facades\DB;
use App\Services\Backoffice\ReservationCalendarService;

class DashboardService
{
    protected ReservationCalendarService $reservationCalendarService;

    public function __construct(ReservationCalendarService $reservationCalendarService)
    {
        $this->reservationCalendarService = $reservationCalendarService;
    }

    /**
     * 대시보드 전체 데이터 가져오기
     */
    public function getDashboardData(): array
    {
        $year = (int) now()->year;
        $month = (int) now()->month;

        // 월별 예약 내역 조회
        $reservationsByDate = $this->reservationCalendarService->getMonthlyReservations($year, $month);

        // 캘린더 생성
        $calendar = $this->reservationCalendarService->generateCalendar($year, $month, $reservationsByDate);

        return [
            'programStats' => $this->getProgramStats(),
            'applicationStats' => $this->getApplicationStats(),
            'recentGroupApplications' => $this->getRecentApplications('group'),
            'recentIndividualApplications' => $this->getRecentApplications('individual'),
            'visitorStats' => $this->getVisitorStats(),
            'calendar' => $calendar,
            'reservationsByDate' => $reservationsByDate,
            'year' => $year,
            'month' => $month,
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
     * 전체 게시글 수 계산 (실시간 집계, 삭제된 글 제외)
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
                $count = DB::table($tableName)
                    ->whereNull('deleted_at')
                    ->count();
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
                ->whereNull('deleted_at')
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
                ->whereNull('deleted_at')
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

    /**
     * 프로그램 통계 가져오기
     */
    public function getProgramStats(): array
    {
        $activePrograms = ProgramReservation::where('is_active', true)->count();
        
        $closedPrograms = ProgramReservation::where('is_active', true)
            ->where(function($query) {
                $query->where(function($q) {
                    $q->where('is_unlimited_capacity', false)
                      ->whereRaw('applied_count >= capacity');
                })->orWhere(function($q) {
                    $q->whereNotNull('application_end_date')
                      ->where('application_end_date', '<', now());
                });
            })
            ->count();
        
        return [
            'active_count' => $activePrograms,
            'closed_count' => $closedPrograms,
        ];
    }

    /**
     * 최근 등록된 프로그램 목록 가져오기
     */
    public function getRecentPrograms()
    {
        return ProgramReservation::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($program) {
                return [
                    'id' => $program->id,
                    'program_name' => $program->program_name,
                    'application_type' => $program->application_type_name,
                    'education_type' => $program->education_type_name,
                    'reception_type' => $program->reception_type_name ?? '-',
                    'education_start_date' => $program->education_start_date?->format('Y.m.d'),
                    'education_end_date' => $program->education_end_date?->format('Y.m.d'),
                    'application_start_date' => $program->application_start_date?->format('Y.m.d'),
                    'application_end_date' => $program->application_end_date?->format('Y.m.d'),
                    'applied_count' => $program->applied_count ?? 0,
                    'capacity' => $program->is_unlimited_capacity ? '무제한' : ($program->capacity ?? 0),
                    'status' => $program->current_reception_type_name,
                    'created_at' => $program->created_at,
                ];
            });
    }

    /**
     * 신청 통계 가져오기
     */
    public function getApplicationStats(): array
    {
        $today = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $todayGroup = GroupApplication::whereBetween('applied_at', [$today, $endOfToday])->count();
        $todayIndividual = IndividualApplication::whereBetween('applied_at', [$today, $endOfToday])->count();
        $todayTotal = $todayGroup + $todayIndividual;
        
        $weekGroup = GroupApplication::whereBetween('applied_at', [$startOfWeek, $endOfWeek])->count();
        $weekIndividual = IndividualApplication::whereBetween('applied_at', [$startOfWeek, $endOfWeek])->count();
        $weekTotal = $weekGroup + $weekIndividual;
        
        $monthGroup = GroupApplication::whereBetween('applied_at', [$startOfMonth, $endOfMonth])->count();
        $monthIndividual = IndividualApplication::whereBetween('applied_at', [$startOfMonth, $endOfMonth])->count();
        $monthTotal = $monthGroup + $monthIndividual;
        
        $pendingGroup = GroupApplication::where('application_status', 'pending')->count();
        $pendingIndividual = IndividualApplication::where('draw_result', 'pending')->count();
        $pendingTotal = $pendingGroup + $pendingIndividual;
        
        return [
            'today_count' => $todayTotal,
            'week_count' => $weekTotal,
            'month_count' => $monthTotal,
            'pending_count' => $pendingTotal,
        ];
    }

    /**
     * 신청 상태별 집계
     */
    public function getApplicationStatusCounts(): array
    {
        $groupPending = GroupApplication::where('application_status', 'pending')->count();
        $groupApproved = GroupApplication::where('application_status', 'approved')->count();
        $groupCancelled = GroupApplication::where('application_status', 'cancelled')->count();
        
        $individualPending = IndividualApplication::where('draw_result', 'pending')->count();
        $individualWin = IndividualApplication::where('draw_result', 'win')->count();
        $individualFail = IndividualApplication::where('draw_result', 'fail')->count();
        
        return [
            'group' => [
                'pending' => $groupPending,
                'approved' => $groupApproved,
                'cancelled' => $groupCancelled,
            ],
            'individual' => [
                'pending' => $individualPending,
                'win' => $individualWin,
                'fail' => $individualFail,
            ],
        ];
    }

    /**
     * 최근 신청 목록 가져오기
     * 
     * @param string $type 'group' 또는 'individual'
     * @return \Illuminate\Support\Collection
     */
    public function getRecentApplications(string $type = 'individual')
    {
        if ($type === 'group') {
            return GroupApplication::with('reservation')
                ->orderBy('applied_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($app) {
                    return [
                        'type' => 'group',
                        'application_number' => $app->application_number,
                        'program_name' => $app->reservation?->program_name ?? '-',
                        'applicant_name' => $app->applicant_name,
                        'applied_at' => $app->applied_at?->format('Y.m.d H:i'),
                        'status' => $app->application_status_label,
                        'created_at' => $app->applied_at ?? $app->created_at,
                    ];
                });
        }
        
        return IndividualApplication::with('reservation')
            ->orderBy('applied_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($app) {
                return [
                    'type' => 'individual',
                    'application_number' => $app->application_number,
                    'program_name' => $app->reservation?->program_name ?? $app->program_name ?? '-',
                    'applicant_name' => $app->applicant_name,
                    'applied_at' => $app->applied_at?->format('Y.m.d H:i'),
                    'status' => $app->draw_result_label,
                    'created_at' => $app->applied_at ?? $app->created_at,
                ];
            });
    }
}

