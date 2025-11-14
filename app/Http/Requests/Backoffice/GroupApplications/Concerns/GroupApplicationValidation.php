<?php

namespace App\Http\Requests\Backoffice\GroupApplications\Concerns;

use App\Models\GroupApplication;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

trait GroupApplicationValidation
{
    protected function sanitizeInputs(): void
    {
        $this->merge([
            'member_id' => $this->filled('member_id') ? (int) $this->input('member_id') : null,
            'program_reservation_id' => $this->filled('program_reservation_id') ? (int) $this->input('program_reservation_id') : null,
            'participation_fee' => $this->input('participation_fee') !== '' ? (int) $this->input('participation_fee') : null,
            'participation_date' => $this->input('participation_date') !== '' ? $this->input('participation_date') : null,
            'applicant_count' => $this->filled('applicant_count') ? (int) $this->input('applicant_count') : null,
            'payment_methods' => array_values(array_filter(Arr::wrap($this->input('payment_methods', [])))),
            'application_status' => $this->input('application_status', 'pending'),
            'payment_status' => $this->input('payment_status', 'unpaid'),
            'reception_status' => $this->input('reception_status', 'application'),
        ]);
    }

    protected function optionKeys(): array
    {
        return [
            'educationTypes' => array_keys(GroupApplication::EDUCATION_TYPE_LABELS),
            'applicationStatuses' => array_keys(GroupApplication::APPLICATION_STATUS_LABELS),
            'paymentStatuses' => array_keys(GroupApplication::PAYMENT_STATUS_LABELS),
            'paymentMethods' => array_keys(GroupApplication::PAYMENT_METHOD_LABELS),
        ];
    }

    protected function commonRules(): array
    {
        $options = $this->optionKeys();

        return [
            'education_type' => ['required', Rule::in($options['educationTypes'])],
            'payment_status' => ['required', Rule::in($options['paymentStatuses'])],
            'application_status' => ['required', Rule::in($options['applicationStatuses'])],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => [Rule::in($options['paymentMethods'])],
            'payment_method' => ['nullable', Rule::in($options['paymentMethods'])],
            'applicant_name' => ['required', 'string', 'max:50'],
            'applicant_contact' => ['required', 'string', 'max:20'],
            'reception_status' => ['nullable', Rule::in(array_keys(GroupApplication::RECEPTION_STATUS_LABELS))],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'school_level' => ['nullable', 'string', 'max:50'],
            'school_name' => ['nullable', 'string', 'max:100'],
            'applicant_count' => ['required', 'integer', 'min:1'],
            'participation_fee' => ['nullable', 'integer', 'min:0'],
            'participation_date' => ['nullable', 'date'],
        ];
    }
}


