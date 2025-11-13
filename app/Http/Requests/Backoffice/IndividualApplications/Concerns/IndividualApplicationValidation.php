<?php

namespace App\Http\Requests\Backoffice\IndividualApplications\Concerns;

use App\Services\Backoffice\IndividualApplicationService;

trait IndividualApplicationValidation
{
    protected function sanitizeOptionalFields(): void
    {
        $this->merge([
            'member_id' => $this->filled('member_id') ? $this->input('member_id') : null,
            'program_reservation_id' => $this->filled('program_reservation_id') ? $this->input('program_reservation_id') : null,
            'participation_fee' => $this->input('participation_fee') !== '' ? $this->input('participation_fee') : null,
            'participation_date' => $this->input('participation_date') !== '' ? $this->input('participation_date') : null,
            'applicant_grade' => $this->input('applicant_grade') !== '' ? $this->input('applicant_grade') : null,
            'applicant_class' => $this->input('applicant_class') !== '' ? $this->input('applicant_class') : null,
            'draw_result' => $this->input('draw_result') !== '' ? $this->input('draw_result') : null,
        ]);
    }

    protected function optionKeys(): array
    {
        $service = app(IndividualApplicationService::class);

        return [
            'receptionTypes' => array_keys($service->getReceptionTypes()),
            'educationTypes' => array_keys($service->getEducationTypes()),
            'drawResults' => array_keys($service->getDrawResults()),
            'paymentStatuses' => array_keys($service->getPaymentStatuses()),
            'paymentMethods' => array_keys($service->getPaymentMethods()),
        ];
    }
}

