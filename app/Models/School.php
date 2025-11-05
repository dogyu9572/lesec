<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'source_type',
        'city',
        'district',
        'school_level',
        'school_name',
        'school_code',
        'address',
        'phone',
        'homepage',
        'is_coed',
        'day_night_division',
        'founding_date',
        'status',
        'last_synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_coed' => 'boolean',
        'founding_date' => 'date',
        'last_synced_at' => 'datetime',
    ];

    /**
     * 등록자와의 관계
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 수정자와의 관계
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 회원과의 관계
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * 구분별로 조회
     */
    public function scopeBySourceType($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * 시/도별로 조회
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * 시/군/구별로 조회
     */
    public function scopeByDistrict($query, string $district)
    {
        return $query->where('district', $district);
    }

    /**
     * 학교급별로 조회
     */
    public function scopeBySchoolLevel($query, string $schoolLevel)
    {
        return $query->where('school_level', $schoolLevel);
    }

    /**
     * 활성화된 학교만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'normal');
    }

    /**
     * 구분 한글명 반환
     */
    public function getSourceTypeNameAttribute(): string
    {
        $types = [
            'api' => 'API',
            'user_registration' => '사용자 등록',
            'admin_registration' => '관리자 등록',
        ];

        return $types[$this->source_type] ?? $this->source_type;
    }

    /**
     * 학교급 한글명 반환
     */
    public function getSchoolLevelNameAttribute(): ?string
    {
        if (!$this->school_level) {
            return null;
        }

        $levels = [
            'elementary' => '초등학교',
            'middle' => '중학교',
            'high' => '고등학교',
        ];

        return $levels[$this->school_level] ?? $this->school_level;
    }

    /**
     * 상태 한글명 반환
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = [
            'normal' => '정상',
            'error' => '오류',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
