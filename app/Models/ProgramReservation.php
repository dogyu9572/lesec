<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'application_start_date' => 'datetime',
        'application_end_date' => 'datetime',
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
     * 현재 접수유형 자동 판정 (단체/개인 프로그램용)
     */
    public function getCurrentReceptionTypeAttribute(): string
    {
        // 개인 프로그램인 경우 reception_type에 따라 처리
        if ($this->application_type === 'individual') {
            // 추첨은 정원 여부와 상관없이 항상 신청 가능
            if ($this->reception_type === 'lottery') {
                return 'application';
            }

            // 네이버폼도 항상 신청 가능
            if ($this->reception_type === 'naver_form') {
                return 'application';
            }

            // 선착순만 정원 체크
            if ($this->reception_type === 'first_come') {
                if ($this->is_unlimited_capacity) {
                    return 'application';
                }

                $capacity = $this->capacity ?? 0;
                // 개인 프로그램은 once-full 정책을 위해 DB의 applied_count(누적 신청수)를 기준으로 마감 여부 판단
                $appliedCount = $this->applied_count ?? 0;

                if ($appliedCount >= $capacity) {
                    return 'closed';
                }

                return 'application';
            }
        }

        // 단체 프로그램용 기존 로직
        if ($this->is_unlimited_capacity) {
            return 'application';
        }

        $capacity = $this->capacity ?? 0;
        // 단체는 표시 기준(applied_count_display)을, 개인은 once-full 정책용으로 누적(applied_count)을 사용
        if ($this->application_type === 'individual') {
            $appliedCount = $this->applied_count ?? 0;
        } else {
            $appliedCount = $this->applied_count_display ?? 0;
        }

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

        if ($this->application_start_date instanceof Carbon && $this->application_start_date->greaterThan($now)) {
            return 'scheduled';
        }

        // 신청 종료일이 지났으면 마감
        if ($this->application_end_date instanceof Carbon && $this->application_end_date->lt($now)) {
            return 'closed';
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
        $appliedCount = $this->applied_count_display ?? 0;

        return max(0, $capacity - $appliedCount);
    }

    public function getAppliedCountDisplayAttribute(): int
    {
        // 개인 프로그램인 경우 취소 제외한 신청 건수로 표시
        if ($this->application_type === 'individual') {
            return $this->applications()->where('payment_status', '!=', IndividualApplication::PAYMENT_STATUS_CANCELLED)->count();
        }

        // 단체 프로그램은 취소 제외한 신청만 합산 (승인대기 + 승인완료)
        return $this->groupApplications()
            ->where('payment_status', '!=', 'cancelled')
            ->sum('applicant_count') ?? 0;
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IndividualApplication::class, 'program_reservation_id');
    }

    public function groupApplications(): HasMany
    {
        return $this->hasMany(GroupApplication::class, 'program_reservation_id');
    }
}
