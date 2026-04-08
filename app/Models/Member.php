<?php

namespace App\Models;

use App\Casts\DeterministicEncryptedString;
use App\Casts\EncryptedDate;
use App\Support\Formatting;
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
        'email' => DeterministicEncryptedString::class,
        'birth_date' => EncryptedDate::class,
        'contact' => DeterministicEncryptedString::class,
        'parent_contact' => DeterministicEncryptedString::class,
        'school_name' => 'encrypted',
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
     * 학교명 부분 검색 (schools.school_name 기준).
     * members.school_name 은 암호화되어 SQL LIKE 로는 검색할 수 없음.
     */
    public function scopeWhereSchoolNameKeyword($query, string $keyword): void
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return;
        }

        $query->whereHas('school', function ($schoolQuery) use ($keyword) {
            $schoolQuery->where('school_name', 'like', '%'.$keyword.'%');
        });
    }

    /**
     * 학교와의 관계
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * 결정적 암호화 컬럼(email, contact, parent_contact)을 평문·레거시 포맷·암호문과 동시에 조회할 때 사용
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function applyWhereDeterministicFieldMatches($query, string $column, string $plain): void
    {
        if (! in_array($column, ['email', 'contact', 'parent_contact'], true)) {
            return;
        }

        $cipher = DeterministicEncryptedString::encryptForQuery($column, $plain);
        $normalized = DeterministicEncryptedString::normalizePlaintext($column, $plain);

        $query->where(function ($q) use ($column, $cipher, $normalized) {
            if ($cipher !== null && $cipher !== '') {
                $q->where($column, $cipher);
            }
            if ($normalized !== '') {
                $q->orWhere($column, $normalized);
            }
            if (($column === 'contact' || $column === 'parent_contact') && $normalized !== '') {
                $formatted = Formatting::formatPhone($normalized);
                if ($formatted !== null && $formatted !== $normalized) {
                    $q->orWhere($column, $formatted);
                }
            }
        });
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
