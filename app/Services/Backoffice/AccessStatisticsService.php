<?php

namespace App\Services\Backoffice;

use App\Models\DailyVisitorStat;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessStatisticsService
{
    /**
     * 접속통계 메인 데이터 조회
     */
    public function getStatistics(Request $request): array
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        // 전체 방문자수
        $totalVisitors = DailyVisitorStat::sum('visitor_count');

        // 오늘 방문자수
        $todayVisitors = DailyVisitorStat::where('visit_date', $today)
            ->value('visitor_count') ?? 0;

        // 어제 방문자수
        $yesterdayVisitors = DailyVisitorStat::where('visit_date', $yesterday)
            ->value('visitor_count') ?? 0;

        // 최고 방문자수
        $peakStat = DailyVisitorStat::orderBy('visitor_count', 'desc')
            ->first();
        $peakVisitors = $peakStat ? $peakStat->visitor_count : 0;
        $peakDate = $peakStat ? $peakStat->visit_date->format('Y-m-d') : null;

        // 기본 통계 데이터 (연별, 월별, 날짜별, 시간별)
        $yearStats = $this->getYearlyStatistics(now()->format('Y'));
        $monthStats = $this->getMonthlyStatistics(now()->format('Y-m'));
        $dateStats = $this->getDailyStatistics(now()->format('Y-m'));
        $hourStats = $this->getHourlyStatistics(now()->format('Y-m-d'));

        return [
            'total_visitors' => $totalVisitors,
            'today_visitors' => $todayVisitors,
            'yesterday_visitors' => $yesterdayVisitors,
            'peak_visitors' => $peakVisitors,
            'peak_date' => $peakDate,
            'year_stats' => $yearStats,
            'month_stats' => $monthStats,
            'date_stats' => $dateStats,
            'hour_stats' => $hourStats,
            'selected_year' => now()->format('Y'),
            'selected_month' => now()->format('Y-m'),
            'selected_date' => now()->format('Y-m-d'),
        ];
    }

    /**
     * 타입별 통계 조회 (AJAX)
     */
    public function getStatisticsByType(string $type, ?string $date): array
    {
        return match($type) {
            'year' => $this->getYearlyStatistics($date ?? now()->format('Y')),
            'month' => $this->getMonthlyStatistics($date ?? now()->format('Y-m')),
            'date' => $this->getDailyStatistics($date ?? now()->format('Y-m')),
            'hour' => $this->getHourlyStatistics($date ?? now()->format('Y-m-d')),
            default => [],
        };
    }

    /**
     * 연별 통계 조회
     */
    private function getYearlyStatistics(string $year): array
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';

        $stats = DailyVisitorStat::whereBetween('visit_date', [$startDate, $endDate])
            ->selectRaw('YEAR(visit_date) as year, SUM(visitor_count) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // 연도별 데이터 구성 (최근 5년)
        $currentYear = (int)$year;
        $years = [];
        for ($i = $currentYear - 4; $i <= $currentYear; $i++) {
            $yearStat = $stats->firstWhere('year', $i);
            $years[] = [
                'year' => $i,
                'label' => $i . '년',
                'count' => $yearStat ? (int)$yearStat->total : 0,
            ];
        }

        return $years;
    }

    /**
     * 월별 통계 조회 (선택한 연도의 월별)
     */
    private function getMonthlyStatistics(string $yearMonth): array
    {
        $year = substr($yearMonth, 0, 4);
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';

        $stats = DailyVisitorStat::whereBetween('visit_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(visit_date, "%Y-%m") as month, SUM(visitor_count) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%s-%02d', $year, $month);
            $monthStat = $stats->get($monthStr);
            $months[] = [
                'month' => $month,
                'label' => $month . '월',
                'count' => $monthStat ? (int)$monthStat->total : 0,
            ];
        }

        return $months;
    }

    /**
     * 날짜별 통계 조회 (선택한 연월의 일별)
     */
    private function getDailyStatistics(string $yearMonth): array
    {
        $year = substr($yearMonth, 0, 4);
        $month = substr($yearMonth, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

        $startDate = $yearMonth . '-01';
        $endDate = $yearMonth . '-' . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);

        $stats = DailyVisitorStat::whereBetween('visit_date', [$startDate, $endDate])
            ->orderBy('visit_date')
            ->get()
            ->keyBy(function($item) {
                return $item->visit_date->format('Y-m-d');
            });

        $dates = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $yearMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            $dateStat = $stats->get($dateStr);
            $dates[] = [
                'day' => $day,
                'label' => $day . '일',
                'count' => $dateStat ? $dateStat->visitor_count : 0,
            ];
        }

        return $dates;
    }

    /**
     * 시간별 통계 조회 (선택한 날짜의 시간별)
     */
    private function getHourlyStatistics(string $date): array
    {
        $stats = VisitorLog::whereDate('created_at', $date)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hours = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStat = $stats->get($hour);
            $hours[] = [
                'hour' => $hour,
                'label' => $hour . '시',
                'count' => $hourStat ? (int)$hourStat->total : 0,
            ];
        }

        return $hours;
    }
}

