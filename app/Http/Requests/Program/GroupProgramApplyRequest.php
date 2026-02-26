<?php

namespace App\Http\Requests\Program;

use App\Models\ProgramReservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupProgramApplyRequest extends FormRequest
{
    /**
     * 요청 권한 확인
     */
    public function authorize(): bool
    {
        return auth('member')->check();
    }

    /**
     * 유효성 검사 규칙
     */
    public function rules(): array
    {
        $rules = [
            'program_reservation_id' => ['required', 'integer', 'exists:program_reservations,id'],
            'applicant_count' => ['required', 'integer', 'min:4'],
            'agreement' => ['required', 'accepted'],
        ];

        // 방학 프로그램인 경우 정원을 모두 채워야 함
        $reservationId = $this->input('program_reservation_id');
        if ($reservationId) {
            $reservation = ProgramReservation::find($reservationId);
            if ($reservation) {
                $isVacation = in_array($reservation->education_type, ['middle_vacation', 'high_vacation'], true);
                if ($isVacation && !$reservation->is_unlimited_capacity) {
                    $capacity = (int) ($reservation->capacity ?? 0);
                    $applied = (int) ($reservation->applied_count ?? 0);
                    // 신청가능 상태면 정원을 모두 채워야 하고, 잔여석 신청 가능 상태면 잔여석을 모두 채워야 함
                    if ($applied === 0) {
                        // 신청가능 상태: 정원을 모두 채워야 함
                        if ($capacity > 0) {
                            $rules['applicant_count'][] = Rule::in([$capacity]);
                        }
                    } else {
                        // 잔여석 신청 가능 상태: 잔여석을 모두 채워야 함
                        $remaining = $capacity - $applied;
                        if ($remaining > 0) {
                            $rules['applicant_count'][] = Rule::in([$remaining]);
                        }
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * 에러 메시지 커스터마이징
     */
    public function messages(): array
    {
        $reservationId = $this->input('program_reservation_id');
        $applicantCount = $this->input('applicant_count');
        $requiredCount = null;
        $statusText = '정원';

        if ($reservationId) {
            $reservation = ProgramReservation::find($reservationId);
            if ($reservation) {
                $isVacation = in_array($reservation->education_type, ['middle_vacation', 'high_vacation'], true);
                if ($isVacation && !$reservation->is_unlimited_capacity) {
                    $capacity = (int) ($reservation->capacity ?? 0);
                    $applied = (int) ($reservation->applied_count ?? 0);
                    // 신청가능 상태면 정원을 모두 채워야 하고, 잔여석 신청 가능 상태면 잔여석을 모두 채워야 함
                    if ($applied === 0) {
                        // 신청가능 상태: 정원을 모두 채워야 함
                        $requiredCount = $capacity;
                        $statusText = '정원';
                    } else {
                        // 잔여석 신청 가능 상태: 잔여석을 모두 채워야 함
                        $remaining = $capacity - $applied;
                        $requiredCount = $remaining;
                        $statusText = '잔여석';
                    }
                }
            }
        }

        $messages = [
            'program_reservation_id.required' => '프로그램을 선택해주세요.',
            'program_reservation_id.exists' => '유효하지 않은 프로그램입니다.',
            'applicant_count.required' => '신청 인원을 입력해주세요.',
            'applicant_count.integer' => '신청 인원은 숫자로 입력해주세요.',
            'applicant_count.min' => '단체 신청은 최소 4명 이상이어야 합니다.',
            'agreement.required' => '승인 안내 내용에 동의해주세요.',
            'agreement.accepted' => '승인 안내 내용에 동의해주세요.',
        ];

        if ($requiredCount && $requiredCount > 0) {
            $messages['applicant_count.in'] = "방학 프로그램은 {$statusText}({$requiredCount}명)을 모두 채워야 신청 가능합니다.";
        }

        return $messages;
    }
}
