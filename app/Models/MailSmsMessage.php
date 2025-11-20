<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailSmsMessage extends Model
{
    use HasFactory;

    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_KAKAO = 'kakao';

    public const STATUS_PREPARED = 'prepared';
    public const STATUS_SENDING = 'sending';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'message_type',
        'title',
        'content',
        'writer_id',
        'member_group_id',
        'success_count',
        'failure_count',
        'status',
        'send_requested_at',
        'send_started_at',
        'send_completed_at',
        'last_error_message',
    ];

    protected $casts = [
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'send_requested_at' => 'datetime',
        'send_started_at' => 'datetime',
        'send_completed_at' => 'datetime',
    ];

    /**
     * 작성 관리자 관계
     */
    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    /**
     * 선택한 회원 그룹
     */
    public function memberGroup()
    {
        return $this->belongsTo(MemberGroup::class, 'member_group_id');
    }

    /**
     * 대상 회원 목록
     */
    public function recipients()
    {
        return $this->hasMany(MailSmsMessageMember::class);
    }

    /**
     * 발송 로그
     */
    public function logs()
    {
        return $this->hasMany(MailSmsLog::class);
    }

    /**
     * 발송 구분 라벨
     */
    public function getMessageTypeLabelAttribute(): string
    {
        return match ($this->message_type) {
            self::TYPE_SMS => 'SMS',
            self::TYPE_KAKAO => '카카오 알림톡',
            default => 'EMAIL',
        };
    }

    /**
     * 상태 라벨
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SENDING => '발송 중',
            self::STATUS_COMPLETED => '발송 완료',
            default => '대기',
        };
    }
}


