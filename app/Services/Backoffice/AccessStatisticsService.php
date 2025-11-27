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
        $today = now()->format('Y-m-d');
        $currentYear = now()->format('Y');
        $yearStats = $this->getYearlyStatistics($currentYear);
        $monthStats = $this->getMonthlyStatistics($currentYear . '-01', $currentYear . '-12');
        $dateStats = $this->getDailyStatistics($today, $today);
        $hourStats = $this->getHourlyStatistics($today);

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
            'selected_date' => $today,
        ];
    }

    /**
     * 타입별 통계 조회 (AJAX)
     */
    public function getStatisticsByType(string $type, ?string $date = null, ?string $startDate = null, ?string $endDate = null, ?string $startMonth = null, ?string $endMonth = null): array
    {
        return match($type) {
            'year' => $this->getYearlyStatistics($date ?? now()->format('Y')),
            'month' => $this->getMonthlyStatistics($startMonth, $endMonth),
            'date' => $this->getDailyStatistics($startDate, $endDate),
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
     * 월별 통계 조회 (시작월~종료월 기간, 최대 12개월)
     */
    private function getMonthlyStatistics(?string $startMonth = null, ?string $endMonth = null): array
    {
        // 디폴트는 현재 연도의 1월~12월
        if (!$startMonth) {
            $startMonth = now()->format('Y') . '-01';
        }
        if (!$endMonth) {
            $endMonth = now()->format('Y') . '-12';
        }

        // 시작월이 종료월보다 늦으면 교환
        if ($startMonth > $endMonth) {
            [$startMonth, $endMonth] = [$endMonth, $startMonth];
        }

        // 최대 12개월 제한
        $start = \Carbon\Carbon::parse($startMonth . '-01');
        $end = \Carbon\Carbon::parse($endMonth . '-01');
        $monthsDiff = $start->diffInMonths($end) + 1;

        if ($monthsDiff > 12) {
            throw new \InvalidArgumentException('조회 기간은 최대 12개월까지 가능합니다.');
        }

        $startDate = $startMonth . '-01';
        $endYear = substr($endMonth, 0, 4);
        $endMonthNum = (int)substr($endMonth, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $endMonthNum, (int)$endYear);
        $endDate = $endMonth . '-' . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);

        $stats = DailyVisitorStat::whereBetween('visit_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(visit_date, "%Y-%m") as month, SUM(visitor_count) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = [];
        $current = $start->copy();

        while ($current <= $end) {
            $monthStr = $current->format('Y-m');
            $monthStat = $stats->get($monthStr);
            $months[] = [
                'month' => (int)$current->format('m'),
                'label' => $current->format('Y년 m월'),
                'count' => $monthStat ? (int)$monthStat->total : 0,
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * 날짜별 통계 조회 (시작일~종료일 기간, 최대 30일)
     */
    private function getDailyStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        // 디폴트는 오늘 날짜
        if (!$startDate) {
            $startDate = now()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }

        // 시작일이 종료일보다 늦으면 교환
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // 최대 30일 제한
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;

        if ($daysDiff > 30) {
            throw new \InvalidArgumentException('조회 기간은 최대 30일까지 가능합니다.');
        }

        $stats = DailyVisitorStat::whereBetween('visit_date', [$startDate, $endDate])
            ->orderBy('visit_date')
            ->get()
            ->keyBy(function($item) {
                return $item->visit_date->format('Y-m-d');
            });

        $dates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dateStat = $stats->get($dateStr);
            $dates[] = [
                'date' => $dateStr,
                'label' => $current->format('m/d'),
                'count' => $dateStat ? $dateStat->visitor_count : 0,
            ];
            $current->addDay();
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

