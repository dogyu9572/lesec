<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class SchoolSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:50'],
            'district' => ['nullable', 'string', 'max:50'],
            'school_level' => ['nullable', 'in:elementary,middle,high'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}


