<?php

namespace App\Http\Requests\Backoffice\IndividualApplications;

use App\Http\Requests\Backoffice\IndividualApplications\Concerns\IndividualApplicationValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIndividualApplicationRequest extends FormRequest
{
    use IndividualApplicationValidation;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeOptionalFields();
    }

    public function rules(): array
    {
        $options = $this->optionKeys();

        return [
            'reception_type' => ['required', Rule::in($options['receptionTypes'])],
            'education_type' => ['required', Rule::in($options['educationTypes'])],
            'draw_result' => ['nullable', Rule::in($options['drawResults'])],
            'payment_status' => ['required', Rule::in($options['paymentStatuses'])],
            'payment_method' => ['nullable', Rule::in($options['paymentMethods'])],
            'program_reservation_id' => ['nullable', 'integer', 'exists:program_reservations,id'],
            'program_name' => ['required', 'string', 'max:255'],
            'participation_date' => ['nullable', 'date'],
            'participation_fee' => ['nullable', 'integer', 'min:0'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'applicant_name' => ['required', 'string', 'max:50'],
            'applicant_school_name' => ['nullable', 'string', 'max:100'],
            'applicant_grade' => ['nullable', 'integer', 'between:1,20'],
            'applicant_class' => ['nullable', 'integer', 'between:1,50'],
            'applicant_contact' => ['required', 'string', 'max:20'],
            'guardian_contact' => ['nullable', 'string', 'max:20'],
        ];
    }
}

