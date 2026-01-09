<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class SendSmsVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^01[0-9][0-9]{8,9}$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => '휴대폰 번호를 입력해주세요.',
            'phone.regex' => '올바른 휴대폰 번호 형식이 아닙니다.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone', '');
        $phone = trim($phone);
        $phone = preg_replace('/[-\s]/', '', $phone);
        $this->merge(['phone' => $phone]);
    }
}
