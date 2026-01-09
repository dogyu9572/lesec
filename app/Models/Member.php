<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'login_id',
        'password',
        'member_type',
        'member_group_id',
        'name',
        'email',
        'birth_date',
        'gender',
        'contact',
        'parent_contact',
        'city',
        'district',
        'school_name',
        'school_id',
        'grade',
        'class_number',
        'address',
        'zipcode',
        'emergency_contact',
        'emergency_contact_relation',
        'profile_image',
        'email_consent',
        'sms_consent',
        'kakao_consent',
        'memo',
        'is_active',
        'joined_at',
        'last_login_at',
        'withdrawal_at',
        'withdrawal_reason',
    ];

    /**
     * 직렬화 시 숨겨야 할 속성들
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'birth_date' => 'date',
        'joined_at' => 'datetime',
        'last_login_at' => 'datetime',
        'withdrawal_at' => 'datetime',
        'is_active' => 'boolean',
        'email_consent' => 'boolean',
        'sms_consent' => 'boolean',
        'kakao_consent' => 'boolean',
        'grade' => 'integer',
        'class_number' => 'integer',
    ];

    /**
     * 회원 그룹과의 관계
     */
    public function memberGroup()
    {
        return $this->belongsTo(MemberGroup::class, 'member_group_id');
    }

    /**
     * 교사인지 확인
     */
    public function isTeacher(): bool
    {
        return $this->member_type === 'teacher';
    }

    /**
     * 학생인지 확인
     */
    public function isStudent(): bool
    {
        return $this->member_type === 'student';
    }

    /**
     * 활성화된 회원만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 교사만 조회
     */
    public function scopeTeachers($query)
    {
        return $query->where('member_type', 'teacher');
    }

    /**
     * 학생만 조회
     */
    public function scopeStudents($query)
    {
        return $query->where('member_type', 'student');
    }

    /**
     * 학교와의 관계
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * 연락처 포맷팅 (표시용)
     */
    public function getFormattedContactAttribute(): ?string
    {
        return $this->formatPhoneNumber($this->contact);
    }

    /**
     * 보호자 연락처 포맷팅 (표시용)
     */
    public function getFormattedParentContactAttribute(): ?string
    {
        return $this->formatPhoneNumber($this->parent_contact);
    }

    /**
     * 비상 연락처 포맷팅 (표시용)
     */
    public function getFormattedEmergencyContactAttribute(): ?string
    {
        return $this->formatPhoneNumber($this->emergency_contact);
    }

    /**
     * 전화번호 포맷팅 헬퍼
     */
    private function formatPhoneNumber(?string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $digits);
        }

        return $digits;
    }
}
