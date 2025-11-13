<?php

namespace App\Http\Requests\Program;

use Illuminate\Foundation\Http\FormRequest;

class IndividualProgramApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_reservation_id' => ['required', 'integer', 'exists:program_reservations,id'],
            'participation_date' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'program_reservation_id' => '프로그램 정보',
            'participation_date' => '참가일',
        ];
    }
}


