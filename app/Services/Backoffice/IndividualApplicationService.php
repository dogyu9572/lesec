<?php

namespace App\Services\Backoffice;

use App\Models\ProgramApplication;
use App\Models\ProgramReservation;
use App\Models\Member;
use App\Services\ProgramReservationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use InvalidArgumentException;

class IndividualApplicationService
{
    protected ProgramReservationService $programReservationService;

    public function __construct(ProgramReservationService $programReservationService)
    {
        $this->programReservationService = $programReservationService;
    }

    /**
     * 필터링된 개인 신청 목록 조회
     */
    public function getFilteredApplications(Request $request): LengthAwarePaginator
    {
        $query = ProgramApplication::query()
            ->with(['reservation', 'member'])
            ->orderBy('created_at', 'desc');

        // 신청유형 필터
        if ($request->filled('reception_type')) {
            $query->where('reception_type', $request->reception_type);
        }

        // 교육유형 필터
        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        // 참가일정 기간 검색
        if ($request->filled('participation_start_date')) {
            $query->where('participation_date', '>=', $request->participation_start_date);
        }
        if ($request->filled('participation_end_date')) {
            $query->where('participation_date', '<=', $request->participation_end_date);
        }

        // 신청기간 검색
        if ($request->filled('applied_start_date')) {
            $query->whereDate('applied_at', '>=', $request->applied_start_date);
        }
        if ($request->filled('applied_end_date')) {
            $query->whereDate('applied_at', '<=', $request->applied_end_date);
        }

        // 추첨결과 필터
        if ($request->filled('draw_result')) {
            $query->where('draw_result', $request->draw_result);
        }

        // 결제상태 필터
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 검색어 (신청자명/학교명/프로그램명/신청번호)
        if ($request->filled('search_keyword') && $request->filled('search_type')) {
            $keyword = $request->search_keyword;
            if ($request->search_type === 'applicant_name') {
                $query->where('applicant_name', 'like', "%{$keyword}%");
            } elseif ($request->search_type === 'school_name') {
                $query->where('applicant_school_name', 'like', "%{$keyword}%");
            } elseif ($request->search_type === 'program_name') {
                $query->where('program_name', 'like', "%{$keyword}%");
            } elseif ($request->search_type === 'application_number') {
                $query->where('application_number', 'like', "%{$keyword}%");
            } else {
                // 전체 검색
                $query->where(function ($q) use ($keyword) {
                    $q->where('applicant_name', 'like', "%{$keyword}%")
                      ->orWhere('applicant_school_name', 'like', "%{$keyword}%")
                      ->orWhere('program_name', 'like', "%{$keyword}%")
                      ->orWhere('application_number', 'like', "%{$keyword}%");
                });
            }
        }

        $perPage = $request->input('per_page', 20);
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 신청유형 목록 반환
     */
    public function getReceptionTypes(): array
    {
        return ProgramApplication::RECEPTION_TYPE_LABELS;
    }

    /**
     * 교육유형 목록 반환
     */
    public function getEducationTypes(): array
    {
        return ProgramApplication::EDUCATION_TYPE_LABELS;
    }

    /**
     * 추첨결과 목록 반환
     */
    public function getDrawResults(): array
    {
        return [
            ProgramApplication::DRAW_RESULT_PENDING => ProgramApplication::DRAW_RESULT_LABELS[ProgramApplication::DRAW_RESULT_PENDING],
            ProgramApplication::DRAW_RESULT_WIN => ProgramApplication::DRAW_RESULT_LABELS[ProgramApplication::DRAW_RESULT_WIN],
            ProgramApplication::DRAW_RESULT_FAIL => ProgramApplication::DRAW_RESULT_LABELS[ProgramApplication::DRAW_RESULT_FAIL],
        ];
    }

    /**
     * 결제상태 목록 반환
     */
    public function getPaymentStatuses(): array
    {
        return [
            ProgramApplication::PAYMENT_STATUS_UNPAID => ProgramApplication::PAYMENT_STATUS_LABELS[ProgramApplication::PAYMENT_STATUS_UNPAID],
            ProgramApplication::PAYMENT_STATUS_PAID => ProgramApplication::PAYMENT_STATUS_LABELS[ProgramApplication::PAYMENT_STATUS_PAID],
        ];
    }

    /**
     * 결제방법 목록 반환
     */
    public function getPaymentMethods(): array
    {
        return [
            'bank_transfer' => '무통장 입금',
            'on_site_card' => '방문 카드결제',
            'online_card' => '온라인 카드결제',
        ];
    }

    /**
     * 개인 신청 신규 생성
     */
    public function createApplication(array $data): ProgramApplication
    {
        $reservationId = $data['program_reservation_id'] ?? null;

        if (!$reservationId) {
            throw new InvalidArgumentException('프로그램을 선택해 주세요.');
        }

        $reservation = ProgramReservation::query()
            ->byApplicationType('individual')
            ->find($reservationId);

        if (!$reservation) {
            throw new InvalidArgumentException('프로그램 정보를 찾을 수 없습니다.');
        }

        $member = null;
        if (!empty($data['member_id'])) {
            $member = Member::find($data['member_id']);
            if (!$member) {
                throw new InvalidArgumentException('회원 정보를 찾을 수 없습니다.');
            }
        }

        $application = $this->programReservationService->createIndividualApplication(
            $reservation,
            [
                'member_id' => $data['member_id'] ?? null,
                'participation_date' => $data['participation_date'] ?? null,
                'program_name' => $data['program_name'] ?? $reservation->program_name,
                'participation_fee' => $data['participation_fee'] ?? $reservation->education_fee,
                'applicant_name' => $data['applicant_name'],
                'applicant_school_name' => $data['applicant_school_name'] ?? null,
                'applicant_grade' => $data['applicant_grade'] ?? null,
                'applicant_class' => $data['applicant_class'] ?? null,
                'applicant_contact' => $data['applicant_contact'],
                'guardian_contact' => $data['guardian_contact'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? ProgramApplication::PAYMENT_STATUS_UNPAID,
                'draw_result' => $data['draw_result'] ?? null,
            ],
            $member,
            true
        );

        $this->updateApplication($application, $data);

        return $application->refresh();
    }

    /**
     * 신청 정보 업데이트
     */
    public function updateApplication(ProgramApplication $application, array $data): bool
    {
        $updateData = [];

        $fields = [
            'reception_type',
            'education_type',
            'draw_result',
            'payment_status',
            'payment_method',
            'program_reservation_id',
            'member_id',
            'program_name',
            'participation_date',
            'participation_fee',
            'applicant_name',
            'applicant_school_name',
            'applicant_grade',
            'applicant_class',
            'applicant_contact',
            'guardian_contact',
        ];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'program_reservation_id':
                case 'member_id':
                    $updateData[$field] = $value !== null && $value !== '' ? (int) $value : null;
                    break;
                case 'participation_date':
                    $updateData[$field] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
                    break;
                case 'participation_fee':
                    $updateData[$field] = $value !== null && $value !== '' ? (int) $value : null;
                    break;
                case 'applicant_grade':
                case 'applicant_class':
                    $updateData[$field] = $value !== null && $value !== '' ? (int) $value : null;
                    break;
                default:
                    $updateData[$field] = $value === '' ? null : $value;
                    break;
            }
        }

        if (empty($updateData)) {
            return true;
        }

        return $application->update($updateData);
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

    /**
     * 신청 삭제
     */
    public function deleteApplication(ProgramApplication $application): bool
    {
        // 신청 삭제 시 프로그램 신청 인원 감소
        if ($application->reservation && !$application->reservation->is_unlimited_capacity) {
            $application->reservation->decrement('applied_count', 1);
        }

        return $application->delete();
    }

    /**
     * 회원 검색
     */
    public function searchMembers(Request $request)
    {
        $query = \App\Models\Member::query();

        if ($request->filled('member_type') && $request->member_type !== 'all') {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('search_term')) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('login_id', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * 프로그램 검색
     */
    public function searchPrograms(Request $request)
    {
        $query = \App\Models\ProgramReservation::query()
            ->byApplicationType('individual')
            ->active()
            ->orderBy('created_at', 'desc');

        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where('program_name', 'like', "%{$keyword}%");
        }

        $perPage = (int) $request->get('per_page', 10);

        return $query->paginate($perPage)->through(function ($program) {
            return [
                'id' => $program->id,
                'program_name' => $program->program_name,
                'education_type' => $program->education_type,
                'education_start_date' => optional($program->education_start_date)->format('Y-m-d'),
                'education_fee' => $program->education_fee,
            ];
        });
    }
}

