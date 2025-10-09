<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * 권한 확인
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
        $rules = [
            'category_group' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        // 부모 카테고리 선택 시 depth 제한 체크
        if ($this->parent_id) {
            $rules['parent_id'][] = function ($attribute, $value, $fail) {
                $parent = \App\Models\Category::find($value);
                if ($parent && $parent->depth >= 3) {
                    $fail('카테고리는 최대 3단계까지만 생성할 수 있습니다.');
                }
            };
        }

        return $rules;
    }

    /**
     * 에러 메시지
     */
    public function messages(): array
    {
        return [
            'category_group.required' => '카테고리 그룹을 선택해주세요.',
            'category_group.max' => '카테고리 그룹은 50자 이내로 입력해주세요.',
            'name.required' => '카테고리명을 입력해주세요.',
            'name.max' => '카테고리명은 100자 이내로 입력해주세요.',
            'parent_id.exists' => '존재하지 않는 상위 카테고리입니다.',
            'display_order.integer' => '정렬 순서는 숫자로 입력해주세요.',
            'display_order.min' => '정렬 순서는 0 이상이어야 합니다.',
        ];
    }
}
