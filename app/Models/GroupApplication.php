<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupApplication extends Model
{
    protected $fillable = [
        'program_reservation_id',
        'application_number',
        'education_type',
        'payment_methods',
        'payment_method',
        'application_status',
        'reception_status',
        'applicant_name',
        'member_id',
        'applicant_contact',
        'school_level',
        'school_name',
        'applicant_count',
        'payment_status',
        'participation_fee',
        'participation_date',
        'applied_at',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'participation_fee' => 'integer',
        'participation_date' => 'date',
        'applied_at' => 'datetime',
    ];

    public const EDUCATION_TYPE_LABELS = [
        'middle_semester' => '중등학기',
        'middle_vacation' => '중등방학',
        'high_semester' => '고등학기',
        'high_vacation' => '고등방학',
        'special' => '특별프로그램',
    ];

    public const APPLICATION_STATUS_LABELS = [
        'pending' => '승인대기',
        'approved' => '승인완료',
        'cancelled' => '취소/반려',
    ];

    public const RECEPTION_STATUS_LABELS = [
        'application' => '신청',
        'in_progress' => '진행중',
        'completed' => '완료',
        'cancelled' => '취소',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => '미입금',
        'paid' => '입금완료',
        'cancelled' => '신청 취소',
    ];

    public const PAYMENT_METHOD_LABELS = [
        'bank_transfer' => '무통장 입금',
        'on_site_card' => '방문 카드결제',
        'online_card' => '온라인 카드결제',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(ProgramReservation::class, 'program_reservation_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GroupApplicationParticipant::class);
    }

    public function getEducationTypeLabelAttribute(): string
    {
        return static::EDUCATION_TYPE_LABELS[$this->education_type] ?? $this->education_type ?? '-';
    }

    public function getApplicationStatusLabelAttribute(): string
    {
        return static::APPLICATION_STATUS_LABELS[$this->application_status] ?? $this->application_status ?? '-';
    }

    public function getReceptionStatusLabelAttribute(): string
    {
        return static::RECEPTION_STATUS_LABELS[$this->reception_status] ?? $this->reception_status ?? '-';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return static::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status ?? '-';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method) {
            return static::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
        }

        if (is_array($this->payment_methods) && count($this->payment_methods) > 0) {
            $labels = [];
            foreach ($this->payment_methods as $method) {
                $labels[] = static::PAYMENT_METHOD_LABELS[$method] ?? $method;
            }

            return implode(', ', $labels);
        }

        return '-';
    }

    public function getParticipationDateFormattedAttribute(): ?string
    {
        if (!$this->participation_date) {
            return null;
        }
        
        $dayNames = ['일', '월', '화', '수', '목', '금', '토'];
        $dayName = $dayNames[$this->participation_date->dayOfWeek];
        
        return $this->participation_date->format('Y.m.d') . '(' . $dayName . ')';
    }

    public function getAppliedAtFormattedAttribute(): ?string
    {
        return $this->applied_at?->format('Y.m.d H:i');
    }

    public function getProgramNameLabelAttribute(): ?string
    {
        return $this->reservation?->program_name ?? '-';
    }
}



