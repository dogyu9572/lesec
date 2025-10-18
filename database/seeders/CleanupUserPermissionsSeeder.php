<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupUserPermissionsSeeder extends Seeder
{
    /**
     * 기존 user_menu_permissions 데이터를 삭제합니다.
     */
    public function run(): void
    {
        // user_menu_permissions 테이블 데이터 전체 삭제
        DB::table('user_menu_permissions')->truncate();
        
        $this->command->info('user_menu_permissions 테이블 데이터가 삭제되었습니다.');
    }
}

