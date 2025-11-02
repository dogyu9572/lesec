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
        // 기존 카테고리 삭제
        Category::truncate();

        // ========================================
        // 게시판 카테고리 그룹
        // ========================================
        
        $boardGroup = Category::create([
            'parent_id' => null,
            'code' => 'C001',
            'name' => '게시판',
            'depth' => 0,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 1차: 공지사항
        $notice = Category::create([
            'parent_id' => $boardGroup->id,
            'code' => 'C002',
            'name' => '공지사항',
            'depth' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 일반공지
        $generalNotice = Category::create([
            'parent_id' => $notice->id,
            'code' => 'C003',
            'name' => '일반공지',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 긴급
        Category::create([
            'parent_id' => $generalNotice->id,
            'code' => 'C004',
            'name' => '긴급',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 일반
        Category::create([
            'parent_id' => $generalNotice->id,
            'code' => 'C005',
            'name' => '일반',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 시스템공지
        Category::create([
            'parent_id' => $notice->id,
            'code' => 'C006',
            'name' => '시스템공지',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 1차: 자료실
        $archive = Category::create([
            'parent_id' => $boardGroup->id,
            'code' => 'C007',
            'name' => '자료실',
            'depth' => 1,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 업무자료
        Category::create([
            'parent_id' => $archive->id,
            'code' => 'C008',
            'name' => '업무자료',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 양식자료
        Category::create([
            'parent_id' => $archive->id,
            'code' => 'C009',
            'name' => '양식자료',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // 상품 카테고리 그룹
        // ========================================

        $productGroup = Category::create([
            'parent_id' => null,
            'code' => 'C010',
            'name' => '상품',
            'depth' => 0,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 1차: 의류
        $clothing = Category::create([
            'parent_id' => $productGroup->id,
            'code' => 'C011',
            'name' => '의류',
            'depth' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 상의
        $top = Category::create([
            'parent_id' => $clothing->id,
            'code' => 'C012',
            'name' => '상의',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 티셔츠
        Category::create([
            'parent_id' => $top->id,
            'code' => 'C013',
            'name' => '티셔츠',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 셔츠
        Category::create([
            'parent_id' => $top->id,
            'code' => 'C014',
            'name' => '셔츠',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 하의
        $bottom = Category::create([
            'parent_id' => $clothing->id,
            'code' => 'C015',
            'name' => '하의',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 3차: 청바지
        Category::create([
            'parent_id' => $bottom->id,
            'code' => 'C016',
            'name' => '청바지',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 슬랙스
        Category::create([
            'parent_id' => $bottom->id,
            'code' => 'C017',
            'name' => '슬랙스',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 1차: 액세서리
        $accessory = Category::create([
            'parent_id' => $productGroup->id,
            'code' => 'C018',
            'name' => '액세서리',
            'depth' => 1,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 가방
        Category::create([
            'parent_id' => $accessory->id,
            'code' => 'C019',
            'name' => '가방',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 모자
        Category::create([
            'parent_id' => $accessory->id,
            'code' => 'C020',
            'name' => '모자',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        $this->command->info('카테고리 시더 실행 완료!');
        $this->command->info('- 게시판 그룹: 1개 그룹, 8개 카테고리 생성');
        $this->command->info('- 상품 그룹: 1개 그룹, 11개 카테고리 생성');
    }
}
