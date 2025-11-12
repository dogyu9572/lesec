<?php

namespace App\Services;

use App\Models\ProgramReservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ProgramReservationService
{
    /**
     * 월별 프로그램 조회
     */
    public function getProgramsByMonth(string $educationType, string $applicationType, int $year, int $month): Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();
        
        return ProgramReservation::query()
            ->byEducationType($educationType)
            ->byApplicationType($applicationType)
            ->active()
            ->whereDate('education_start_date', '<=', $endOfMonth)
            ->whereDate('education_end_date', '>=', $startOfMonth)
            ->orderBy('education_start_date', 'asc')
            ->get();
    }

    /**
     * 개인 신청 프로그램 전체 조회
     */
    public function getIndividualPrograms(string $educationType): Collection
    {
        return ProgramReservation::query()
            ->byEducationType($educationType)
            ->byApplicationType('individual')
            ->active()
            ->orderBy('education_start_date', 'desc')
            ->get();
    }

    /**
     * 개인 신청 프로그램 필터링
     */
    public function filterIndividualPrograms(
        SupportCollection $programs,
        ?int $month,
        ?string $programName,
        ?string $status
    ): SupportCollection {
        return $programs->filter(function (ProgramReservation $program) use ($month, $programName, $status) {
            if ($month !== null && (int) $program->education_start_date->month !== $month) {
                return false;
            }

            if ($programName !== null && $programName !== '' && $program->program_name !== $programName) {
                return false;
            }

            if ($status !== null && $status !== '') {
                if ($program->individual_action_type !== $status) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * 개인 신청 프로그램 월 옵션 반환
     */
    public function getIndividualProgramMonths(SupportCollection $programs): SupportCollection
    {
        return $programs
            ->map(fn (ProgramReservation $program) => (int) $program->education_start_date->month)
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * 개인 신청 프로그램명 옵션 반환
     */
    public function getIndividualProgramNames(SupportCollection $programs): SupportCollection
    {
        return $programs
            ->map(fn (ProgramReservation $program) => $program->program_name)
            ->unique()
            ->values();
    }

    /**
     * 날짜별 프로그램 그룹화 (교육 기간 내 모든 날짜 포함)
     */
    public function groupProgramsByDate(Collection $programs): array
    {
        $programsByDate = [];
        
        foreach ($programs as $program) {
            $startDate = $program->education_start_date;
            $endDate = $program->education_end_date;
            
            // 시작일부터 종료일까지 모든 날짜에 프로그램 추가
            $currentDate = Carbon::parse($startDate);
            $endDateCarbon = Carbon::parse($endDate);
            
            while ($currentDate <= $endDateCarbon) {
                $dateKey = $currentDate->format('Y-m-d');
                if (!isset($programsByDate[$dateKey])) {
                    $programsByDate[$dateKey] = [];
                }
                $programsByDate[$dateKey][] = $program;
                $currentDate->addDay();
            }
        }
        
        return $programsByDate;
    }

    /**
     * 캘린더 구조 생성
     */
    public function generateCalendar(int $year, int $month, array $programsByDate): array
    {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $dayOfWeek = date('w', $firstDay);
        
        $calendar = [];
        $day = 1;
        $week = [];
        
        // 이전 달 날짜로 채우기
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
        
        for ($i = 0; $i < $dayOfWeek; $i++) {
            $week[] = [
                'day' => $daysInPrevMonth - ($dayOfWeek - $i - 1),
                'disabled' => true,
                'programs' => []
            ];
        }
        
        // 현재 달 날짜
        while ($day <= $daysInMonth) {
            $dateKey = sprintf('%d-%02d-%02d', $year, $month, $day);
            $week[] = [
                'day' => $day,
                'disabled' => false,
                'programs' => $programsByDate[$dateKey] ?? []
            ];
            
            if (count($week) == 7) {
                $calendar[] = $week;
                $week = [];
            }
            
            $day++;
        }
        
        // 다음 달 날짜로 채우기
        $nextDay = 1;
        while (count($week) < 7) {
            $week[] = [
                'day' => $nextDay,
                'disabled' => true,
                'programs' => []
            ];
            $nextDay++;
        }
        
        if (count($week) > 0) {
            $calendar[] = $week;
        }
        
        return $calendar;
    }

    /**
     * 특정 프로그램 조회
     */
    public function getProgramById(int $id): ?ProgramReservation
    {
        return ProgramReservation::find($id);
    }

    /**
     * 신청 가능 여부 확인
     */
    public function checkAvailability(int $programId, int $requestedCount): array
    {
        $program = $this->getProgramById($programId);
        
        if (!$program) {
            return [
                'available' => false,
                'message' => '프로그램을 찾을 수 없습니다.',
            ];
        }

        if (!$program->is_active) {
            return [
                'available' => false,
                'message' => '비활성화된 프로그램입니다.',
            ];
        }

        if ($program->is_unlimited_capacity) {
            return [
                'available' => true,
                'message' => '신청 가능합니다.',
            ];
        }

        $remainingCapacity = $program->remaining_capacity;

        if ($remainingCapacity < $requestedCount) {
            return [
                'available' => false,
                'message' => '잔여 정원이 부족합니다.',
                'remaining' => $remainingCapacity,
            ];
        }

        return [
            'available' => true,
            'message' => '신청 가능합니다.',
            'remaining' => $remainingCapacity,
        ];
    }

    /**
     * 접수유형 자동 판정
     */
    public function calculateReceptionStatus(int $appliedCount, ?int $capacity, bool $isUnlimited = false): string
    {
        if ($isUnlimited) {
            return 'application';
        }

        $capacity = $capacity ?? 0;

        if ($appliedCount >= $capacity) {
            return 'closed';
        } elseif ($appliedCount > 0) {
            return 'remaining';
        } else {
            return 'application';
        }
    }
}

