<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class ProgramUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'host' => 'nullable|string|max:255',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'location' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'detail_content' => 'nullable|string',
            'other_info' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'host.max' => '주최는 255자 이내로 입력해주세요.',
            'period_start.date' => '기간 시작일은 올바른 날짜 형식으로 입력해주세요.',
            'period_end.date' => '기간 종료일은 올바른 날짜 형식으로 입력해주세요.',
            'period_end.after_or_equal' => '기간 종료일은 시작일 이후 날짜여야 합니다.',
            'location.max' => '장소는 255자 이내로 입력해주세요.',
            'target.max' => '대상은 255자 이내로 입력해주세요.',
            'is_active.boolean' => '활성화 여부는 올바른 값으로 선택해주세요.',
        ];
    }
}
