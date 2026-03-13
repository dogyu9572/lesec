<?php

namespace App\Http\Requests\Program;

use App\Models\ProgramReservation;
use Illuminate\Foundation\Http\FormRequest;

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
     * - 최소 4명, 정원/잔여석 초과만 막음 (방학도 정원 이내면 인원 제한 없음)
     */
    public function rules(): array
    {
        $rules = [
            'program_reservation_id' => ['required', 'integer', 'exists:program_reservations,id'],
            'applicant_count' => ['required', 'integer', 'min:4'],
            'agreement' => ['required', 'accepted'],
        ];

        $reservationId = $this->input('program_reservation_id');
        if ($reservationId) {
            $reservation = ProgramReservation::find($reservationId);
            if ($reservation && !$reservation->is_unlimited_capacity) {
                $max = $reservation->remaining_capacity;
                if ($max > 0) {
                    $rules['applicant_count'][] = 'max:' . $max;
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
        return [
            'program_reservation_id.required' => '프로그램을 선택해주세요.',
            'program_reservation_id.exists' => '유효하지 않은 프로그램입니다.',
            'applicant_count.required' => '신청 인원을 입력해주세요.',
            'applicant_count.integer' => '신청 인원은 숫자로 입력해주세요.',
            'applicant_count.min' => '단체 신청은 최소 4명 이상이어야 합니다.',
            'applicant_count.max' => '신청 인원은 정원(또는 잔여석)보다 많을 수 없습니다.',
            'agreement.required' => '승인 안내 내용에 동의해주세요.',
            'agreement.accepted' => '승인 안내 내용에 동의해주세요.',
        ];
    }
}
