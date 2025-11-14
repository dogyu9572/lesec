<?php

namespace App\Http\Requests\Backoffice\GroupApplications;

use App\Http\Requests\Backoffice\GroupApplications\Concerns\GroupApplicationValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupApplicationRequest extends FormRequest
{
    use GroupApplicationValidation;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeInputs();
    }

    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'program_reservation_id' => ['required', 'integer', 'exists:program_reservations,id'],
        ]);
    }
}


