<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMenuPermission extends Model
{
    /**
     * 테이블명 지정
     */
    protected $table = 'admin_group_menu_permissions';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'group_id',
        'menu_id',
        'granted'
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'granted' => 'boolean',
    ];

    /**
     * 권한 그룹과의 관계
     */
    public function group()
    {
        return $this->belongsTo(AdminGroup::class, 'group_id');
    }

    /**
     * 메뉴와의 관계
     */
    public function menu()
    {
        return $this->belongsTo(AdminMenu::class, 'menu_id');
    }
}

