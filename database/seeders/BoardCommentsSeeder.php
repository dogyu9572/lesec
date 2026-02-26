<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardCommentsSeeder extends Seeder
{
    /**
     * 게시판 댓글 데이터. data/board_comments.json 이 있으면 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/board_comments.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        DB::table('board_comments')->delete();
        foreach ($rows as $row) {
            DB::table('board_comments')->insert($row);
        }
    }
}
