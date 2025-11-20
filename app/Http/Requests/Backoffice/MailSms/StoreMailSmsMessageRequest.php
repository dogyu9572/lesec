<?php

namespace App\Http\Requests\Backoffice\MailSms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMailSmsMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message_type' => ['required', Rule::in(['email', 'sms', 'kakao'])],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'member_group_id' => ['nullable', 'integer', 'exists:member_groups,id'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'distinct', 'exists:members,id'],
        ];
    }
}


