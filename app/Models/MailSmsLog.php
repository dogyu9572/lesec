<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailSmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_sms_message_id',
        'send_sequence',
        'mail_sms_message_member_id',
        'member_id',
        'member_name',
        'member_email',
        'member_contact',
        'result_status',
        'sent_at',
        'response_code',
        'response_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * 메시지 정보
     */
    public function message()
    {
        return $this->belongsTo(MailSmsMessage::class, 'mail_sms_message_id');
    }

    /**
     * 대상 회원 정보
     */
    public function messageMember()
    {
        return $this->belongsTo(MailSmsMessageMember::class, 'mail_sms_message_member_id');
    }

    /**
     * 회원 원본 정보
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * 발송 결과 라벨
     */
    public function getResultStatusLabelAttribute(): string
    {
        return $this->result_status === 'success' ? '성공' : '실패';
    }
}


