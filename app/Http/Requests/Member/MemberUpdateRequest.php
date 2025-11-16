<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

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
        $member = Auth::guard('member')->user();
        $memberId = $member->id;

        $passwordRules = ['nullable', 'string'];
        
        if ($this->filled('password')) {
            $passwordRules = array_merge($passwordRules, [
                'min:8',
                'max:20',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ]);
        }

        $rules = [
            'current_password' => ['required'],
            'password' => $passwordRules,
            'contact' => ['required', 'string', 'max:50', $this->uniqueContactRule($memberId)],
            'parent_contact' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'grade' => ['nullable', 'integer', 'between:1,12'],
            'class_number' => ['nullable', 'integer', 'between:1,30'],
            'notification_agree' => ['nullable', 'boolean'],
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
            'password.password' => '비밀번호는 영문, 숫자, 특수문자를 모두 포함해 8~20자로 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'contact.required' => '연락처를 입력해주세요.',
            'contact.unique' => '이미 등록된 연락처입니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'school_name.required' => '학교명을 입력해주세요.',
            'school_id.exists' => '선택한 학교 정보가 올바르지 않습니다.',
            'grade.between' => '학년은 1~12 사이의 숫자로 선택해주세요.',
            'class_number.between' => '반은 1~30 사이의 숫자로 선택해주세요.',
        ];
    }

    /**
     * 검증 전 데이터 정리
     */
    protected function prepareForValidation(): void
    {
        $contact = preg_replace('/[^0-9]/', '', (string) $this->input('contact'));
        $parentContact = preg_replace('/[^0-9]/', '', (string) $this->input('parent_contact'));
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
            'contact' => $contact,
            'parent_contact' => $parentContact ?: null,
            'email' => $email,
            'city' => trim((string) $this->input('city')),
            'district' => trim((string) $this->input('district')),
            'school_name' => trim((string) $this->input('school_name')),
            'school_id' => $this->filled('school_id') ? (int) $this->input('school_id') : null,
            'grade' => $this->filled('grade') ? (int) $this->input('grade') : null,
            'class_number' => $this->filled('class_number') ? (int) $this->input('class_number') : null,
            'notification_agree' => $this->boolean('notification_agree'),
        ]);
    }

    /**
     * 연락처 중복 체크 규칙
     */
    private function uniqueContactRule(int $memberId): Unique
    {
        $contactDigits = preg_replace('/[^0-9]/', '', (string) $this->input('contact'));

        return Rule::unique('members', 'contact')
            ->ignore($memberId)
            ->where(function ($query) use ($contactDigits) {
                $query->where('contact', $contactDigits);

                $formatted = $this->formatContactForDisplay($contactDigits);
                if ($formatted !== null) {
                    $query->orWhere('contact', $formatted);
                }
            });
    }

    /**
     * 연락처 포맷팅
     */
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
            
            $currentPassword = $this->input('current_password');
            
            if (!empty($currentPassword) && !password_verify($currentPassword, $member->password)) {
                $validator->errors()->add('current_password', '현재 비밀번호가 올바르지 않습니다.');
            }

            // 학생 회원이고 연락처가 변경된 경우에만 중복 확인 필수
            if ($member->member_type === 'student' && $member->parent_contact) {
                $currentContact = preg_replace('/[^0-9]/', '', (string) $member->contact);
                $newContact = preg_replace('/[^0-9]/', '', (string) $this->input('contact'));
                
                // 연락처가 실제로 변경된 경우에만 중복 확인 필수
                if ($currentContact !== $newContact) {
                    $contactVerified = $this->input('contact_verified');
                    if ($contactVerified !== '1') {
                        $validator->errors()->add('contact_verified', '학생 연락처 중복 확인을 해주세요.');
                    }
                }
            }
        });
    }
}

