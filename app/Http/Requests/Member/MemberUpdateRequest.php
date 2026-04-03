<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class MemberUpdateRequest extends FormRequest
{
    /**
     * 요청 권한 여부
     */
    public function authorize(): bool
    {
        return Auth::guard('member')->check();
    }

    /**
     * 검증 규칙
     */
    public function rules(): array
    {
        $passwordRules = ['nullable', 'string'];
        $passwordValue = trim((string) $this->input('password'));
        $hasPassword = $this->filled('password') && $passwordValue !== '';

        if ($hasPassword) {
            $passwordRules = array_merge($passwordRules, [
                'min:8',
                'max:20',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ]);
        }

        $rules = [
            'current_password' => ['required', 'string'],
            'password' => $passwordRules,
            'email' => ['required', 'string', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'grade' => ['nullable', 'integer', 'between:1,12'],
            'class_number' => ['nullable', 'integer', 'between:1,30'],
            'notification_agree' => ['required', 'accepted'],
        ];

        return $rules;
    }

    /**
     * 사용자 정의 메시지
     */
    public function messages(): array
    {
        return [
            'current_password.required' => '현재 비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 8자 이상 입력해주세요.',
            'password.max' => '비밀번호는 20자 이하로 입력해주세요.',
            'password.password' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.letters' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.numbers' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.symbols' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'school_name.required' => '학교명을 입력해주세요.',
            'school_id.exists' => '선택한 학교 정보가 올바르지 않습니다.',
            'grade.between' => '학년은 1~12 사이의 숫자로 선택해주세요.',
            'class_number.between' => '반은 1~30 사이의 숫자로 선택해주세요.',
            'notification_agree.required' => '수신 동의 항목에 동의해주세요.',
            'notification_agree.accepted' => '수신 동의 항목에 동의해주세요.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $emailId = trim((string) $this->input('email_id'));
        $emailDomainSelected = (string) $this->input('email_domain');
        $emailDomainCustom = trim((string) $this->input('email_domain_custom'));

        $emailDomain = $emailDomainSelected === 'custom' ? $emailDomainCustom : $emailDomainSelected;
        $email = null;

        if ($emailId !== '' && $emailDomain !== '') {
            $email = $emailId . '@' . $emailDomain;
        } elseif ($this->filled('email')) {
            $email = trim((string) $this->input('email'));
        }

        $this->merge([
            'email' => $email,
            'city' => trim((string) $this->input('city')) ?: null,
            'district' => trim((string) $this->input('district')) ?: null,
            'school_name' => trim((string) $this->input('school_name')),
            'school_id' => $this->filled('school_id') ? (int) $this->input('school_id') : null,
            'grade' => $this->filled('grade') ? (int) $this->input('grade') : null,
            'class_number' => $this->filled('class_number') ? (int) $this->input('class_number') : null,
            'notification_agree' => $this->boolean('notification_agree'),
        ]);
    }

    /**
     * 검증 후 추가 검증
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $member = Auth::guard('member')->user();

            if (!$member) {
                return;
            }

            $currentPassword = (string) $this->input('current_password', '');

            if ($currentPassword === '') {
                return;
            }

            if (!password_verify($currentPassword, $member->password)) {
                $validator->errors()->add('current_password', '현재 비밀번호가 올바르지 않습니다.');
            }
        });
    }
}
