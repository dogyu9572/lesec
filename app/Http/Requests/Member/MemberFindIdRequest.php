<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class MemberFindIdRequest extends FormRequest
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
            'name' => trim((string) $this->input('name')),
            'contact' => preg_replace('/[^0-9]/', '', (string) $this->input('contact')),
        ]);
    }
}


