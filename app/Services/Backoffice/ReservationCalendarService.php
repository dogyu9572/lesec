<?php

namespace App\Services\Backoffice;

use App\Models\IndividualApplication;
use App\Models\GroupApplication;
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
     * 월별 예약 내역 조회 (개인/단체 신청 기준)
     */
    public function getMonthlyReservations(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        return $this->buildReservationMap($startOfMonth, $endOfMonth);
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
     * 특정 날짜의 예약 내역 조회
     */
    public function getReservationsByDate(string $date): array
    {
        $dateCarbon = Carbon::parse($date)->startOfDay();
        $map = $this->buildReservationMap($dateCarbon, (clone $dateCarbon)->endOfDay());
        $dateKey = $dateCarbon->format('Y-m-d');
        $data = $map[$dateKey] ?? ['individual' => [], 'group' => []];

        return [
            'individual' => $data['individual'],
            'group' => $data['group'],
        ];
    }

    /**
     * 지정 범위의 예약 내역을 날짜별로 정리
     */
    protected function buildReservationMap(Carbon $start, Carbon $end): array
    {
        $reservationsByDate = [];

        $individualApplications = IndividualApplication::query()
            ->with(['reservation'])
            ->whereHas('reservation', function ($query) use ($start, $end) {
                $query->whereDate('education_start_date', '<=', $end->format('Y-m-d'))
                    ->whereDate('education_end_date', '>=', $start->format('Y-m-d'));
            })
            ->get();

        $groupApplications = GroupApplication::query()
            ->with(['reservation'])
            ->whereHas('reservation', function ($query) use ($start, $end) {
                $query->whereDate('education_start_date', '<=', $end->format('Y-m-d'))
                    ->whereDate('education_end_date', '>=', $start->format('Y-m-d'));
            })
            ->get();

        $individualSummaries = $this->formatIndividualReservations($individualApplications);
        foreach ($individualSummaries as $summary) {
            $this->spreadReservationAcrossDates($reservationsByDate, 'individual', $summary, $start, $end);
        }

        $groupSummaries = $this->formatGroupReservations($groupApplications);
        foreach ($groupSummaries as $summary) {
            $this->spreadReservationAcrossDates($reservationsByDate, 'group', $summary, $start, $end);
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

    protected function formatIndividualReservations(Collection $applications): array
    {
        return $applications
            ->groupBy('program_reservation_id')
            ->map(function (Collection $apps) {
                $firstApplication = $apps->first();
                $reservation = $firstApplication->relationLoaded('reservation') ? $firstApplication->reservation : null;
                $startDate = $reservation?->education_start_date ?? $firstApplication->participation_date;
                $endDate = $reservation?->education_end_date ?? $firstApplication->participation_date;
                $startDate = $startDate ? Carbon::parse($startDate) : null;
                $endDate = $endDate ? Carbon::parse($endDate) : $startDate;

                $applicantCount = $apps->count();
                $isUnlimited = (bool) ($reservation->is_unlimited_capacity ?? false);
                $capacity = $reservation->capacity ?? null;

                return [
                    'data' => [
                        'id' => $firstApplication->id,
                        'program_reservation_id' => $firstApplication->program_reservation_id,
                        'type' => 'individual',
                        'date' => optional($firstApplication->participation_date)->format('Y-m-d') ?? '',
                        'education_start_date' => $startDate ? $startDate->format('Y-m-d') : null,
                        'education_end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                        'program_name' => $firstApplication->program_name,
                        'applicant_count' => $applicantCount,
                        'capacity' => $capacity,
                        'is_unlimited' => $isUnlimited,
                        'is_closed' => $this->isReservationClosed($reservation, $applicantCount),
                        'applicant_name' => $firstApplication->applicant_name,
                        'application_number' => $firstApplication->application_number,
                    ],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function formatGroupReservations(Collection $applications): array
    {
        return $applications
            ->groupBy('program_reservation_id')
            ->map(function (Collection $apps) {
                $firstApplication = $apps->first();
                $reservation = $firstApplication->relationLoaded('reservation') ? $firstApplication->reservation : null;
                $totalApplicantCount = $apps->sum(function ($application) {
                    return $application->applicant_count ?? 0;
                });
                $startDate = $reservation?->education_start_date ?? $firstApplication->participation_date;
                $endDate = $reservation?->education_end_date ?? $firstApplication->participation_date;
                $startDate = $startDate ? Carbon::parse($startDate) : null;
                $endDate = $endDate ? Carbon::parse($endDate) : $startDate;

                $isUnlimited = (bool) ($reservation->is_unlimited_capacity ?? false);
                $capacity = $reservation->capacity ?? null;

                return [
                    'data' => [
                        'id' => $firstApplication->id,
                        'program_reservation_id' => $firstApplication->program_reservation_id,
                        'type' => 'group',
                        'date' => optional($firstApplication->participation_date)->format('Y-m-d') ?? '',
                        'education_start_date' => $startDate ? $startDate->format('Y-m-d') : null,
                        'education_end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                        'program_name' => $reservation->program_name ?? '',
                        'applicant_count' => $totalApplicantCount,
                        'capacity' => $capacity,
                        'is_unlimited' => $isUnlimited,
                        'is_closed' => $this->isReservationClosed($reservation, $totalApplicantCount),
                        'applicant_name' => $firstApplication->applicant_name,
                        'application_number' => $firstApplication->application_number,
                    ],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function spreadReservationAcrossDates(array &$map, string $type, array $reservation, Carbon $rangeStart, Carbon $rangeEnd): void
    {
        $startDate = $reservation['start_date'];
        $endDate = $reservation['end_date'];

        if (!$startDate instanceof Carbon || !$endDate instanceof Carbon) {
            return;
        }

        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        while ($current <= $end) {
            if ($current < $rangeStart || $current > $rangeEnd) {
                $current->addDay();
                continue;
            }

            $dateKey = $current->format('Y-m-d');
            $map[$dateKey] = $map[$dateKey] ?? ['individual' => [], 'group' => []];

            $map[$dateKey][$type][] = $reservation['data'];

            $current->addDay();
        }
    }

    protected function isReservationClosed($reservation, int $applicantCount): bool
    {
        if (!$reservation) {
            return false;
        }

        $today = Carbon::today();
        $applicationEnd = $reservation->application_end_date;
        if ($applicationEnd) {
            $applicationEndDate = $applicationEnd instanceof Carbon ? $applicationEnd : Carbon::parse($applicationEnd);
            if ($applicationEndDate->lt($today)) {
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

