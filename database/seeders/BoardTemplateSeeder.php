<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BoardTemplate;
use App\Models\BoardSkin;
use App\Models\Category;

class BoardTemplateSeeder extends Seeder
{
    /**
     * 기본 템플릿 시더
     */
    public function run(): void
    {
        // 기본 스킨 가져오기
        $defaultSkin = BoardSkin::first();
        
        if (!$defaultSkin) {
            $this->command->warn('스킨이 없습니다. 먼저 BoardSkinSeeder를 실행하세요.');
            return;
        }

        // 게시판 카테고리 그룹 조회 (depth=0, name='게시판')
        $boardCategoryGroup = Category::where('depth', 0)
            ->where('name', '게시판')
            ->first();

        $templates = [
            // 1. 공지사항 템플릿
            [
                'name' => '공지사항',
                'description' => '관리자가 작성하는 공지사항 게시판',
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => true, 'required' => true, 'label' => '제목'],
                    'content' => ['enabled' => true, 'required' => true, 'label' => '내용'],
                    'category' => ['enabled' => false, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => true, 'required' => true, 'label' => '작성자'],
                    'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => true, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => null,
                'enable_notice' => true,
                'enable_sorting' => false,
                'enable_category' => false,
                'category_id' => null,
                'list_count' => 20,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
                'is_active' => true,
            ],

            // 2. 갤러리 템플릿
            [
                'name' => '갤러리',
                'description' => '썸네일 이미지를 보여주는 갤러리형 게시판',
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => true, 'required' => true, 'label' => '제목'],
                    'content' => ['enabled' => true, 'required' => true, 'label' => '내용'],
                    'category' => ['enabled' => true, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => true, 'required' => true, 'label' => '작성자'],
                    'password' => ['enabled' => true, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => true, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => true, 'required' => true, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => null,
                'enable_notice' => true,
                'enable_sorting' => false,
                'enable_category' => true,
                'category_id' => $boardCategoryGroup?->id,
                'list_count' => 12,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_active' => true,
            ],

            // 3. FAQ 템플릿
            [
                'name' => 'FAQ',
                'description' => '자주 묻는 질문 게시판',
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => true, 'required' => true, 'label' => '질문'],
                    'content' => ['enabled' => true, 'required' => true, 'label' => '답변'],
                    'category' => ['enabled' => true, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => false, 'required' => false, 'label' => '작성자'],
                    'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => false, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => null,
                'enable_notice' => false,
                'enable_sorting' => true,
                'enable_category' => true,
                'category_id' => $boardCategoryGroup?->id,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
                'is_active' => true,
            ],

            // 4. 일반 게시판 템플릿
            [
                'name' => '일반 게시판',
                'description' => '모든 기능이 활성화된 기본 게시판',
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => true, 'required' => true, 'label' => '제목'],
                    'content' => ['enabled' => true, 'required' => true, 'label' => '내용'],
                    'category' => ['enabled' => true, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => true, 'required' => true, 'label' => '작성자'],
                    'password' => ['enabled' => true, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => true, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => true, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => null,
                'enable_notice' => true,
                'enable_sorting' => false,
                'enable_category' => true,
                'category_id' => $boardCategoryGroup?->id,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            BoardTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('✓ 기본 템플릿 4종이 생성되었습니다.');
    }
}
