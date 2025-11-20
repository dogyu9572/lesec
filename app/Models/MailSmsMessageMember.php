<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailSmsMessageMember extends Model
{
    use HasFactory;

    protected $table = 'mail_sms_message_member';

    protected $fillable = [
        'mail_sms_message_id',
        'member_id',
        'member_name',
        'member_email',
        'member_contact',
        'is_selected',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    /**
     * 메시지 정보
     */
    public function message()
    {
        return $this->belongsTo(MailSmsMessage::class, 'mail_sms_message_id');
    }

    /**
     * 회원 정보
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * 발송 로그
     */
    public function logs()
    {
        return $this->hasMany(MailSmsLog::class, 'mail_sms_message_member_id');
    }
}


