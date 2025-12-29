<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    /**
     * 요청에 대한 권한을 확인합니다.
     */
    public function authorize(): bool
    {
        return true; // 컨트롤러에서 권한 체크
    }

    /**
     * 유효성 검사 규칙을 정의합니다.
     */
    public function rules(): array
    {
        return [
            'login_id' => 'nullable|string|max:255|unique:users,login_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:10',
                'confirmed',
                function ($attribute, $value, $fail) {
                    $types = 0;
                    if (preg_match('/[a-z]/', $value)) $types++;
                    if (preg_match('/[A-Z]/', $value)) $types++;
                    if (preg_match('/\d/', $value)) $types++;
                    if (preg_match('/[@$!%*?&#]/', $value)) $types++;
                    
                    if ($types < 2) {
                        $fail('비밀번호는 영문 대소문자, 숫자, 특수문자 중 2종류 이상 조합하여 10자리 이상으로 입력해주세요.');
                    }
                },
            ],
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'admin_group_id' => 'nullable|exists:admin_groups,id',
        ];
    }

    /**
     * 유효성 검사 메시지를 정의합니다.
     */
    public function messages(): array
    {
        return AdminValidationMessages::getStoreMessages();
    }
}
