<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 관리자 사용자 시더 실행
        $this->call(AdminUserSeeder::class);

        // 사용자 시더 실행
        $this->call(UserSeeder::class);

        // 관리자 메뉴 시더 실행
        $this->call(AdminMenuSeeder::class);

        // 게시판 스킨 시더 실행
        $this->call(BoardSkinSeeder::class);

        // 게시판 템플릿 시더 실행 (카테고리보다 먼저)
        $this->call(BoardTemplateSeeder::class);

        // 카테고리 시더 실행 (템플릿 이후)
        $this->call(CategorySeeder::class);

        // 게시판 시더 실행
        $this->call(BoardSeeder::class);

        // 인사말 게시판 시더 실행
        $this->call(BoardGreetingsSeeder::class);

        // 배너 시더 실행
        $this->call(BannerSeeder::class);

        // 팝업 시더 실행
        $this->call(PopupSeeder::class);

        // 설정 시더 실행
        $this->call(SettingSeeder::class);

        // 프로그램 시더 실행
        $this->call(ProgramSeeder::class);

        // 회원 그룹 시더 실행
        $this->call(MemberGroupSeeder::class);

        // 회원 시더 실행 (그룹 이후)
        $this->call(MemberSeeder::class);
    }
}
