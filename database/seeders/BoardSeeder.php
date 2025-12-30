<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\BoardTemplate;

class BoardSeeder extends Seeder
{
    /**
     * 게시판 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        Board::query()->forceDelete();

        // 템플릿 조회
        $noticeTemplate = BoardTemplate::where('name', '공지사항')->first();
        $faqTemplate = BoardTemplate::where('name', 'FAQ')->first();
        $libraryTemplate = BoardTemplate::where('name', '자료실')->first();
        $singlePageTemplate = BoardTemplate::where('name', '단일페이지')->first();
        $contactTemplate = BoardTemplate::where('name', '연구원 연락처 관리')->first();
        
        // 스킨 조회
        $defaultSkin = \App\Models\BoardSkin::where('name', '기본 스킨')->first();

        $boards = [
            [
                'id' => 2,
                'name' => '공지사항',
                'slug' => 'notices',
                'description' => '관리자가 작성하는 공지사항 게시판',
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $noticeTemplate?->id ?? 1,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => true,
                'is_single_page' => false,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
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
            ],
            [
                'id' => 5,
                'name' => '자료실',
                'slug' => 'library',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $libraryTemplate?->id ?? 4,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => false,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
            ],
            [
                'id' => 6,
                'name' => 'FAQ',
                'slug' => 'faq',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $faqTemplate?->id ?? 3,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => false,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'admin',
                'permission_comment' => 'member',
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
                'custom_fields_config' => [],
            ],
            [
                'id' => 10,
                'name' => '인사말',
                'slug' => 'greetings',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $singlePageTemplate?->id ?? 5,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => true,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
            ],
            [
                'id' => 11,
                'name' => '연구원 연락처 관리',
                'slug' => 'contacts',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $contactTemplate?->id ?? 6,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => false,
                'enable_sorting' => true,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
            ],
            [
                'id' => 12,
                'name' => '개인정보처리방침 관리',
                'slug' => 'privacy-policy',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $singlePageTemplate?->id ?? 5,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => true,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
            ],
            [
                'id' => 13,
                'name' => '설립목적 관리',
                'slug' => 'purpose',
                'description' => null,
                'skin_id' => $defaultSkin?->id ?? 5,
                'template_id' => $singlePageTemplate?->id ?? 5,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 15,
                'enable_notice' => false,
                'is_single_page' => true,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
                'custom_fields_config' => [
                    ['name' => 'block1_title', 'type' => 'text', 'label' => '블럭 1 제목', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block1_content', 'type' => 'editor', 'label' => '블럭 1 내용', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block2_title', 'type' => 'text', 'label' => '블럭 2 제목', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block2_content', 'type' => 'editor', 'label' => '블럭 2 내용', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block3_title', 'type' => 'text', 'label' => '블럭 3 제목', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block3_content', 'type' => 'editor', 'label' => '블럭 3 내용', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block4_title', 'type' => 'text', 'label' => '블럭 4 제목', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                    ['name' => 'block4_content', 'type' => 'editor', 'label' => '블럭 4 내용', 'options' => null, 'required' => true, 'max_length' => null, 'placeholder' => null],
                ],
            ],
        ];

        foreach ($boards as $board) {
            Board::create($board);
        }
    }
}
