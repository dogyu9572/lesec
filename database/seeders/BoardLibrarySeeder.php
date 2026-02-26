<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardLibrarySeeder extends Seeder
{
    /**
     * 자료실 게시글 데이터. data/board_library.json 이 있으면 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/board_library.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        DB::table('board_library')->delete();
        foreach ($rows as $row) {
            $this->normalizeJson($row, ['attachments', 'custom_fields']);
            DB::table('board_library')->insert($row);
        }
    }

    private function normalizeJson(array &$row, array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_array($row[$key])) {
                $row[$key] = json_encode($row[$key], JSON_UNESCAPED_UNICODE);
            }
        }
    }
}
