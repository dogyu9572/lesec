<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramReservation extends Model
{
    protected $fillable = [
        'education_type',
        'application_type',
        'program_name',
        'education_start_date',
        'education_end_date',
        'payment_methods',
        'reception_type',
        'application_start_date',
        'application_end_date',
        'capacity',
        'is_unlimited_capacity',
        'education_fee',
        'is_free',
        'naver_form_url',
        'waitlist_url',
        'author',
        'is_active',
    ];

    protected $casts = [
        'education_start_date' => 'date',
        'education_end_date' => 'date',
        'application_start_date' => 'date',
        'application_end_date' => 'date',
        'payment_methods' => 'array',
        'is_unlimited_capacity' => 'boolean',
        'education_fee' => 'decimal:2',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * 교육유형별로 조회
     */
    public function scopeByEducationType($query, string $educationType)
    {
        return $query->where('education_type', $educationType);
    }

    /**
     * 신청유형별로 조회
     */
    public function scopeByApplicationType($query, string $applicationType)
    {
        return $query->where('application_type', $applicationType);
    }

    /**
     * 활성화된 프로그램만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 교육유형 한글명 반환
     */
    public function getEducationTypeNameAttribute(): string
    {
        $types = [
            'middle_semester' => '중등학기',
            'middle_vacation' => '중등방학',
            'high_semester' => '고등학기',
            'high_vacation' => '고등방학',
            'special' => '특별프로그램',
        ];

        return $types[$this->education_type] ?? $this->education_type;
    }

    /**
     * 신청유형 한글명 반환
     */
    public function getApplicationTypeNameAttribute(): string
    {
        $types = [
            'group' => '단체',
            'individual' => '개인',
        ];

        return $types[$this->application_type] ?? $this->application_type;
    }

    /**
     * 접수유형 한글명 반환
     */
    public function getReceptionTypeNameAttribute(): ?string
    {
        if (!$this->reception_type) {
            return null;
        }

        $types = [
            'application' => '신청',
            'remaining' => '잔여석 신청',
            'closed' => '마감',
            'first_come' => '선착순',
            'lottery' => '추첨',
            'naver_form' => '네이버폼',
        ];

        return $types[$this->reception_type] ?? $this->reception_type;
    }

    /**
     * 결제수단 한글명 배열 반환
     */
    public function getPaymentMethodNamesAttribute(): array
    {
        if (!$this->payment_methods) {
            return [];
        }

        $methodMap = [
            'bank_transfer' => '무통장 입금',
            'on_site_card' => '방문 카드결제',
            'online_card' => '온라인 카드결제',
        ];

        $names = [];
        foreach ($this->payment_methods as $method) {
            if (isset($methodMap[$method])) {
                $names[] = $methodMap[$method];
            }
        }

        return $names;
    }
}
