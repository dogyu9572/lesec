<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 기존 카테고리 삭제 (테스트용)
        Category::truncate();

        // ========================================
        // 게시판 카테고리 (board)
        // ========================================
        
        // 1차: 공지사항
        $notice = Category::create([
            'category_group' => 'board',
            'name' => '공지사항',
            'depth' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 일반공지
        $generalNotice = Category::create([
            'parent_id' => $notice->id,
            'category_group' => 'board',
            'name' => '일반공지',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 긴급
        Category::create([
            'parent_id' => $generalNotice->id,
            'category_group' => 'board',
            'name' => '긴급',
            'depth' => 3,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 일반
        Category::create([
            'parent_id' => $generalNotice->id,
            'category_group' => 'board',
            'name' => '일반',
            'depth' => 3,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 시스템공지
        Category::create([
            'parent_id' => $notice->id,
            'category_group' => 'board',
            'name' => '시스템공지',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 1차: 자료실
        $archive = Category::create([
            'category_group' => 'board',
            'name' => '자료실',
            'depth' => 1,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 업무자료
        Category::create([
            'parent_id' => $archive->id,
            'category_group' => 'board',
            'name' => '업무자료',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 양식자료
        Category::create([
            'parent_id' => $archive->id,
            'category_group' => 'board',
            'name' => '양식자료',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // 상품 카테고리 (product)
        // ========================================

        // 1차: 의류
        $clothing = Category::create([
            'category_group' => 'product',
            'name' => '의류',
            'depth' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 상의
        $top = Category::create([
            'parent_id' => $clothing->id,
            'category_group' => 'product',
            'name' => '상의',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 티셔츠
        Category::create([
            'parent_id' => $top->id,
            'category_group' => 'product',
            'name' => '티셔츠',
            'depth' => 3,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 셔츠
        Category::create([
            'parent_id' => $top->id,
            'category_group' => 'product',
            'name' => '셔츠',
            'depth' => 3,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 하의
        $bottom = Category::create([
            'parent_id' => $clothing->id,
            'category_group' => 'product',
            'name' => '하의',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 3차: 청바지
        Category::create([
            'parent_id' => $bottom->id,
            'category_group' => 'product',
            'name' => '청바지',
            'depth' => 3,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 3차: 슬랙스
        Category::create([
            'parent_id' => $bottom->id,
            'category_group' => 'product',
            'name' => '슬랙스',
            'depth' => 3,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 1차: 액세서리
        $accessory = Category::create([
            'category_group' => 'product',
            'name' => '액세서리',
            'depth' => 1,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // 2차: 가방
        Category::create([
            'parent_id' => $accessory->id,
            'category_group' => 'product',
            'name' => '가방',
            'depth' => 2,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2차: 모자
        Category::create([
            'parent_id' => $accessory->id,
            'category_group' => 'product',
            'name' => '모자',
            'depth' => 2,
            'display_order' => 2,
            'is_active' => true,
        ]);

        $this->command->info('카테고리 시더 실행 완료!');
        $this->command->info('- board 그룹: 8개 카테고리 생성');
        $this->command->info('- product 그룹: 11개 카테고리 생성');
    }
}
