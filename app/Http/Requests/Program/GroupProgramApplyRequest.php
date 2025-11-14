<?php

namespace App\Http\Requests\Program;

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
     */
    public function rules(): array
    {
        return [
            'program_reservation_id' => ['required', 'integer', 'exists:program_reservations,id'],
            'applicant_count' => ['required', 'integer', 'min:4'],
            'agreement' => ['required', 'accepted'],
        ];
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
            'agreement.required' => '승인 안내 내용에 동의해주세요.',
            'agreement.accepted' => '승인 안내 내용에 동의해주세요.',
        ];
    }
}

