<?php

namespace App\Services\Backoffice\Concerns;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * 교육 일정이 교육신청 불가 기간과 겹치는지 검증하는 트레이트
 */
trait ValidatesScheduleAvailability
{
    /**
     * 교육 일정이 교육신청 불가 기간과 겹치는지 확인
     */
    protected function ensureEducationScheduleIsAvailable(?string $startDate, ?string $endDate): void
    {
        if (!$startDate || !$endDate) {
            return;
        }

        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $hasDisabledSchedule = Schedule::query()
            ->disableApplication()
            ->overlapping($start, $end)
            ->exists();

        if ($hasDisabledSchedule) {
            throw ValidationException::withMessages([
                'education_start_date' => '교육신청 불가 기간이 포함되어 저장할 수 없습니다.',
                'education_end_date' => '교육신청 불가 기간이 포함되어 저장할 수 없습니다.',
            ]);
        }
    }
}

