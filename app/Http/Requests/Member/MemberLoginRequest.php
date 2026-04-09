<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class MemberLoginRequest extends FormRequest
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
            'password' => ['required', 'string', 'max:255'],
            'remember_login_id' => ['nullable', 'boolean'],
        ];
    }

    /**
     * 사용자 정의 메시지
     */
    public function messages(): array
    {
        return [
            'login_id.required' => '아이디를 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $loginId = $this->input('login_id');
        $password = $this->input('password');

        $this->merge([
            'login_id' => is_string($loginId) ? trim($loginId) : '',
            'password' => is_string($password) ? $password : '',
            'remember_login_id' => $this->boolean('remember_login_id'),
        ]);
    }
}


