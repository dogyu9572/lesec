<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminGroup;
use App\Models\GroupMenuPermission;
use App\Models\AdminMenu;

class AdminGroupSeeder extends Seeder
{
    /**
     * 관리자 권한 그룹 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 그룹 데이터 삭제
        AdminGroup::query()->delete();

        // 모든 메뉴 ID 가져오기
        $allMenuIds = AdminMenu::where('is_active', true)->pluck('id')->toArray();

        // 1. 전체 권한 그룹
        $fullAccessGroup = AdminGroup::create([
            'name' => '전체 권한',
            'description' => '모든 메뉴에 접근 가능한 관리자 그룹',
            'is_active' => true,
        ]);

        // 모든 메뉴에 권한 부여
        foreach ($allMenuIds as $menuId) {
            GroupMenuPermission::create([
                'group_id' => $fullAccessGroup->id,
                'menu_id' => $menuId,
                'granted' => true,
            ]);
        }

        // 2. 콘텐츠 관리자 그룹
        $contentManagerGroup = AdminGroup::create([
            'name' => '콘텐츠 관리자',
            'description' => '게시판, 팝업, 배너 등 콘텐츠 관련 메뉴만 접근 가능',
            'is_active' => true,
        ]);

        // 콘텐츠 관련 메뉴만 권한 부여 (대시보드, 게시판관리, 홈페이지관리, 기업정보관리)
        $contentMenuIds = AdminMenu::whereIn('id', [1, 19, 21, 22])
            ->orWhereIn('parent_id', [19, 21, 22])
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        foreach ($contentMenuIds as $menuId) {
            GroupMenuPermission::create([
                'group_id' => $contentManagerGroup->id,
                'menu_id' => $menuId,
                'granted' => true,
            ]);
        }

        // 3. 뷰어 그룹
        $viewerGroup = AdminGroup::create([
            'name' => '뷰어',
            'description' => '대시보드 및 조회만 가능한 그룹',
            'is_active' => true,
        ]);

        // 대시보드만 권한 부여
        GroupMenuPermission::create([
            'group_id' => $viewerGroup->id,
            'menu_id' => 1, // 대시보드
            'granted' => true,
        ]);
    }
}

