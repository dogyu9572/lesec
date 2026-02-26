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
        $rules = [
            'message_type' => ['required', Rule::in(['email', 'sms', 'kakao'])],
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'member_group_id' => ['nullable', 'integer', 'exists:member_groups,id'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'distinct', 'exists:members,id'],
        ];

        // 메시지 타입별 content 길이 제한
        $messageType = $this->input('message_type');
        if ($messageType === 'sms') {
            $rules['content'][] = 'max:200'; // SMS 기준
        } elseif ($messageType === 'kakao') {
            $rules['content'][] = 'max:500'; // 카카오 알림톡 기준
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'content.max' => $this->input('message_type') === 'sms'
                ? 'SMS 내용은 최대 200자까지 입력할 수 있습니다.'
                : '카카오 알림톡 내용은 최대 500자까지 입력할 수 있습니다.',
        ];
    }
}
