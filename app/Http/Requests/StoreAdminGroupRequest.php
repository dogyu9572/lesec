<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminGroupRequest extends FormRequest
{
    /**
     * 요청에 대한 권한을 확인합니다.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙을 정의합니다.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:admin_groups,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 유효성 검사 메시지를 정의합니다.
     */
    public function messages(): array
    {
        return [
            'name.required' => '그룹명은 필수 입력 항목입니다.',
            'name.unique' => '이미 존재하는 그룹명입니다.',
            'name.max' => '그룹명은 최대 255자까지 입력할 수 있습니다.',
        ];
    }
}

