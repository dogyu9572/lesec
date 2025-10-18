<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminGroup extends Model
{
    use HasFactory;

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 이 그룹의 메뉴 권한들과의 관계
     */
    public function groupMenuPermissions()
    {
        return $this->hasMany(GroupMenuPermission::class, 'group_id');
    }

    /**
     * 이 그룹에 속한 사용자들
     */
    public function users()
    {
        return $this->hasMany(User::class, 'admin_group_id');
    }

    /**
     * 이 그룹이 접근 가능한 메뉴들
     */
    public function menus()
    {
        return $this->belongsToMany(AdminMenu::class, 'group_menu_permissions', 'group_id', 'menu_id')
            ->wherePivot('granted', true)
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * 특정 메뉴에 대한 권한 확인
     */
    public function hasMenuPermission($menuId): bool
    {
        return $this->groupMenuPermissions()
            ->where('menu_id', $menuId)
            ->where('granted', true)
            ->exists();
    }

    /**
     * 그룹의 모든 메뉴 권한 조회
     */
    public function getAllMenuPermissions(): array
    {
        return $this->groupMenuPermissions()
            ->where('granted', true)
            ->pluck('menu_id')
            ->toArray();
    }

    /**
     * 활성화된 그룹만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

