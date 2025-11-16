<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualApplication extends Model
{
    protected $table = 'individual_applications';

    public const RECEPTION_TYPE_LABELS = [
        'first_come' => '선착순',
        'lottery' => '추첨',
        'naver_form' => '네이버폼',
    ];

    public const EDUCATION_TYPE_LABELS = [
        'middle_semester' => '중등학기',
        'middle_vacation' => '중등방학',
        'high_semester' => '고등학기',
        'high_vacation' => '고등방학',
        'special' => '특별프로그램',
    ];

    public const DRAW_RESULT_LABELS = [
        'pending' => '대기중',
        'win' => '당첨',
        'fail' => '미당첨',
        'waitlist' => '대기중',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => '미입금',
        'paid' => '입금완료',
        'refunded' => '환불',
        'cancelled' => '신청 취소',
    ];

    protected $fillable = [
        'program_reservation_id',
        'member_id',
        'application_number',
        'education_type',
        'reception_type',
        'program_name',
        'participation_date',
        'participation_fee',
        'payment_method',
        'payment_status',
        'draw_result',
        'applicant_name',
        'applicant_school_name',
        'applicant_grade',
        'applicant_class',
        'applicant_contact',
        'guardian_contact',
        'applied_at',
    ];

    protected $casts = [
        'participation_date' => 'date',
        'participation_fee' => 'integer',
        'applicant_grade' => 'integer',
        'applicant_class' => 'integer',
        'applied_at' => 'datetime',
    ];

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

    public const DRAW_RESULT_PENDING = 'pending';
    public const DRAW_RESULT_WIN = 'win';
    public const DRAW_RESULT_WAITLIST = 'waitlist';
    public const DRAW_RESULT_FAIL = 'fail';

    public function getReceptionTypeLabelAttribute(): string
    {
        return static::RECEPTION_TYPE_LABELS[$this->reception_type] ?? ($this->reception_type ?? '-');
    }

    public function getEducationTypeLabelAttribute(): string
    {
        return static::EDUCATION_TYPE_LABELS[$this->education_type] ?? ($this->education_type ?? '-');
    }

    public function getDrawResultLabelAttribute(): string
    {
        $value = $this->draw_result;

        if ($value === null || $value === '') {
            return '-';
        }

        return static::DRAW_RESULT_LABELS[$value] ?? $value;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return static::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ($this->payment_status ?? '-');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(ProgramReservation::class, 'program_reservation_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

