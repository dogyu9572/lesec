<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardFaqSeeder extends Seeder
{
    /**
     * FAQ 게시판 게시글을 시드합니다.
     * database/seeders/data/board_faq.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/board_faq.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            if (!empty($rows)) {
                DB::table('board_faq')->delete();
                foreach ($rows as $row) {
                    $insert = $this->normalizeBoardFaqRow($row);
                    DB::table('board_faq')->insert($insert);
                }
            }
            return;
        }

        $now = now()->format('Y-m-d H:i:s');
        $posts = [
            [
                'user_id' => null,
                'title' => '참가비는 어떻게 입금하나요?',
                'content' => '<p>참가비는 참여 확정 문자 수신 후 안내된 계좌로 입금해 주시면 됩니다.</p>',
                'author_name' => '관리자',
                'password' => null,
                'is_notice' => false,
                'is_secret' => false,
                'is_active' => true,
                'category' => '신청/입금/환불',
                'attachments' => null,
                'view_count' => 0,
                'sort_order' => 1,
                'custom_fields' => null,
                'thumbnail' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ];
        DB::table('board_faq')->insert($posts);
    }

    private function normalizeBoardFaqRow(array $row): array
    {
        $jsonKeys = ['attachments', 'custom_fields'];
        foreach ($jsonKeys as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = json_decode($row[$key], true);
            }
            if (isset($row[$key]) && is_array($row[$key])) {
                $row[$key] = json_encode($row[$key], JSON_UNESCAPED_UNICODE);
            }
        }
        return $row;
    }
}
