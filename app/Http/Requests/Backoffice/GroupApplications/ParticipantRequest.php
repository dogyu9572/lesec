<?php

namespace App\Http\Requests\Backoffice\GroupApplications;

use Illuminate\Foundation\Http\FormRequest;

class ParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 업로드 요청: 파일 존재 시
        if ($this->hasFile('csv_file')) {
            return [
                'csv_file' => 'required|file|mimes:csv,txt|max:4096',
            ];
        }

        // 단건 등록/수정 요청
        return [
            'name' => 'required|string|max:50',
            'grade' => 'required|integer|min:1|max:3',
            'class' => 'required|string|max:20',
            'birthday' => 'nullable|string|max:10',
        ];
    }
}


