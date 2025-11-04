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
                'id' => 1,
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
                'is_single_page' => false,
                'list_count' => 20,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],

            // 2. 갤러리 템플릿
            [
                'id' => 2,
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
                'is_single_page' => false,
                'list_count' => 12,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],

            // 3. FAQ 템플릿
            [
                'id' => 3,
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
                'enable_sorting' => false,
                'enable_category' => true,
                'category_id' => $boardCategoryGroup?->id,
                'is_single_page' => false,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],

            // 4. 자료실 템플릿
            [
                'id' => 4,
                'name' => '자료실',
                'description' => '모든 기능이 활성화된 기본 게시판',
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
                'enable_notice' => false,
                'enable_sorting' => false,
                'enable_category' => false,
                'category_id' => null,
                'is_single_page' => false,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],

            // 5. 단일페이지 템플릿
            [
                'id' => 5,
                'name' => '단일페이지',
                'description' => null,
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => true, 'required' => true, 'label' => '제목'],
                    'content' => ['enabled' => true, 'required' => true, 'label' => '내용'],
                    'category' => ['enabled' => false, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => false, 'required' => false, 'label' => '작성자'],
                    'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => false, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => false, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => null,
                'enable_notice' => false,
                'enable_sorting' => false,
                'enable_category' => false,
                'category_id' => null,
                'is_single_page' => true,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],

            // 6. 연구원 연락처 관리 템플릿
            [
                'id' => 6,
                'name' => '연구원 연락처 관리',
                'description' => null,
                'skin_id' => $defaultSkin->id,
                'field_config' => [
                    'title' => ['enabled' => false, 'required' => false, 'label' => '제목'],
                    'content' => ['enabled' => false, 'required' => false, 'label' => '내용'],
                    'category' => ['enabled' => false, 'required' => false, 'label' => '카테고리'],
                    'author_name' => ['enabled' => false, 'required' => false, 'label' => '작성자'],
                    'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
                    'attachments' => ['enabled' => false, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => [
                    ['name' => 'team', 'type' => 'text', 'label' => '팀', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'name', 'type' => 'text', 'label' => '이름', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'position', 'type' => 'text', 'label' => '직책', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'responsibilities', 'type' => 'text', 'label' => '담당업무', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'contact', 'type' => 'text', 'label' => '연락처', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'email', 'type' => 'text', 'label' => '이메일', 'options' => null, 'required' => false, 'max_length' => null, 'placeholder' => null],
                ],
                'enable_notice' => false,
                'enable_sorting' => true,
                'enable_category' => false,
                'category_id' => null,
                'is_single_page' => false,
                'list_count' => 15,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
                'is_system' => false,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            BoardTemplate::updateOrCreate(
                ['id' => $template['id']],
                $template
            );
        }

        $this->command->info('✓ 기본 템플릿 6종이 생성되었습니다.');
    }
}
