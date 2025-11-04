<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'type',
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
     * 타입으로 프로그램 조회 (없으면 생성)
     */
    public static function findOrCreateByType(string $type): self
    {
        return static::firstOrCreate(
            ['type' => $type],
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
}
