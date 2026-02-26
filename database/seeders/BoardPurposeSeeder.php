<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoardPurposeSeeder extends Seeder
{
    /**
     * 설립목적 게시판 데이터를 시드합니다.
     * data/board_purpose.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        if (!Schema::hasTable('board_purpose')) {
            return;
        }
        $path = database_path('seeders/data/board_purpose.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        DB::table('board_purpose')->delete();
        foreach ($rows as $row) {
            $this->normalizeJsonColumns($row, ['attachments', 'custom_fields']);
            DB::table('board_purpose')->insert($row);
        }
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
