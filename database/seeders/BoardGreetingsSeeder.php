<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardGreetingsSeeder extends Seeder
{
    /**
     * 인사말 게시판 데이터를 시드합니다.
     * data/board_greetings.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/board_greetings.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            DB::table('board_greetings')->delete();
            foreach ($rows as $row) {
                $this->normalizeJsonColumns($row, ['attachments', 'custom_fields']);
                DB::table('board_greetings')->insert($row);
            }
            return;
        }

        DB::table('board_greetings')->delete();
        DB::table('board_greetings')->insert([
            'user_id' => null,
            'title' => '인사말',
            'content' => '<p>환영합니다.</p>',
            'author_name' => '관리자',
            'password' => null,
            'is_notice' => true,
            'is_secret' => false,
            'category' => null,
            'attachments' => null,
            'view_count' => 0,
            'sort_order' => 0,
            'custom_fields' => null,
            'thumbnail' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function normalizeJsonColumns(array &$row, array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_array($row[$key])) {
                $row[$key] = json_encode($row[$key], JSON_UNESCAPED_UNICODE);
            }
        }
    }
}
