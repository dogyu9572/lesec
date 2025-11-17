<?php

namespace App\Services\Backoffice;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    /**
     * 일정 목록 조회
     */
    public function getList(array $filters = []): Collection
    {
        $query = Schedule::query();

        if (isset($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('start_date', 'asc')->get();
    }

    /**
     * 일정 생성
     */
    public function create(array $data): Schedule
    {
        return Schedule::create([
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'content' => $data['content'] ?? null,
            'disable_application' => $data['disable_application'] ?? false,
        ]);
    }

    /**
     * 일정 수정
     */
    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update([
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'content' => $data['content'] ?? null,
            'disable_application' => $data['disable_application'] ?? false,
        ]);

        return $schedule->fresh();
    }

    /**
     * 일정 삭제
     */
    public function delete(Schedule $schedule): bool
    {
        return $schedule->delete();
    }

    /**
     * 특정 날짜 범위의 "교육신청 불가" 일정 조회
     */
    public function getDisabledDates(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        $schedules = Schedule::query()
            ->disableApplication()
            ->overlapping(
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            )
            ->get();

        $disabledDates = [];
        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->start_date);
            $end = Carbon::parse($schedule->end_date);
            
            // 해당 월의 범위로 제한
            $rangeStart = $start->isBefore($startOfMonth) ? $startOfMonth : $start;
            $rangeEnd = $end->isAfter($endOfMonth) ? $endOfMonth : $end;

            $current = (clone $rangeStart);
            while ($current->lte($rangeEnd)) {
                $dateKey = $current->format('Y-m-d');
                $disabledDates[$dateKey] = true;
                $current->addDay();
            }
        }

        return $disabledDates;
    }

    /**
     * 특정 날짜의 일정 조회
     */
    public function getSchedulesByDate(string $date): Collection
    {
        return Schedule::query()
            ->inDateRange($date)
            ->orderBy('start_date', 'asc')
            ->get();
    }
}

