<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminMenu;

class AdminMenuSeeder extends Seeder
{
    /**
     * 관리자 메뉴 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        AdminMenu::query()->delete();

        $menus = [
            [
                'parent_id' => null,
                'name' => '대시보드',
                'url' => '/backoffice',
                'icon' => 'fa-tachometer-alt',
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => null,
                'name' => '시스템 관리',
                'url' => null,
                'icon' => 'fa-cogs',
                'order' => 2,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => null,
                'name' => '기본설정',
                'url' => null,
                'icon' => 'fa-file-alt',
                'order' => 3,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => null,
                'name' => '게시판관리',
                'url' => null,
                'icon' => 'fa-chart-bar',
                'order' => 4,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => null,
                'name' => '홈페이지관리',
                'url' => null,
                'icon' => 'fa-home',
                'order' => 5,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => null,
                'name' => '기업정보관리',
                'url' => null,
                'icon' => 'fa-users',
                'order' => 6,
                'is_active' => true,
                'permission_key' => null,
            ],
        ];

        // parent_id가 null인 메뉴들을 먼저 생성
        $parentMenus = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] === null) {
                $parentMenus[] = AdminMenu::create($menu);
            }
        }

        // 시스템 관리 하위 메뉴
        $systemMenu = $parentMenus[1];
        $childMenus = [
            [
                'parent_id' => $systemMenu->id,
                'name' => '메뉴 관리',
                'url' => '/backoffice/admin-menus',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $systemMenu->id,
                'name' => '게시판 관리',
                'url' => '/backoffice/boards',
                'icon' => null,
                'order' => 2,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $systemMenu->id,
                'name' => '게시판 스킨 관리',
                'url' => '/backoffice/board-skins',
                'icon' => null,
                'order' => 3,
                'is_active' => false,
                'permission_key' => null,
            ],
        ];

        foreach ($childMenus as $menu) {
            AdminMenu::create($menu);
        }

        // 기본설정 하위 메뉴
        $settingMenu = $parentMenus[2];
        $settingChildMenus = [
            [
                'parent_id' => $settingMenu->id,
                'name' => '기본설정',
                'url' => '/backoffice/setting',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $settingMenu->id,
                'name' => '관리자 관리',
                'url' => '/backoffice/admins',
                'icon' => null,
                'order' => 2,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $settingMenu->id,
                'name' => '카테고리 관리',
                'url' => '/backoffice/categories',
                'icon' => null,
                'order' => 3,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $settingMenu->id,
                'name' => '관리자 권한 그룹',
                'url' => '/backoffice/admin-groups',
                'icon' => null,
                'order' => 4,
                'is_active' => true,
                'permission_key' => null,
            ],
        ];

        foreach ($settingChildMenus as $menu) {
            AdminMenu::create($menu);
        }

        // 게시판관리 하위 메뉴
        $boardMenu = $parentMenus[3];
        $boardChildMenus = [
            [
                'parent_id' => $boardMenu->id,
                'name' => '공지사항',
                'url' => '/backoffice/board-posts/notices',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $boardMenu->id,
                'name' => '갤러리',
                'url' => '/backoffice/board-posts/gallerys',
                'icon' => null,
                'order' => 2,
                'is_active' => true,
                'permission_key' => null,
            ],
        ];

        foreach ($boardChildMenus as $menu) {
            AdminMenu::create($menu);
        }

        // 홈페이지관리 하위 메뉴
        $homepageMenu = $parentMenus[4];
        $homepageChildMenus = [
            [
                'parent_id' => $homepageMenu->id,
                'name' => '팝업 관리',
                'url' => '/backoffice/popups',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
            [
                'parent_id' => $homepageMenu->id,
                'name' => '배너 관리',
                'url' => '/backoffice/banners',
                'icon' => null,
                'order' => 2,
                'is_active' => true,
                'permission_key' => null,
            ],
        ];

        foreach ($homepageChildMenus as $menu) {
            AdminMenu::create($menu);
        }

        // 기업정보관리 하위 메뉴
        $companyMenu = $parentMenus[5];
        $companyChildMenus = [
            [
                'parent_id' => $companyMenu->id,
                'name' => '인사말',
                'url' => '/backoffice/board-posts/greetings',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'permission_key' => null,
            ],
        ];

        foreach ($companyChildMenus as $menu) {
            AdminMenu::create($menu);
        }
    }
}
