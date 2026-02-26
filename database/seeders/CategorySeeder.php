<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * 카테고리 데이터를 시드합니다.
     * data/categories.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/categories.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Category::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            foreach ($rows as $row) {
                DB::table('categories')->insert($row);
            }
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faqGroup = Category::create([
            'parent_id' => null,
            'code' => 'F001',
            'name' => 'FAQ',
            'depth' => 0,
            'display_order' => 0,
            'is_active' => true,
        ]);

        // 1차: 신청/입금/환불
        Category::create([
            'parent_id' => $faqGroup->id,
            'code' => 'F002',
            'name' => '신청/입금/환불',
            'depth' => 1,
            'display_order' => 0,
            'is_active' => true,
        ]);

        // 1차: 수료증
        Category::create([
            'parent_id' => $faqGroup->id,
            'code' => 'F003',
            'name' => '수료증',
            'depth' => 1,
            'display_order' => 0,
            'is_active' => true,
        ]);

        // 1차: 대기자
        Category::create([
            'parent_id' => $faqGroup->id,
            'code' => 'F004',
            'name' => '대기자',
            'depth' => 1,
            'display_order' => 0,
            'is_active' => true,
        ]);

        // 1차: 회원정보
        Category::create([
            'parent_id' => $faqGroup->id,
            'code' => 'F005',
            'name' => '회원정보',
            'depth' => 1,
            'display_order' => 0,
            'is_active' => true,
        ]);

        // 1차: 일반
        Category::create([
            'parent_id' => $faqGroup->id,
            'code' => 'F006',
            'name' => '일반',
            'depth' => 1,
            'display_order' => 0,
            'is_active' => true,
        ]);

        $this->command->info('카테고리 시더 실행 완료!');
        $this->command->info('- FAQ 그룹: 1개 그룹, 5개 카테고리 생성');
    }
}
