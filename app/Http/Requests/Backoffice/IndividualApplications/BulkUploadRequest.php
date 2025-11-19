<?php

namespace App\Http\Requests\Backoffice\IndividualApplications;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
            'program_reservation_id' => [
                'required',
                'integer',
                'exists:program_reservations,id,reception_type,naver_form'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => '파일을 선택해주세요.',
            'file.file' => '올바른 파일을 업로드해주세요.',
            'file.mimes' => 'CSV 또는 엑셀 파일만 업로드 가능합니다.',
            'file.max' => '파일 크기는 5MB 이하여야 합니다.',
            'program_reservation_id.required' => '프로그램을 선택해주세요.',
            'program_reservation_id.integer' => '올바른 프로그램을 선택해주세요.',
            'program_reservation_id.exists' => '네이버 폼 프로그램을 선택해주세요.',
        ];
    }
}

