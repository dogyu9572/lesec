<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminMenuRequest extends FormRequest
{
    /**
     * 요청 권한 확인
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:admin_menus,id',
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 유효성 검사 메시지
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => '존재하지 않는 상위 메뉴입니다.',
            'name.required' => '메뉴 이름은 필수입니다.',
            'name.max' => '메뉴 이름은 최대 255자까지 입력 가능합니다.',
            'url.max' => 'URL은 최대 255자까지 입력 가능합니다.',
            'icon.max' => '아이콘은 최대 100자까지 입력 가능합니다.',
            'order.required' => '순서는 필수입니다.',
            'order.integer' => '순서는 숫자로 입력해주세요.',
            'order.min' => '순서는 0 이상이어야 합니다.',
            'is_active.boolean' => '활성화 상태는 참/거짓 값이어야 합니다.',
        ];
    }

    /**
     * 검증 통과 후 데이터 처리
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // is_active가 체크박스로 전송되므로, 존재 여부로 true/false 설정
        $validated['is_active'] = isset($validated['is_active']) && $validated['is_active'];
        
        return $validated;
    }
}
