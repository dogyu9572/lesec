<?php

namespace App\Models;

use Carbon\Carbon;
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
        'applied_count',
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
        'applied_count' => 'integer',
        'is_unlimited_capacity' => 'boolean',
        'education_fee' => 'integer',
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

    /**
     * 현재 접수유형 자동 판정 (단체 프로그램용)
     */
    public function getCurrentReceptionTypeAttribute(): string
    {
        // 정원 무제한이면 항상 신청 가능
        if ($this->is_unlimited_capacity) {
            return 'application';
        }

        $capacity = $this->capacity ?? 0;
        $appliedCount = $this->applied_count ?? 0;

        if ($appliedCount >= $capacity) {
            return 'closed';
        } elseif ($appliedCount > 0) {
            return 'remaining';
        } else {
            return 'application';
        }
    }

    /**
     * 현재 접수유형 한글명 반환
     */
    public function getCurrentReceptionTypeNameAttribute(): string
    {
        $type = $this->current_reception_type;

        $types = [
            'application' => '신청',
            'remaining' => '잔여석 신청',
            'closed' => '마감',
        ];

        return $types[$type] ?? '신청';
    }

    public function getIndividualActionTypeAttribute(): string
    {
        $now = Carbon::now();

        if ($this->application_start_date && $this->application_start_date->isAfter($now)) {
            return 'scheduled';
        }

        if ($this->current_reception_type === 'closed' && !empty($this->waitlist_url)) {
            return 'waitlist';
        }

        if ($this->current_reception_type === 'closed') {
            return 'closed';
        }

        return 'apply';
    }

    /**
     * 잔여 정원 반환
     */
    public function getRemainingCapacityAttribute(): int
    {
        if ($this->is_unlimited_capacity) {
            return 9999;
        }

        $capacity = $this->capacity ?? 0;
        $appliedCount = $this->applied_count ?? 0;

        return max(0, $capacity - $appliedCount);
    }
}
