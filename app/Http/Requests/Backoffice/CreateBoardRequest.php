<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Board;

class CreateBoardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 권한은 미들웨어에서 처리
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:100',
            'slug' => 'nullable|alpha_dash|max:50',
            'description' => 'nullable|max:500',
            'template_id' => 'required|exists:board_templates,id',
            'is_active' => 'boolean',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => '게시판 이름은 필수입니다.',
            'name.max' => '게시판 이름은 100자를 초과할 수 없습니다.',
            'slug.alpha_dash' => '게시판 슬러그는 영문, 숫자, 하이픈, 언더스코어만 사용 가능합니다.',
            'slug.max' => '게시판 슬러그는 50자를 초과할 수 없습니다.',
            'description.max' => '게시판 설명은 500자를 초과할 수 없습니다.',
            'template_id.required' => '템플릿을 선택해주세요.',
            'template_id.exists' => '선택한 템플릿이 존재하지 않습니다.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // slug 중복 체크 (삭제된 데이터 제외)
            if (!empty($this->slug)) {
                if (!Board::isSlugAvailable($this->slug)) {
                    $validator->errors()->add('slug', '이미 사용 중인 게시판 식별자입니다.');
                }
            }
        });
    }
}
