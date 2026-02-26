<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserMenuPermissionsSeeder extends Seeder
{
    /**
     * 사용자별 메뉴 권한. data/user_menu_permissions.json 이 있으면 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/user_menu_permissions.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        DB::table('user_menu_permissions')->delete();
        foreach ($rows as $row) {
            DB::table('user_menu_permissions')->insert($row);
        }
    }
}
