<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'type',
        'application_type',
        'host',
        'period_start',
        'period_end',
        'location',
        'target',
        'detail_content',
        'other_info',
        'is_active',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * 활성화된 프로그램만 조회하는 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 타입별로 프로그램 조회
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 신청유형별로 프로그램 조회
     */
    public function scopeByApplicationType($query, string $applicationType)
    {
        return $query->where('application_type', $applicationType);
    }

    /**
     * 타입과 신청유형으로 프로그램 조회 (없으면 생성)
     */
    public static function findOrCreateByTypeAndApplicationType(string $type, string $applicationType = 'individual'): self
    {
        return static::firstOrCreate(
            ['type' => $type, 'application_type' => $applicationType],
            ['is_active' => true]
        );
    }

    /**
     * 타입으로 프로그램 조회 (없으면 생성) - 기존 호환성 유지
     */
    public static function findOrCreateByType(string $type): self
    {
        return static::firstOrCreate(
            ['type' => $type, 'application_type' => 'individual'],
            ['is_active' => true]
        );
    }

    /**
     * 프로그램 타입 한글명 반환
     */
    public function getTypeNameAttribute(): string
    {
        $types = [
            'middle_semester' => '중등학기',
            'middle_vacation' => '중등방학',
            'high_semester' => '고등학기',
            'high_vacation' => '고등방학',
            'special' => '특별프로그램',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * 신청유형 한글명 반환
     */
    public function getApplicationTypeNameAttribute(): string
    {
        $types = [
            'individual' => '개인',
            'group' => '단체',
        ];

        return $types[$this->application_type] ?? $this->application_type;
    }

    /**
     * 전체 탭명 반환 (예: "중등학기 개인")
     */
    public function getFullTabNameAttribute(): string
    {
        return $this->type_name . ' ' . $this->application_type_name;
    }
}
