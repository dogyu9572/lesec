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
        'estimate_note',
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

        return '미선택';
    }

    /**
     * 표시용 결제방법 라벨 (선택된 것이 없으면 프로그램의 모든 결제수단 표시)
     */
    public function getDisplayPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method) {
            return static::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
        }

        $allowed = is_array($this->reservation->payment_methods ?? null) ? $this->reservation->payment_methods : [];
        if (empty($allowed)) {
            return '-';
        }

        $labels = [];
        foreach ($allowed as $methodKey) {
            if (isset(static::PAYMENT_METHOD_LABELS[$methodKey])) {
                $labels[] = static::PAYMENT_METHOD_LABELS[$methodKey];
            }
        }

        return !empty($labels) ? implode(', ', $labels) : '-';
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

    /**
     * 1인당 교육비 (프로그램 등록값 우선)
     */
    public function getFeePerPersonAttribute(): int
    {
        $fee = $this->reservation->education_fee ?? $this->participation_fee ?? null;

        return $fee !== null ? (int) $fee : 0;
    }

    /**
     * 총 결제 금액 (1인당 교육비 × 신청인원)
     */
    public function getTotalParticipationFeeAttribute(): int
    {
        $count = (int) ($this->applicant_count ?? 0);
        if ($count <= 0) {
            return 0;
        }

        return $this->fee_per_person * $count;
    }

    /**
     * 총 결제 금액 표시용 (금액이 있으면 "N원", 없으면 "-")
     */
    public function getDisplayTotalFeeLabelAttribute(): string
    {
        $total = $this->total_participation_fee;

        return $total > 0 ? number_format($total) . '원' : '-';
    }
}
