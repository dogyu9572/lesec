<?php

namespace App\Http\Requests\Backoffice\GroupApplications;

use App\Models\GroupApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupApplicationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedEducationTypes = array_keys(GroupApplication::EDUCATION_TYPE_LABELS);
        $allowedApplicationStatuses = array_keys(GroupApplication::APPLICATION_STATUS_LABELS);
        $allowedPaymentStatuses = array_keys(GroupApplication::PAYMENT_STATUS_LABELS);
        $allowedPaymentMethods = array_keys(GroupApplication::PAYMENT_METHOD_LABELS);
        $allowedReceptionStatuses = array_keys(GroupApplication::RECEPTION_STATUS_LABELS);

        return [
            'education_type' => ['required', Rule::in($allowedEducationTypes)],
            'program_reservation_id' => ['nullable', 'integer', 'exists:program_reservations,id'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['in:' . implode(',', $allowedPaymentMethods)],
            'payment_method' => ['nullable', Rule::in($allowedPaymentMethods)],
            'application_status' => ['required', Rule::in($allowedApplicationStatuses)],
            'reception_status' => ['nullable', Rule::in($allowedReceptionStatuses)],
            'applicant_name' => ['required', 'string', 'max:50'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'applicant_contact' => ['nullable', 'string', 'max:30'],
            'school_level' => ['nullable', 'string', 'max:50'],
            'school_name' => ['nullable', 'string', 'max:100'],
            'applicant_count' => ['nullable', 'integer', 'min:0'],
            'payment_status' => ['required', Rule::in($allowedPaymentStatuses)],
            'participation_fee' => ['nullable', 'integer', 'min:0'],
            'participation_date' => ['nullable', 'date'],
        ];
    }
}


