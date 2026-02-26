<?php

namespace App\Services\Backoffice;

use App\Models\Board;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardPostService
{
    /**
     * 동적 테이블명 생성
     */
    public function getTableName(string $slug): string
    {
        return 'board_' . $slug;
    }

    /**
     * 게시글 목록 조회
     */
    public function getPosts(string $slug, Request $request)
    {
        $query = DB::table($this->getTableName($slug));

        $this->applySearchFilters($query, $request);

        // 목록 개수 설정
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 15;

        // 정렬 기능이 활성화된 게시판인지 확인
        $board = Board::where('slug', $slug)->first();

        // 공지글은 항상 위에
        if ($board && $board->isNoticeEnabled()) {
            $query->orderBy('is_notice', 'desc');
        }

        // sort_order가 NULL이거나 0이면 등록일 빠른순, 아니면 sort_order 내림차순(큰 값이 위)
        // sort_order가 있는 것들을 먼저 보여주고, 그 다음에 sort_order가 없는 것들을 등록일 빠른순으로
        $query->orderByRaw('CASE WHEN sort_order IS NULL OR sort_order = 0 THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CASE WHEN sort_order IS NULL OR sort_order = 0 THEN created_at ELSE sort_order END DESC')
            ->orderBy('created_at', 'asc');

        $posts = $query->paginate($perPage)->withQueryString();

        $this->transformDates($posts);
        return $posts;
    }

    /**
     * 검색 필터 적용
     */
    private function applySearchFilters($query, Request $request): void
    {
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('keyword')) {
            $this->applyKeywordSearch($query, $request->keyword, $request->search_type);
        }
    }

    /**
     * 키워드 검색 적용
     */
    private function applyKeywordSearch($query, string $keyword, ?string $searchType): void
    {
        if ($searchType === 'title') {
            $query->where('title', 'like', "%{$keyword}%");
        } elseif ($searchType === 'content') {
            $query->where('content', 'like', "%{$keyword}%");
        } else {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }
    }

    /**
     * 날짜 변환
     */
    private function transformDates($posts): void
    {
        $posts->getCollection()->transform(function ($post) {
            foreach (['created_at', 'updated_at'] as $dateField) {
                if (isset($post->$dateField) && is_string($post->$dateField)) {
                    $post->$dateField = Carbon::parse($post->$dateField);
                }
            }
            return $post;
        });
    }

    /**
     * 게시글 저장
     */
    public function storePost(string $slug, array $validated, Request $request, $board): int
    {
        $data = $this->preparePostData($validated, $request, $slug, $board);

        return DB::table($this->getTableName($slug))->insertGetId($data);
    }

    /**
     * 게시글 데이터 준비
     */
    private function preparePostData(array $validated, Request $request, string $slug, $board): array
    {
        // 정렬 기능 활성화된 게시판인 경우 자동으로 sort_order 설정
        $sortOrder = 0;
        if ($board && $board->enable_sorting) {
            $sortOrder = $this->getNextSortOrder($slug);
        }

        return [
            'user_id' => null,
            'author_name' => $validated['author_name'] ?? '관리자',
            'title' => $validated['title'],
            'content' => $this->sanitizeContent($validated['content']),
            'category' => $validated['category'] ?? null,
            'is_notice' => $request->has('is_notice'),
            'is_secret' => $request->has('is_secret'),
            'is_active' => $request->has('is_active'),
            'password' => $validated['password'] ?? null,
            'thumbnail' => $this->handleThumbnail($request, $slug),
            'attachments' => json_encode($this->handleAttachments($request, $slug)),
            'custom_fields' => $this->getCustomFieldsJson($request, $board),
            'view_count' => 0,
            'sort_order' => $sortOrder,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * 다음 정렬 순서 값을 계산합니다 (외부에서 호출 가능)
     */
    public function calculateNextSortOrder(string $slug): int
    {
        $maxSortOrder = DB::table($this->getTableName($slug))
            ->max('sort_order');

        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * 다음 정렬 순서 값을 가져옵니다 (정렬 기능 활성화된 게시판에만 사용)
     */
    private function getNextSortOrder(string $slug): int
    {
        return $this->calculateNextSortOrder($slug);
    }

    /**
     * HTML 내용 정리
     */
    private function sanitizeContent(string $content): string
    {
        // 빈 문자열이나 null 체크
        if (empty($content)) {
            return '';
        }

        // font 태그를 span 태그로 변환 (color 속성을 style로 변환)
        $content = preg_replace_callback(
            '/<font\s+color=["\']?([^"\'>\s]+)["\']?[^>]*>(.*?)<\/font>/is',
            function ($matches) {
                $color = $matches[1];
                $text = $matches[2];
                return '<span style="color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '">' . $text . '</span>';
            },
            $content
        );

        // color 속성이 없는 font 태그는 그대로 유지 (나중에 제거될 수 있음)
        // strip_tags는 속성을 유지하므로 스타일이 보존됨
        // Summernote가 생성하는 모든 태그 허용: <b>, <i>, <s>, <strike> 포함
        $allowedTags = '<p><br><strong><b><em><i><u><s><strike><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><table><thead><tbody><tr><td><th><a><img><div><span><iframe><video><source><font>';
        $cleaned = strip_tags($content, $allowedTags);

        // strip_tags 결과가 false일 경우 원본 반환
        if ($cleaned === false) {
            return $content;
        }

        return $cleaned;
    }

    /**
     * 썸네일 처리
     */
    private function handleThumbnail(Request $request, string $slug): ?string
    {
        // 썸네일 제거 요청이 있는 경우
        if ($request->has('remove_thumbnail')) {
            return null;
        }

        // 새 썸네일이 업로드된 경우
        if ($request->hasFile('thumbnail')) {
            return $request->file('thumbnail')->store('thumbnails/' . $slug, 'public');
        }

        // 기존 썸네일이 있는 경우 보존
        if ($request->has('existing_thumbnail')) {
            return $request->input('existing_thumbnail');
        }

        return null;
    }

    /**
     * 첨부파일 처리
     */
    private function handleAttachments(Request $request, string $slug): array
    {
        $attachments = [];
        $removeIndices = $request->input('remove_attachments', []);

        // 기존 첨부파일 보존 (제거 요청이 없는 것만)
        if ($request->has('existing_attachments')) {
            $existingAttachments = $request->input('existing_attachments', []);
            foreach ($existingAttachments as $index => $attachment) {
                // 제거 요청이 있는 인덱스는 제외
                if (in_array($index, $removeIndices)) {
                    continue;
                }

                if (is_string($attachment)) {
                    $attachment = json_decode($attachment, true);
                }
                if (is_array($attachment) && isset($attachment['name'], $attachment['path'])) {
                    $attachments[] = $attachment;
                }
            }
        }

        // 새 첨부파일 추가
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store('uploads/' . $slug, 'public'),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        return $attachments;
    }

    /**
     * 커스텀 필드 JSON 생성
     */
    private function getCustomFieldsJson(Request $request, $board): ?string
    {
        $customFields = $this->processCustomFields($request, $board);
        return !empty($customFields) ? json_encode($customFields) : null;
    }

    /**
     * 커스텀 필드 처리
     */
    private function processCustomFields(Request $request, $board): array
    {
        if (!$board->custom_fields_config || !is_array($board->custom_fields_config)) {
            return [];
        }

        $customFields = [];
        foreach ($board->custom_fields_config as $fieldConfig) {
            $fieldName = $fieldConfig['name'];
            $fieldValue = $request->input("custom_field_{$fieldName}");

            // 에디터 타입인 경우 HTML sanitize 적용 (문자열인 경우에만)
            if ($fieldConfig['type'] === 'editor' && !empty($fieldValue) && is_string($fieldValue)) {
                $fieldValue = $this->sanitizeContent($fieldValue);
            }

            $customFields[$fieldName] = $fieldValue;
        }
        return $customFields;
    }

    /**
     * 게시글 조회
     */
    public function getPost(string $slug, int $postId)
    {
        $post = DB::table($this->getTableName($slug))->where('id', $postId)->first();

        if (!$post) {
            return null;
        }

        $this->transformSinglePostDates($post);
        return $post;
    }

    /**
     * 단일 게시글 날짜 변환
     */
    private function transformSinglePostDates($post): void
    {
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (isset($post->$dateField) && is_string($post->$dateField)) {
                $post->$dateField = Carbon::parse($post->$dateField);
            }
        }
    }

    /**
     * 게시글 수정
     */
    public function updatePost(string $slug, int $postId, array $validated, Request $request, $board): bool
    {
        $data = $this->prepareUpdateData($validated, $request, $slug, $board, $postId);

        return DB::table($this->getTableName($slug))
            ->where('id', $postId)
            ->update($data);
    }

    /**
     * 수정 데이터 준비
     */
    private function prepareUpdateData(array $validated, Request $request, string $slug, $board, int $postId): array
    {
        // 기존 게시글 조회 (is_active 값 유지를 위해)
        $existingPost = $this->getPost($slug, $postId);

        $data = [
            'title' => $validated['title'],
            'content' => $this->sanitizeContent($validated['content']),
            'category' => $validated['category'] ?? null,
            'is_notice' => $request->has('is_notice'),
            'is_secret' => $request->has('is_secret'),
            'author_name' => $validated['author_name'] ?? '관리자',
            'password' => $validated['password'] ?? null,
            'thumbnail' => $this->handleThumbnail($request, $slug),
            'attachments' => json_encode($this->handleAttachments($request, $slug)),
            'custom_fields' => $this->getCustomFieldsJson($request, $board),
            'sort_order' => $request->input('sort_order', 0),
            'updated_at' => now()
        ];

        // is_active는 요청에 있으면 업데이트, 없으면 기존 값 유지
        if ($request->has('is_active')) {
            $data['is_active'] = $request->has('is_active');
        } elseif ($existingPost) {
            $data['is_active'] = $existingPost->is_active ?? true;
        } else {
            $data['is_active'] = true; // 기본값
        }

        return $data;
    }

    /**
     * 게시글 삭제
     */
    public function deletePost(string $slug, int $postId): bool
    {
        $post = DB::table($this->getTableName($slug))->where('id', $postId)->first();

        if (!$post) {
            return false;
        }

        $this->deleteAttachments($post);

        return DB::table($this->getTableName($slug))->where('id', $postId)->delete();
    }

    /**
     * 첨부파일 삭제
     */
    private function deleteAttachments($post): void
    {
        if (!$post->attachments) {
            return;
        }

        $attachments = json_decode($post->attachments, true);
        if (!is_array($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            if (isset($attachment['path'])) {
                $filePath = storage_path('app/public/' . $attachment['path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(string $slug, array $postIds): int
    {
        return DB::table($this->getTableName($slug))->whereIn('id', $postIds)->delete();
    }
}
