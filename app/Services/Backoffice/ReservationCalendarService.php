<?php

namespace App\Services\Backoffice;

use App\Models\ProgramReservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationCalendarService
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * 월별 예약 내역 조회 (ProgramReservation 기준, 사용자 캘린더와 동일)
     * - 신청 유무와 관계없이 해당 월에 교육 시작일이 있는 전체 프로그램 표시
     * - 단체는 한 날짜씩 따로 등록되므로 education_start_date 기준으로만 배치 (기간 연속 표시 안 함)
     */
    public function getMonthlyReservations(int $year, int $month): array
    {
        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $dayOfWeek = $firstDay->dayOfWeek;
        $calendarStart = (clone $firstDay)->subDays($dayOfWeek)->startOfDay();
        $remainder = ($dayOfWeek + $daysInMonth) % 7;
        $nextMonthDays = $remainder === 0 ? 7 : (7 - $remainder);
        $calendarEnd = (clone $firstDay)->endOfMonth()->addDays($nextMonthDays);

        $programs = ProgramReservation::query()
            ->active()
            ->whereDate('education_start_date', '<=', $calendarEnd)
            ->whereDate('education_end_date', '>=', $calendarStart)
            ->orderBy('education_start_date')
            ->get();

        return $this->buildReservationMapFromPrograms($programs, $calendarStart, $calendarEnd);
    }

    /**
     * 캘린더 구조 생성
     */
    public function generateCalendar(int $year, int $month, array $reservationsByDate): array
    {
        $disabledDates = $this->scheduleService->getDisabledDates($year, $month);

        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $dayOfWeek = $firstDay->dayOfWeek; // 0 (일) ~ 6 (토)

        $calendar = [];
        $week = [];

        $summaryDefaults = [
            'individual' => 0,
            'group' => 0,
            'total' => 0,
        ];

        // 이전 달 날짜
        $prevMonthDate = (clone $firstDay)->subMonthNoOverflow();
        $daysInPrevMonth = $prevMonthDate->daysInMonth;
        for ($i = 0; $i < $dayOfWeek; $i++) {
            $week[] = [
                'day' => $daysInPrevMonth - ($dayOfWeek - $i - 1),
                'disabled' => true,
                'is_disabled_date' => false,
                'reservation_summary' => $summaryDefaults,
            ];
        }

        // 현재 달 날짜
        $day = 1;
        while ($day <= $daysInMonth) {
            $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isDisabledDate = isset($disabledDates[$dateKey]);
            $disabledDateInfo = $isDisabledDate && is_array($disabledDates[$dateKey]) ? $disabledDates[$dateKey] : null;
            $summary = $reservationsByDate[$dateKey]['summary'] ?? $summaryDefaults;

            $week[] = [
                'day' => $day,
                'disabled' => $isDisabledDate,
                'is_disabled_date' => $isDisabledDate,
                'disabled_date_title' => $disabledDateInfo['title'] ?? null,
                'reservation_summary' => $summary,
            ];

            if (count($week) === 7) {
                $calendar[] = $week;
                $week = [];
            }

            $day++;
        }

        // 다음 달 날짜
        $nextDay = 1;
        while (count($week) > 0 && count($week) < 7) {
            $week[] = [
                'day' => $nextDay,
                'disabled' => true,
                'is_disabled_date' => false,
                'reservation_summary' => $summaryDefaults,
            ];
            $nextDay++;
        }

        if (count($week) > 0) {
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * 특정 날짜의 예약 내역 조회 (해당 날짜를 교육 시작일로 하는 프로그램만)
     */
    public function getReservationsByDate(string $date): array
    {
        $dateCarbon = Carbon::parse($date)->startOfDay();
        $dateKey = $dateCarbon->format('Y-m-d');

        $programs = ProgramReservation::query()
            ->active()
            ->whereDate('education_start_date', $dateKey)
            ->orderBy('education_start_date')
            ->get();

        $individual = [];
        $group = [];

        foreach ($programs as $program) {
            $row = $this->programToCalendarRow($program);
            if ($program->application_type === 'individual') {
                $individual[] = $row;
            } else {
                $group[] = $row;
            }
        }

        return [
            'individual' => $individual,
            'group' => $group,
        ];
    }

    /**
     * ProgramReservation 목록을 날짜별 맵으로 구성 (교육 시작일 당일만 배치, 연속 기간 미표시)
     */
    protected function buildReservationMapFromPrograms(Collection $programs, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $reservationsByDate = [];

        foreach ($programs as $program) {
            $startDate = $program->education_start_date;
            if (!$startDate) {
                continue;
            }

            $dateKey = $startDate instanceof Carbon
                ? $startDate->format('Y-m-d')
                : Carbon::parse($startDate)->format('Y-m-d');

            if ($dateKey < $rangeStart->format('Y-m-d') || $dateKey > $rangeEnd->format('Y-m-d')) {
                continue;
            }

            $reservationsByDate[$dateKey] = $reservationsByDate[$dateKey] ?? ['individual' => [], 'group' => []];
            $type = $program->application_type === 'individual' ? 'individual' : 'group';
            $reservationsByDate[$dateKey][$type][] = $this->programToCalendarRow($program);
        }

        foreach ($reservationsByDate as $dateKey => &$data) {
            $individual = $data['individual'] ?? [];
            $group = $data['group'] ?? [];
            $data['individual'] = $individual;
            $data['group'] = $group;
            $data['summary'] = [
                'individual' => count($individual),
                'group' => count($group),
                'total' => count($individual) + count($group),
            ];
        }

        ksort($reservationsByDate);

        return $reservationsByDate;
    }

    /**
     * ProgramReservation 한 건을 캘린더/우측 패널용 배열로 변환
     */
    protected function programToCalendarRow(ProgramReservation $program): array
    {
        $applicantCount = $program->applied_count_display ?? 0;
        $capacity = $program->capacity ?? null;
        $isUnlimited = (bool) ($program->is_unlimited_capacity ?? false);

        return [
            'program_reservation_id' => $program->id,
            'type' => $program->application_type,
            'date' => $program->education_start_date ? Carbon::parse($program->education_start_date)->format('Y-m-d') : '',
            'education_start_date' => $program->education_start_date ? Carbon::parse($program->education_start_date)->format('Y-m-d') : null,
            'education_end_date' => $program->education_end_date ? Carbon::parse($program->education_end_date)->format('Y-m-d') : null,
            'program_name' => $program->program_name ?? '',
            'applicant_count' => $applicantCount,
            'capacity' => $capacity,
            'is_unlimited' => $isUnlimited,
            'is_closed' => $this->isReservationClosed($program, $applicantCount),
        ];
    }

    protected function isReservationClosed($reservation, int $applicantCount): bool
    {
        if (!$reservation) {
            return false;
        }

        // 신청 종료일 시간까지 비교
        $now = Carbon::now();
        $applicationEnd = $reservation->application_end_date;
        if ($applicationEnd) {
            $applicationEndDate = $applicationEnd instanceof Carbon ? $applicationEnd : Carbon::parse($applicationEnd);
            if ($applicationEndDate->lt($now)) {
                return true;
            }
        }

        $isUnlimited = (bool) ($reservation->is_unlimited_capacity ?? false);
        $capacity = $reservation->capacity;
        if (!$isUnlimited && $capacity && $applicantCount >= $capacity) {
            return true;
        }

        return false;
    }
}
