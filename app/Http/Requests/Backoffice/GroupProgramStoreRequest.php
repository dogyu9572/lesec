<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class GroupProgramStoreRequest extends FormRequest
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
            'education_type' => 'required|in:middle_semester,middle_vacation,high_semester,high_vacation,special',
            'program_name' => 'required|string|max:255',
            'education_start_date' => 'required|date',
            'education_end_date' => 'required|date|after_or_equal:education_start_date',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'in:bank_transfer,on_site_card,online_card',
            'reception_type' => 'required|in:application,remaining,closed',
            'application_start_date' => 'required|date',
            'application_end_date' => 'required|date|after_or_equal:application_start_date',
            'capacity' => 'nullable|integer|min:1|required_without:is_unlimited_capacity',
            'is_unlimited_capacity' => 'nullable|boolean',
            'education_fee' => 'nullable|numeric|min:0|required_without:is_free',
            'is_free' => 'nullable|boolean',
            'author' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'education_type.required' => '교육유형을 선택해주세요.',
            'education_type.in' => '올바른 교육유형을 선택해주세요.',
            'program_name.required' => '프로그램명을 입력해주세요.',
            'program_name.max' => '프로그램명은 255자를 초과할 수 없습니다.',
            'education_start_date.required' => '교육 시작일을 선택해주세요.',
            'education_start_date.date' => '올바른 날짜 형식을 입력해주세요.',
            'education_end_date.required' => '교육 종료일을 선택해주세요.',
            'education_end_date.date' => '올바른 날짜 형식을 입력해주세요.',
            'education_end_date.after_or_equal' => '교육 종료일은 시작일과 같거나 이후여야 합니다.',
            'payment_methods.required' => '결제수단을 최소 1개 이상 선택해주세요.',
            'payment_methods.array' => '결제수단을 올바르게 선택해주세요.',
            'payment_methods.min' => '결제수단을 최소 1개 이상 선택해주세요.',
            'reception_type.required' => '접수유형을 선택해주세요.',
            'reception_type.in' => '올바른 접수유형을 선택해주세요.',
            'application_start_date.required' => '신청 시작일을 선택해주세요.',
            'application_start_date.date' => '올바른 날짜 형식을 입력해주세요.',
            'application_end_date.required' => '신청 종료일을 선택해주세요.',
            'application_end_date.date' => '올바른 날짜 형식을 입력해주세요.',
            'application_end_date.after_or_equal' => '신청 종료일은 시작일과 같거나 이후여야 합니다.',
            'capacity.integer' => '정원은 숫자로 입력해주세요.',
            'capacity.min' => '정원은 1명 이상이어야 합니다.',
            'capacity.required_without' => '제한없음이 선택되지 않은 경우 정원을 입력해주세요.',
            'education_fee.numeric' => '교육비는 숫자로 입력해주세요.',
            'education_fee.min' => '교육비는 0원 이상이어야 합니다.',
            'education_fee.required_without' => '무료가 선택되지 않은 경우 교육비를 입력해주세요.',
            'author.max' => '작성자는 100자를 초과할 수 없습니다.',
        ];
    }
}
