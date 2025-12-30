<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class MemberOverFourteenRegisterRequest extends FormRequest
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
        $memberType = $this->session()->get('member_registration.member_type');

        return [
            'login_id' => ['required', 'string', 'min:4', 'max:50', 'unique:members,login_id'],
            'login_id_verified' => ['required', 'in:1'],
            'password' => [
                'required',
                'string',
                'min:9',
                'max:12',
                'confirmed',
                'regex:/^(?!.*[\x{AC00}-\x{D7AF}]).+$/u',
            ],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date_format:Ymd', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'contact' => ['required', 'string', 'max:50', $this->uniqueContactRule()],
            'contact_verified' => ['required', 'in:1'],
            'parent_contact' => $memberType === 'student'
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'grade' => $memberType === 'student'
                ? ['required', 'integer', 'between:1,3']
                : ['nullable', 'integer', 'between:1,6'],
            'class_number' => $memberType === 'student'
                ? ['required', 'integer', 'between:1,20']
                : ['nullable', 'integer', 'between:1,20'],
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
            'login_id_verified.required' => '아이디 중복 확인을 해주세요.',
            'login_id_verified.in' => '아이디 중복 확인을 해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 9자 이상 입력해주세요.',
            'password.max' => '비밀번호는 12자 이하로 입력해주세요.',
            'password.regex' => '한글을 제외한 영문/숫자/특수문자로 9~12자리로 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'name.required' => '이름을 입력해주세요.',
            'birth_date.required' => '생년월일을 입력해주세요.',
            'birth_date.date_format' => '생년월일은 YYYYMMDD 형식으로 입력해주세요.',
            'birth_date.before' => '미래 날짜는 입력할 수 없습니다.',
            'gender.required' => '성별을 선택해주세요.',
            'contact.required' => '연락처를 입력해주세요.',
            'contact.unique' => '이미 등록된 연락처입니다.',
            'contact_verified.required' => '학생 연락처 중복 확인을 해주세요.',
            'contact_verified.in' => '학생 연락처 중복 확인을 해주세요.',
            'parent_contact.required' => '보호자 연락처를 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'school_name.required' => '학교명을 입력해주세요.',
            'school_id.exists' => '선택한 학교 정보가 올바르지 않습니다.',
            'grade.required' => '학년을 선택해주세요.',
            'grade.between' => '학년은 1~3학년 중에서 선택해주세요.',
            'class_number.required' => '반을 선택해주세요.',
            'class_number.between' => '반은 1~20 사이의 숫자로 선택해주세요.',
            'privacy_agree.accepted' => '개인정보 처리방침에 동의해야 회원가입이 가능합니다.',
            'notification_agree.accepted' => '수신 동의 항목에 동의해야 회원가입이 가능합니다.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $studentContactOriginal = (string) $this->input('student_contact');
        $studentContact = preg_replace('/[^0-9]/', '', $studentContactOriginal);
        $parentContactOriginal = (string) $this->input('parent_contact');
        $parentContact = preg_replace('/[^0-9]/', '', $parentContactOriginal);
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

        // 원본 필드 값도 유지하여 validation 실패 시 입력값이 보존되도록 함
        $this->merge([
            'login_id' => trim((string) $this->input('login_id')),
            'password' => $this->input('password'), // 비밀번호 값 유지
            'password_confirmation' => $this->input('password_confirmation'), // 비밀번호 확인 값 유지
            'name' => trim((string) $this->input('name')),
            'contact' => $studentContact,
            'student_contact' => $studentContactOriginal, // 원본 값 유지 (하이픈 포함)
            'parent_contact' => $parentContactOriginal, // 원본 값 유지 (하이픈 포함)
            'email' => $email,
            'email_id' => $emailId, // 원본 값 유지
            'email_domain' => $emailDomainSelected, // 원본 값 유지
            'email_domain_custom' => $emailDomainCustom, // 원본 값 유지
            'city' => trim((string) $this->input('city')),
            'district' => trim((string) $this->input('district')),
            'school_name' => trim((string) $this->input('school_name')),
            'school_id' => $this->filled('school_id') ? (int) $this->input('school_id') : null,
            'grade' => $this->filled('grade') ? (int) $this->input('grade') : null,
            'class_number' => $this->filled('class_number') ? (int) $this->input('class_number') : null,
            'privacy_agree' => $this->boolean('privacy_agree'),
            'notification_agree' => $this->boolean('notification_agree'),
            'login_id_verified' => $this->input('login_id_verified', '0'), // 원본 값 유지
            'contact_verified' => $this->input('contact_verified', '0'), // 원본 값 유지
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


