<?php

namespace App\Services\Board;

use App\Models\Board;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoardContentService
{
    /**
     * 게시판 정보를 조회한다.
     */
    public function getBoard(string $slug): Board
    {
        return Board::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * 게시글 목록을 페이지네이션 형태로 반환한다.
     */
    public function getPostPaginator(Board $board, array $filters = []): LengthAwarePaginator
    {
        $table = $this->getTableName($board->slug);

        $query = DB::table($table)
            ->whereNull('deleted_at');

        $this->applyKeywordFilter($query, $filters);
        $this->applyCategoryFilter($query, $filters);

        if ($board->enable_sorting) {
            $query->orderBy('sort_order', 'desc');
        }

        if ($board->isNoticeEnabled()) {
            $query->orderBy('is_notice', 'desc');
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $this->resolvePerPage($board, $filters);

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($post) => $this->transformPost($post))
        );

        return $paginator;
    }

    /**
     * 게시글 상세 정보를 조회한다.
     */
    public function getPostDetail(Board $board, int $postId): ?array
    {
        $table = $this->getTableName($board->slug);

        $post = DB::table($table)
            ->where('id', $postId)
            ->whereNull('deleted_at')
            ->first();

        if (!$post) {
            return null;
        }

        $this->increaseViewCount($table, $postId);

        $post = $this->transformPost($post);

        $previous = $this->findAdjacentPost($table, $post, 'previous');
        $next = $this->findAdjacentPost($table, $post, 'next');

        return [
            'post' => $post,
            'previous' => $previous ? $this->transformPost($previous) : null,
            'next' => $next ? $this->transformPost($next) : null,
        ];
    }

    /**
     * 첨부파일 다운로드 응답을 생성한다.
     */
    public function downloadAttachment(Board $board, int $postId, int $attachmentIndex): ?StreamedResponse
    {
        $table = $this->getTableName($board->slug);

        $post = DB::table($table)
            ->where('id', $postId)
            ->whereNull('deleted_at')
            ->first();

        if (!$post) {
            return null;
        }

        if (empty($post->attachments)) {
            return null;
        }

        $attachments = $this->decodeAttachments($post->attachments);
        
        if (empty($attachments)) {
            return null;
        }

        $attachmentsArray = array_values($attachments);
        
        if ($attachmentIndex < 0 || $attachmentIndex >= count($attachmentsArray)) {
            return null;
        }

        $attachment = $attachmentsArray[$attachmentIndex];

        if (!is_array($attachment) || empty($attachment['path'])) {
            return null;
        }

        $path = $attachment['path'];
        $downloadName = $attachment['name'] ?? basename($path);

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->download($path, $downloadName);
    }

    /**
     * 활성화된 게시글을 모아온다.
     */
    public function getActivePosts(Board $board, int $limit = 0, ?callable $queryCallback = null): Collection
    {
        $table = $this->getTableName($board->slug);
        $query = DB::table($table)->whereNull('deleted_at');

        if ($queryCallback) {
            $queryCallback($query);
        }

        if ($board->enable_sorting) {
            $query->orderBy('sort_order', 'desc');
        }

        $query->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $posts = $query->get();

        return $posts->map(fn ($post) => $this->transformPost($post));
    }

    /**
     * 최신 게시글 한 건을 얻는다.
     */
    public function getLatestPost(Board $board): ?object
    {
        return $this->getActivePosts($board, 1)->first();
    }

    /**
     * 게시판에서 사용 가능한 카테고리 목록을 반환한다.
     */
    public function getAvailableCategories(Board $board): Collection
    {
        if (!$board->isFieldEnabled('category')) {
            return collect();
        }

        $table = $this->getTableName($board->slug);

        $categories = DB::table($table)
            ->whereNull('deleted_at')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return $categories;
    }

    /**
     * 테이블명을 생성한다.
     */
    private function getTableName(string $slug): string
    {
        return 'board_' . $slug;
    }

    /**
     * 키워드 검색 조건을 적용한다.
     */
    private function applyKeywordFilter(Builder $query, array $filters): void
    {
        $keyword = trim($filters['keyword'] ?? '');

        if ($keyword === '') {
            return;
        }

        $searchType = $filters['search_type'] ?? null;

        if ($searchType === 'title') {
            $query->where('title', 'like', '%' . $keyword . '%');
            return;
        }

        if ($searchType === 'content') {
            $query->where('content', 'like', '%' . $keyword . '%');
            return;
        }

        $query->where(function (Builder $subQuery) use ($keyword) {
            $subQuery->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('content', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * 카테고리 필터를 적용한다.
     */
    private function applyCategoryFilter(Builder $query, array $filters): void
    {
        $category = $filters['category'] ?? null;

        if (!$category) {
            return;
        }

        $query->where('category', $category);
    }

    /**
     * 페이지 당 게시글 수를 결정한다.
     */
    private function resolvePerPage(Board $board, array $filters): int
    {
        $perPage = (int) ($filters['per_page'] ?? $board->list_count ?? 10);

        if ($perPage < 1) {
            $perPage = 10;
        }

        return $perPage;
    }

    /**
     * 게시글 데이터를 변환한다.
     */
    private function transformPost(object $post): object
    {
        $post->attachments = $this->decodeAttachments($post->attachments);

        if (!empty($post->custom_fields)) {
            $post->custom_fields = $this->decodeCustomFields($post->custom_fields);
        }

        if (!empty($post->created_at)) {
            $post->created_at = Carbon::parse($post->created_at);
        }

        if (!empty($post->updated_at)) {
            $post->updated_at = Carbon::parse($post->updated_at);
        }

        return $post;
    }

    /**
     * 첨부파일 JSON을 배열로 변환한다.
     */
    private function decodeAttachments(?string $attachments): array
    {
        if (!$attachments) {
            return [];
        }

        $decoded = json_decode($attachments, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 커스텀 필드 JSON을 배열로 변환한다.
     */
    private function decodeCustomFields(?string $customFields): array
    {
        if (!$customFields) {
            return [];
        }

        $decoded = json_decode($customFields, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 조회수를 증가시킨다.
     */
    private function increaseViewCount(string $table, int $postId): void
    {
        DB::table($table)
            ->where('id', $postId)
            ->increment('view_count');
    }

    /**
     * 이전 혹은 다음 게시글을 찾는다.
     */
    private function findAdjacentPost(string $table, object $currentPost, string $direction): ?object
    {
        $operator = $direction === 'previous' ? '<' : '>';
        $order = $direction === 'previous' ? 'desc' : 'asc';

        $currentCreatedAt = $currentPost->created_at instanceof Carbon
            ? $currentPost->created_at
            : Carbon::parse($currentPost->created_at);

        return DB::table($table)
            ->whereNull('deleted_at')
            ->where('created_at', $operator, $currentCreatedAt->toDateTimeString())
            ->orderBy('created_at', $order)
            ->orderBy('id', $order)
            ->first();
    }
}

