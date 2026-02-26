<?php

namespace Database\Seeders;

use App\Models\BoardSkin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardSkinSeeder extends Seeder
{
    /**
     * 게시판 스킨 데이터를 시드합니다.
     * data/board_skins.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/board_skins.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            BoardSkin::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            foreach ($rows as $row) {
                $this->normalizeJson($row, ['options']);
                DB::table('board_skins')->insert($row);
            }
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BoardSkin::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $skins = [
            [
                'name' => '기본 스킨',
                'directory' => 'default',
                'description' => '기본 게시판 스킨입니다. 모든 게시판에서 사용할 수 있는 기본 스킨입니다.',
                'thumbnail' => null,
                'options' => [
                    'show_view_count' => true,
                    'show_date' => true,
                    'list_date_format' => 'Y-m-d',
                    'view_date_format' => 'Y-m-d H:i',
                ],
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => '갤러리 스킨',
                'directory' => 'gallery',
                'description' => '갤러리 형태의 게시판에서 사용할 수 있는 이미지 중심 스킨입니다.',
                'thumbnail' => null,
                'options' => [
                    'show_view_count' => true,
                    'show_date' => true,
                    'list_date_format' => 'Y-m-d',
                    'view_date_format' => 'Y-m-d H:i',
                    'gallery_columns' => 3,
                    'thumbnail_width' => 300,
                    'thumbnail_height' => 200,
                ],
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($skins as $skin) {
            BoardSkin::create([
                'name' => $skin['name'],
                'directory' => $skin['directory'],
                'description' => $skin['description'],
                'thumbnail' => $skin['thumbnail'],
                'options' => json_encode($skin['options']),
                'is_active' => $skin['is_active'],
                'is_default' => $skin['is_default'],
            ]);
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
