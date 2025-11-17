<?php

namespace App\Http\Requests\Backoffice\Schedules;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleUpdateRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'content' => ['nullable', 'string'],
            'disable_application' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => '일정 제목을 입력해주세요.',
            'start_date.required' => '시작 날짜를 선택해주세요.',
            'start_date.date' => '올바른 날짜 형식이 아닙니다.',
            'end_date.required' => '종료 날짜를 선택해주세요.',
            'end_date.date' => '올바른 날짜 형식이 아닙니다.',
            'end_date.after_or_equal' => '종료 날짜는 시작 날짜와 같거나 이후여야 합니다.',
        ];
    }
}
