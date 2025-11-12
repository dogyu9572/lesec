<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class MemberFindPasswordRequest extends FormRequest
{
    /**
     * 요청 권한 여부
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 검증 규칙
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * 사용자 정의 메시지
     */
    public function messages(): array
    {
        return [
            'login_id.required' => '아이디를 입력해주세요.',
            'name.required' => '이름을 입력해주세요.',
            'contact.required' => '휴대폰번호를 입력해주세요.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'login_id' => trim((string) $this->input('login_id')),
            'name' => trim((string) $this->input('name')),
            'contact' => preg_replace('/[^0-9]/', '', (string) $this->input('contact')),
        ]);
    }
}


