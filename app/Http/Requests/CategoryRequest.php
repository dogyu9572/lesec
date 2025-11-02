<?php

namespace App\Http\Requests;

use App\Models\Category;
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
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'code' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        // 부모 카테고리 선택 시 depth 제한 체크 (최대 depth=2까지, 그룹은 depth=0)
        if ($this->parent_id) {
            $rules['parent_id'][] = function ($attribute, $value, $fail) {
                $parent = Category::find($value);
                if ($parent && $parent->depth >= 2) {
                    $fail('카테고리는 최대 2차(3단계)까지만 생성할 수 있습니다.');
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
            'name.required' => '카테고리명을 입력해주세요.',
            'name.max' => '카테고리명은 100자 이내로 입력해주세요.',
            'parent_id.exists' => '존재하지 않는 상위 카테고리입니다.',
            'code.max' => '코드는 50자 이내로 입력해주세요.',
            'display_order.integer' => '정렬 순서는 숫자로 입력해주세요.',
            'display_order.min' => '정렬 순서는 0 이상이어야 합니다.',
        ];
    }
}
