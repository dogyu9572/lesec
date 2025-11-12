<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class MemberUnderFourteenRegisterRequest extends FormRequest
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
            'login_id' => ['required', 'string', 'min:4', 'max:50', 'unique:members,login_id'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date_format:Ymd', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'contact' => ['required', 'string', 'max:50', $this->uniqueContactRule()],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:members,email'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'privacy_agree' => ['accepted'],
            'notification_agree' => ['accepted'],
        ];
    }

    /**
     * 사용자 정의 메시지
     */
    public function messages(): array
    {
        return [
            'login_id.required' => '아이디를 입력해주세요.',
            'login_id.min' => '아이디는 최소 4자 이상 입력해주세요.',
            'login_id.unique' => '이미 사용 중인 아이디입니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 8자 이상 입력해주세요.',
            'password.password' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'name.required' => '이름을 입력해주세요.',
            'birth_date.required' => '생년월일을 입력해주세요.',
            'birth_date.date_format' => '생년월일은 YYYYMMDD 형식으로 입력해주세요.',
            'birth_date.before' => '미래 날짜는 입력할 수 없습니다.',
            'gender.required' => '성별을 선택해주세요.',
            'contact.required' => '연락처를 입력해주세요.',
            'contact.unique' => '이미 등록된 연락처입니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.unique' => '이미 등록된 이메일입니다.',
            'school_name.required' => '학교명을 입력해주세요.',
            'school_id.exists' => '선택한 학교 정보가 올바르지 않습니다.',
            'privacy_agree.accepted' => '개인정보 처리방침에 동의해야 회원가입이 가능합니다.',
            'notification_agree.accepted' => '수신 동의 항목에 동의해야 회원가입이 가능합니다.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $contact = preg_replace('/[^0-9]/', '', (string) $this->input('contact'));
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
            'login_id' => trim((string) $this->input('login_id')),
            'name' => trim((string) $this->input('name')),
            'contact' => $contact,
            'email' => $email,
            'city' => trim((string) $this->input('city')),
            'district' => trim((string) $this->input('district')),
            'school_name' => trim((string) $this->input('school_name')),
            'school_id' => $this->filled('school_id') ? (int) $this->input('school_id') : null,
            'privacy_agree' => $this->boolean('privacy_agree'),
            'notification_agree' => $this->boolean('notification_agree'),
        ]);
    }

    private function uniqueContactRule(): Unique
    {
        $contactDigits = preg_replace('/[^0-9]/', '', (string) $this->input('contact'));

        return Rule::unique('members', 'contact')->where(function ($query) use ($contactDigits) {
            $query->where('contact', $contactDigits);

            $formatted = $this->formatContactForDisplay($contactDigits);
            if ($formatted !== null) {
                $query->orWhere('contact', $formatted);
            }
        });
    }

    private function formatContactForDisplay(?string $digits): ?string
    {
        if (empty($digits)) {
            return null;
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $digits);
        }

        return $digits;
    }
}


