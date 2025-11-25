<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * 카테고리 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 카테고리 삭제 (외래키 고려)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // FAQ 카테고리 그룹
        // ========================================
        
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
