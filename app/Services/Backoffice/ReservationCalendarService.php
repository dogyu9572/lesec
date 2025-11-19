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

        // 개인 신청 내역 - program_reservation_id별로 그룹화
        $individualApplications = IndividualApplication::query()
            ->whereDate('participation_date', $dateCarbon->format('Y-m-d'))
            ->with(['reservation'])
            ->get()
            ->groupBy('program_reservation_id')
            ->map(function ($applications) {
                $firstApplication = $applications->first();
                $reservation = $firstApplication->reservation;
                
                return [
                    'id' => $firstApplication->id,
                    'program_reservation_id' => $firstApplication->program_reservation_id,
                    'type' => 'individual',
                    'date' => optional($firstApplication->participation_date)->format('Y-m-d') ?? '',
                    'program_name' => $firstApplication->program_name,
                    'applicant_count' => $applications->count(),
                    'capacity' => $reservation->capacity ?? null,
                    'is_unlimited' => $reservation->is_unlimited_capacity ?? false,
                    'applicant_name' => $firstApplication->applicant_name,
                    'application_number' => $firstApplication->application_number,
                ];
            })
            ->values();

        // 단체 신청 내역 - program_reservation_id별로 그룹화
        $groupApplications = GroupApplication::query()
            ->whereDate('participation_date', $dateCarbon->format('Y-m-d'))
            ->with(['reservation'])
            ->get()
            ->groupBy('program_reservation_id')
            ->map(function ($applications) {
                $firstApplication = $applications->first();
                $reservation = $firstApplication->reservation;
                
                // 같은 프로그램의 인원 수 합산
                $totalApplicantCount = $applications->sum(function ($application) {
                    return $application->applicant_count ?? 0;
                });
                
                return [
                    'id' => $firstApplication->id,
                    'program_reservation_id' => $firstApplication->program_reservation_id,
                    'type' => 'group',
                    'date' => optional($firstApplication->participation_date)->format('Y-m-d') ?? '',
                    'program_name' => $reservation->program_name ?? '',
                    'applicant_count' => $totalApplicantCount,
                    'capacity' => $reservation->capacity ?? null,
                    'is_unlimited' => $reservation->is_unlimited_capacity ?? false,
                    'applicant_name' => $firstApplication->applicant_name,
                    'application_number' => $firstApplication->application_number,
                ];
            })
            ->values();

        return [
            'individual' => $individualApplications->toArray(),
            'group' => $groupApplications->toArray(),
        ];
    }
}

