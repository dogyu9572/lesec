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

        // 템플릿 조회 (선택적으로 사용)
        $noticeTemplate = BoardTemplate::where('name', '공지사항')->first();
        $galleryTemplate = BoardTemplate::where('name', '갤러리')->first();

        $boards = [
            [
                'name' => '공지사항',
                'slug' => 'notices',
                'description' => '관리자가 작성하는 공지사항 게시판',
                'skin_id' => 1,
                'template_id' => $noticeTemplate?->id,
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
                'name' => '갤러리',
                'slug' => 'gallerys',
                'description' => '썸네일 이미지를 보여주는 갤러리형 게시판',
                'skin_id' => 2,
                'template_id' => $galleryTemplate?->id,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 12,
                'enable_notice' => true,
                'is_single_page' => false,
                'enable_sorting' => false,
                'hot_threshold' => 100,
                'permission_read' => 'all',
                'permission_write' => 'member',
                'permission_comment' => 'member',
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
            ],
            [
                'name' => '인사말',
                'slug' => 'greetings',
                'description' => '인사말 게시판',
                'skin_id' => 1,
                'template_id' => null,
                'is_active' => true,
                'table_created' => false,
                'list_count' => 1,
                'enable_notice' => true,
                'is_single_page' => true,
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
                    'attachments' => ['enabled' => false, 'required' => false, 'label' => '첨부파일'],
                    'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
                    'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
                    'created_at' => ['enabled' => true, 'required' => false, 'label' => '등록일'],
                ],
                'custom_fields_config' => [
                    [
                        'name' => 'greeting_ko',
                        'type' => 'editor',
                        'label' => '인사말 국문',
                        'options' => null,
                        'required' => false,
                        'max_length' => null,
                        'placeholder' => null,
                    ],
                    [
                        'name' => 'greeting_en',
                        'type' => 'editor',
                        'label' => '인사말 영문',
                        'options' => null,
                        'required' => false,
                        'max_length' => null,
                        'placeholder' => null,
                    ],
                    [
                        'name' => 'ceo_message_ko',
                        'type' => 'text',
                        'label' => '대표이사 국문',
                        'options' => null,
                        'required' => false,
                        'max_length' => null,
                        'placeholder' => null,
                    ],
                    [
                        'name' => 'ceo_message_en',
                        'type' => 'text',
                        'label' => '대표이사 영문',
                        'options' => null,
                        'required' => false,
                        'max_length' => null,
                        'placeholder' => null,
                    ],
                ],
            ],
        ];

        foreach ($boards as $board) {
            Board::create($board);
        }
    }
}
