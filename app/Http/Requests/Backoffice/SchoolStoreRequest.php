<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class SchoolStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'source_type' => 'required|in:api,user_registration,admin_registration',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'school_level' => 'nullable|in:elementary,middle,high',
            'school_name' => 'required|string|max:255',
            'school_code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'homepage' => 'nullable|url|max:500',
            'is_coed' => 'nullable|boolean',
            'day_night_division' => 'nullable|string|max:50',
            'founding_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'source_type.required' => '구분을 선택해주세요.',
            'source_type.in' => '올바른 구분을 선택해주세요.',
            'city.max' => '시/도는 100자를 초과할 수 없습니다.',
            'district.max' => '시/군/구는 100자를 초과할 수 없습니다.',
            'school_level.in' => '올바른 학교급을 선택해주세요.',
            'school_name.required' => '학교명을 입력해주세요.',
            'school_name.max' => '학교명은 255자를 초과할 수 없습니다.',
            'school_code.max' => '학교 코드는 50자를 초과할 수 없습니다.',
            'address.max' => '주소는 500자를 초과할 수 없습니다.',
            'phone.max' => '전화번호는 50자를 초과할 수 없습니다.',
            'homepage.url' => '올바른 URL 형식을 입력해주세요.',
            'homepage.max' => '홈페이지 주소는 500자를 초과할 수 없습니다.',
            'is_coed.boolean' => '남녀공학 여부는 올바른 형식으로 입력해주세요.',
            'day_night_division.max' => '주야구분은 50자를 초과할 수 없습니다.',
            'founding_date.date' => '올바른 날짜 형식을 입력해주세요.',
        ];
    }
}
