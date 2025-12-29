<?php

namespace App\Services;

use App\Models\IndividualApplication;
use App\Models\ProgramReservation;
use App\Models\GroupApplication;
use App\Models\Member;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramReservationService
{
    /**
     * 월별 프로그램 조회
     */
    public function getProgramsByMonth(string $educationType, string $applicationType, int $year, int $month): Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();
        
        $programs = ProgramReservation::query()
            ->byEducationType($educationType)
            ->byApplicationType($applicationType)
            ->active()
            ->whereDate('education_start_date', '<=', $endOfMonth)
            ->whereDate('education_end_date', '>=', $startOfMonth)
            ->orderBy('education_start_date', 'asc')
            ->get();
        
        return $this->filterProgramsBySchedule($programs);
    }

    /**
     * 개인 신청 프로그램 전체 조회
     */
    public function getIndividualPrograms(string $educationType): Collection
    {
        $programs = ProgramReservation::query()
            ->byEducationType($educationType)
            ->byApplicationType('individual')
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $this->filterProgramsBySchedule($programs);
    }

    public function getIndividualProgramById(int $id): ?ProgramReservation
    {
        return ProgramReservation::query()
            ->where('id', $id)
            ->where('application_type', 'individual')
            ->active()
            ->first();
    }

    public function getGroupProgramById(int $id): ?ProgramReservation
    {
        return ProgramReservation::query()
            ->where('id', $id)
            ->where('application_type', 'group')
            ->active()
            ->first();
    }

    /**
     * 일정관리 일정과 겹치는 프로그램 필터링 (사용자 페이지에서 숨김)
     */
    private function filterProgramsBySchedule(Collection $programs): Collection
    {
        if ($programs->isEmpty()) {
            return $programs;
        }

        // disable_application=true인 일정 조회
        $disabledSchedules = Schedule::query()
            ->disableApplication()
            ->get();

        if ($disabledSchedules->isEmpty()) {
            return $programs;
        }

        // 각 프로그램의 교육 기간과 일정이 겹치는지 확인
        return $programs->filter(function (ProgramReservation $program) use ($disabledSchedules) {
            if (!$program->education_start_date || !$program->education_end_date) {
                return true; // 날짜가 없으면 필터링하지 않음
            }

            $programStart = Carbon::parse($program->education_start_date);
            $programEnd = Carbon::parse($program->education_end_date);

            // 일정과 겹치는지 확인
            foreach ($disabledSchedules as $schedule) {
                if (!$schedule->start_date || !$schedule->end_date) {
                    continue;
                }

                $scheduleStart = Carbon::parse($schedule->start_date);
                $scheduleEnd = Carbon::parse($schedule->end_date);

                // 기간이 겹치는지 확인
                if ($programStart->lte($scheduleEnd) && $programEnd->gte($scheduleStart)) {
                    return false; // 겹치면 필터링 (제외)
                }
            }

            return true; // 겹치지 않으면 포함
        })->values();
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
            if ($month !== null) {
                $startDate = $program->education_start_date;
                if (!$startDate instanceof Carbon || (int) $startDate->month !== $month) {
                    return false;
                }
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
            ->filter(fn (ProgramReservation $program) => $program->education_start_date instanceof Carbon)
            ->map(function (ProgramReservation $program) {
                $startDate = $program->education_start_date;
                return $startDate instanceof Carbon ? (int) $startDate->month : null;
            })
            ->filter()
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

    public function createIndividualApplication(ProgramReservation $reservation, array $data, ?Member $member = null, bool $allowAdminOverride = false): IndividualApplication
    {
        if ($reservation->application_type !== 'individual') {
            throw new \InvalidArgumentException('개인 신청 프로그램이 아닙니다.');
        }

        if (!$reservation->is_active) {
            throw new \InvalidArgumentException('비활성화된 프로그램입니다.');
        }

        if (!$allowAdminOverride && $reservation->application_start_date instanceof Carbon && $reservation->application_start_date->greaterThan(now())) {
            throw new \InvalidArgumentException('접수 예정인 프로그램입니다.');
        }

        $requestedCount = 1;
        if (!$reservation->is_unlimited_capacity) {
            $remaining = $reservation->remaining_capacity;
            if ($remaining < $requestedCount) {
                throw new \InvalidArgumentException('잔여 정원이 부족합니다.');
            }
        }

        $participationDateValue = $data['participation_date'] ?? null;
        if (empty($participationDateValue)) {
            $startDate = $reservation->education_start_date;
            if ($startDate instanceof Carbon) {
                $participationDateValue = $startDate->toDateString();
            } else {
                throw new \InvalidArgumentException('참가일 정보를 확인할 수 없습니다.');
            }
        }

        $participationDate = Carbon::parse($participationDateValue);

        $memberModel = $member;
        if (!$memberModel && !empty($data['member_id'])) {
             $memberModel = Member::find($data['member_id']);
         }
 
         if ($memberModel && $memberModel->member_type === 'student') {
            $programPrefix = Str::upper(Str::substr($reservation->program_name, 0, 2));

            $existingApplication = IndividualApplication::query()
                ->where('member_id', $memberModel->id)
                ->get(['program_name'])
                ->contains(function (IndividualApplication $application) use ($programPrefix) {
                    $existingPrefix = Str::upper(Str::substr($application->program_name, 0, 2));
                    return $existingPrefix === $programPrefix;
                });

            if ($existingApplication) {
                throw new \InvalidArgumentException('이미 동일한 프로그램을 신청하셨습니다. 다른 프로그램을 선택해주세요.');
            }
         }

        $applicantContact = $this->normalizeContactNumber($data['applicant_contact'] ?? '');
        if ($applicantContact === null) {
            throw new \InvalidArgumentException('신청자 연락처를 정확히 입력해주세요.');
        }

        $guardianContact = $this->normalizeContactNumber($data['guardian_contact'] ?? '');

        $participationFeeValue = $data['participation_fee'] ?? $reservation->education_fee;
        if ($participationFeeValue === '' || $participationFeeValue === null) {
            $participationFeeValue = null;
        } else {
            $participationFeeValue = (int) preg_replace('/[^0-9]/', '', (string) $participationFeeValue);
        }

        $programNameValue = $data['program_name'] ?? $reservation->program_name;

        return DB::transaction(function () use ($reservation, $data, $requestedCount, $applicantContact, $guardianContact, $participationDate, $memberModel, $participationFeeValue, $programNameValue) {
            $application = IndividualApplication::create([
                'program_reservation_id' => $reservation->id,
                'member_id' => $memberModel?->id ?? $data['member_id'] ?? null,
                'application_number' => $this->generateIndividualApplicationNumber($reservation, $memberModel),
                'education_type' => $reservation->education_type,
                'reception_type' => $reservation->reception_type ?? 'first_come',
                'program_name' => $programNameValue,
                'participation_date' => $participationDate,
                'participation_fee' => $participationFeeValue,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? IndividualApplication::PAYMENT_STATUS_UNPAID,
                'draw_result' => $data['draw_result'] ?? IndividualApplication::DRAW_RESULT_PENDING,
                'applicant_name' => $data['applicant_name'],
                'applicant_school_name' => $data['applicant_school_name'] ?? $memberModel?->school_name,
                'applicant_grade' => $data['applicant_grade'] ?? $memberModel?->grade,
                'applicant_class' => $data['applicant_class'] ?? $memberModel?->class_number,
                'applicant_contact' => $applicantContact,
                'guardian_contact' => $guardianContact,
                'applied_at' => now(),
            ]);

            if (!$reservation->is_unlimited_capacity) {
                $reservation->increment('applied_count', $requestedCount);
            }

            return $application;
        });
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
    public function generateCalendar(int $year, int $month, array $programsByDate, array $disabledDates = []): array
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
                'is_disabled_date' => false,
                'programs' => []
            ];
        }
        
        // 현재 달 날짜
        while ($day <= $daysInMonth) {
            $dateKey = sprintf('%d-%02d-%02d', $year, $month, $day);
            $isDisabled = isset($disabledDates[$dateKey]);
            $disabledDateInfo = $isDisabled && is_array($disabledDates[$dateKey]) ? $disabledDates[$dateKey] : null;
            $week[] = [
                'day' => $day,
                'disabled' => $isDisabled,
                'is_disabled_date' => $isDisabled,
                'disabled_date_title' => $disabledDateInfo['title'] ?? null,
                'programs' => $isDisabled ? [] : ($programsByDate[$dateKey] ?? [])
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
                'is_disabled_date' => false,
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

    private function generateIndividualApplicationNumber(ProgramReservation $reservation, ?Member $member = null): string
    {
        $year = $this->getAcademicYear();

        // 회원 정보 기반으로 접두사 결정
        if ($member && $member->member_type === 'teacher') {
            // 교사는 개인 신청 불가하지만 만약의 경우 T
            $prefix = 'T';
        } elseif ($member && $member->member_type === 'student') {
            // 학생은 학교급(school_level)으로 판단
            $schoolLevel = $member->school?->school_level;
            if ($schoolLevel === 'high') {
                $prefix = 'H';
            } elseif ($schoolLevel === 'middle') {
                $prefix = 'M';
            } else {
                // school_level이 없으면 프로그램 타입으로 판단
                $prefix = str_starts_with($reservation->education_type, 'high') ? 'H' : 'M';
            }
        } else {
            // 회원 정보가 없으면 프로그램 타입으로 판단
            $prefix = str_starts_with($reservation->education_type, 'high') ? 'H' : 'M';
        }

        $latestNumber = IndividualApplication::query()
            ->where('application_number', 'like', $prefix . $year . '%')
            ->orderBy('application_number', 'desc')
            ->lockForUpdate()
            ->value('application_number');
 
         $nextSequence = 1;
         if ($latestNumber) {
            if (preg_match('/^' . preg_quote($prefix . $year, '/') . '(\d{4})$/', $latestNumber, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }
         }
 
         return sprintf('%s%s%04d', $prefix, $year, $nextSequence);
    }

    private function extractProgramPrefix(?string $programName): string
    {
        if (empty($programName)) {
            return '';
        }

        $trimmed = trim($programName);

        if ($trimmed === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($trimmed, 0, 2));
    }

    private function normalizeContactNumber(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $number);

        return $digits !== '' ? $digits : null;
    }

    /**
     * 3월 기준 학사연도 계산
     */
    private function getAcademicYear(): int
    {
        $now = now();
        $year = $now->year;
        
        // 3월 1일 이전이면 전년도 사용
        if ($now->month < 3) {
            $year--;
        }
        
        return $year;
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

    /**
     * 단체 신청 생성
     */
    public function createGroupApplication(ProgramReservation $reservation, array $data): GroupApplication
    {
        return DB::transaction(function () use ($reservation, $data) {
            $requestedCount = $data['applicant_count'] ?? 10;

            if (!$reservation->is_active) {
                throw new \InvalidArgumentException('비활성화된 프로그램입니다.');
            }

            $now = now();
            if ($reservation->application_start_date && $now->lt($reservation->application_start_date)) {
                throw new \InvalidArgumentException('접수 예정인 프로그램입니다.');
            }

            if ($reservation->application_end_date && $now->gt($reservation->application_end_date)) {
                throw new \InvalidArgumentException('접수 마감된 프로그램입니다.');
            }

            if (!$reservation->is_unlimited_capacity) {
                $remainingCapacity = $reservation->remaining_capacity;
                if ($remainingCapacity < $requestedCount) {
                    throw new \InvalidArgumentException('잔여 정원이 부족합니다. (잔여: ' . $remainingCapacity . '명)');
                }
            }

            $applicationNumber = $this->generateGroupApplicationNumber($reservation);

            $applicantContact = $this->normalizeContactNumber($data['applicant_contact'] ?? null);

            $paymentMethods = $reservation->payment_methods ?? [];
            if (!is_array($paymentMethods)) {
                $paymentMethods = [];
            }

            $application = GroupApplication::create([
                'program_reservation_id' => $reservation->id,
                'application_number' => $applicationNumber,
                'education_type' => $reservation->education_type,
                'payment_methods' => $paymentMethods,
                'payment_method' => $data['payment_method'] ?? null,
                'application_status' => $data['application_status'] ?? 'pending',
                'reception_status' => $data['reception_status'] ?? 'application',
                'applicant_name' => $data['applicant_name'] ?? null,
                'member_id' => $data['member_id'] ?? null,
                'applicant_contact' => $applicantContact,
                'school_level' => $data['school_level'] ?? null,
                'school_name' => $data['school_name'] ?? null,
                'applicant_count' => $requestedCount,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'participation_fee' => $data['participation_fee'] ?? $reservation->education_fee,
                'participation_date' => $data['participation_date'] ?? null,
                'applied_at' => now(),
            ]);

            if (!$reservation->is_unlimited_capacity) {
                $reservation->increment('applied_count', $requestedCount);
            }

            return $application;
        });
    }

    /**
     * 단체 신청번호 생성
     */
    private function generateGroupApplicationNumber(ProgramReservation $reservation): string
    {
        $year = $this->getAcademicYear();
        $prefix = 'T'; // 교사만 단체 신청 가능

        $latestNumber = GroupApplication::query()
            ->where('application_number', 'like', $prefix . $year . '%')
            ->orderBy('application_number', 'desc')
            ->lockForUpdate()
            ->value('application_number');

        $nextSequence = 1;
        if ($latestNumber && preg_match('/^' . preg_quote($prefix . $year, '/') . '(\d{4})$/', $latestNumber, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%s%04d', $prefix, $year, $nextSequence);
    }
}

