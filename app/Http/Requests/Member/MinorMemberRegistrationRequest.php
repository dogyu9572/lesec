<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class MinorMemberRegistrationRequest extends FormRequest
{
    /**
     * 권한을 확인합니다.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 검증 전 입력값을 정리합니다.
     */
    protected function prepareForValidation(): void
    {
        $emailDomain = Str::lower(trim((string) $this->input('email_domain')));

        if ($emailDomain === 'custom') {
            $emailDomain = Str::lower(trim((string) $this->input('email_domain_custom')));
        }

        $emailLocal = Str::lower(trim((string) $this->input('email_id')));

        $email = $emailLocal && $emailDomain
            ? "{$emailLocal}@{$emailDomain}"
            : null;

        $this->merge([
            'login_id' => trim((string) $this->input('login_id')),
            'name' => trim((string) $this->input('name')),
            'contact' => preg_replace('/\s+/', '', (string) $this->input('contact')),
            'city' => trim((string) $this->input('city')),
            'district' => trim((string) $this->input('district')),
            'school_name' => trim((string) $this->input('school_name')),
            'email' => $email,
        ]);
    }

    /**
     * 검증 규칙을 반환합니다.
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:members,login_id'],
            'password' => [
                'required',
                'string',
                'min:9',
                'max:12',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
                'confirmed',
            ],
            'name' => ['required', 'string', 'max:50'],
            'birth_date' => ['required', 'date_format:Ymd'],
            'gender' => ['required', 'in:male,female'],
            'contact' => ['required', 'string', 'max:20'],
            'email_id' => ['required', 'string', 'max:64'],
            'email_domain' => ['required', 'string', 'max:64'],
            'email_domain_custom' => ['nullable', 'string', 'max:64', 'required_if:email_domain,custom'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'privacy_agree' => ['accepted'],
            'notification_agree' => ['accepted'],
        ];
    }

    /**
     * 오류 메시지를 반환합니다.
     */
    public function messages(): array
    {
        return [
            'login_id.required' => '아이디를 입력해 주세요.',
            'login_id.min' => '아이디는 최소 4자 이상 입력해주세요.',
            'login_id.max' => '아이디는 최대 20자까지 입력할 수 있습니다.',
            'login_id.alpha_dash' => '아이디는 영문, 숫자, 하이픈, 언더바만 사용할 수 있습니다.',
            'login_id.unique' => '이미 사용 중인 아이디입니다.',
            'password.required' => '비밀번호를 입력해 주세요.',
            'password.min' => '비밀번호는 최소 9자 이상 입력해주세요.',
            'password.max' => '비밀번호는 최대 12자까지 입력할 수 있습니다.',
            'password.regex' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해야 합니다.',
            'password.confirmed' => '비밀번호가 일치하지 않습니다.',
            'name.required' => '이름을 입력해 주세요.',
            'name.max' => '이름은 최대 50자까지 입력할 수 있습니다.',
            'birth_date.required' => '생년월일을 입력해 주세요.',
            'birth_date.date_format' => '생년월일은 YYYYMMDD 형식으로 입력해 주세요.',
            'gender.required' => '성별을 선택해 주세요.',
            'gender.in' => '성별 선택이 올바르지 않습니다.',
            'contact.required' => '연락처를 입력해 주세요.',
            'contact.max' => '연락처는 20자 이하로 입력해 주세요.',
            'email_id.required' => '이메일 아이디를 입력해 주세요.',
            'email_id.max' => '이메일 아이디는 64자 이하로 입력해 주세요.',
            'email_domain.required' => '이메일 도메인을 선택해 주세요.',
            'email_domain.max' => '이메일 도메인은 64자 이하로 입력해 주세요.',
            'email_domain_custom.required_if' => '이메일 도메인을 직접 입력해 주세요.',
            'email_domain_custom.max' => '이메일 도메인은 64자 이하로 입력해 주세요.',
            'email.required' => '이메일을 입력해 주세요.',
            'email.email' => '올바른 이메일 형식으로 입력해 주세요.',
            'email.max' => '이메일은 255자 이하로 입력해 주세요.',
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'city.max' => '시/도 정보는 100자 이하로 입력해 주세요.',
            'district.max' => '시/군/구 정보는 100자 이하로 입력해 주세요.',
            'school_name.max' => '학교명은 255자 이하로 입력해 주세요.',
            'privacy_agree.accepted' => '개인정보 처리방침에 동의해 주세요.',
            'notification_agree.accepted' => '이메일 및 문자 수신에 동의해 주세요.',
        ];
    }
}

