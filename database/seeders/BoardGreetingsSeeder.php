<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardGreetingsSeeder extends Seeder
{
    /**
     * 인사말 게시판 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        DB::table('board_greetings')->delete();

        // 샘플 데이터
        $greetings = [
            [
                'user_id' => null,
                'title' => '인사말',
                'content' => '<p>환영합니다.</p>',
                'author_name' => '관리자',
                'password' => null,
                'is_notice' => true,
                'is_secret' => false,
                'category' => null,
                'attachments' => '[]',
                'view_count' => 0,
                'sort_order' => 0,
                'custom_fields' => json_encode([
                    'greeting_ko' => '<p>안녕하세요. 인사말 국문입니다.</p>',
                    'greeting_en' => '<p>Welcome. This is the English greeting.</p>',
                    'ceo_message_ko' => '대표이사 국문',
                    'ceo_message_en' => 'CEO Message English',
                ]),
                'thumbnail' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($greetings as $greeting) {
            DB::table('board_greetings')->insert($greeting);
        }
    }
}
