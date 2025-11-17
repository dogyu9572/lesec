<?php

namespace App\Services\Backoffice;

use App\Models\IndividualApplication;
use App\Models\GroupApplication;
use App\Models\ProgramReservation;
use App\Services\ProgramReservationService;
use App\Services\Backoffice\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationCalendarService
{
    protected ProgramReservationService $programReservationService;
    protected ScheduleService $scheduleService;

    public function __construct(
        ProgramReservationService $programReservationService,
        ScheduleService $scheduleService
    ) {
        $this->programReservationService = $programReservationService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * 월별 프로그램 조회 (개인/단체 모두)
     */
    public function getProgramsByMonth(int $year, int $month): Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        return ProgramReservation::query()
            ->active()
            ->whereDate('education_start_date', '<=', $endOfMonth)
            ->whereDate('education_end_date', '>=', $startOfMonth)
            ->orderBy('education_start_date', 'asc')
            ->get();
    }

    /**
     * 날짜별 프로그램 그룹화
     */
    public function groupProgramsByDate(Collection $programs): array
    {
        return $this->programReservationService->groupProgramsByDate($programs);
    }

    /**
     * 캘린더 구조 생성
     */
    public function generateCalendar(int $year, int $month, array $programsByDate): array
    {
        // 교육신청 불가 일정 조회
        $disabledDates = $this->scheduleService->getDisabledDates($year, $month);
        
        return $this->programReservationService->generateCalendar($year, $month, $programsByDate, $disabledDates);
    }

    /**
     * 특정 날짜의 예약 내역 조회
     */
    public function getReservationsByDate(string $date): array
    {
        $dateCarbon = Carbon::parse($date);

        // 개인 신청 내역
        $individualApplications = IndividualApplication::query()
            ->whereDate('participation_date', $dateCarbon->format('Y-m-d'))
            ->with(['reservation'])
            ->orderBy('participation_date', 'asc')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'type' => 'individual',
                    'date' => optional($application->participation_date)->format('Y-m-d') ?? '',
                    'program_name' => $application->program_name,
                    'applicant_count' => 1,
                    'capacity' => $application->reservation->capacity ?? null,
                    'is_unlimited' => $application->reservation->is_unlimited_capacity ?? false,
                    'applicant_name' => $application->applicant_name,
                    'application_number' => $application->application_number,
                ];
            });

        // 단체 신청 내역
        $groupApplications = GroupApplication::query()
            ->whereDate('participation_date', $dateCarbon->format('Y-m-d'))
            ->with(['reservation'])
            ->orderBy('participation_date', 'asc')
            ->get()
            ->map(function ($application) {
                $reservation = $application->reservation;
                return [
                    'id' => $application->id,
                    'type' => 'group',
                    'date' => optional($application->participation_date)->format('Y-m-d') ?? '',
                    'program_name' => $reservation->program_name ?? '',
                    'applicant_count' => $application->applicant_count ?? 0,
                    'capacity' => $reservation->capacity ?? null,
                    'is_unlimited' => $reservation->is_unlimited_capacity ?? false,
                    'applicant_name' => $application->applicant_name,
                    'application_number' => $application->application_number,
                ];
            });

        return [
            'individual' => $individualApplications->toArray(),
            'group' => $groupApplications->toArray(),
        ];
    }
}

