<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberGroup extends Model
{
    use HasFactory;

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'member_count',
        'sort_order',
        'color',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'is_active' => 'boolean',
        'member_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 이 그룹에 속한 회원들
     */
    public function members()
    {
        return $this->hasMany(Member::class, 'member_group_id');
    }

    /**
     * 활성화된 그룹만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 정렬된 그룹 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                     ->orderBy('name', 'asc');
    }
}
