<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Popup;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Services\Board\BoardContentService;

class HomeController extends Controller
{
    protected BoardContentService $boardContentService;

    public function __construct(BoardContentService $boardContentService)
    {
        $this->boardContentService = $boardContentService;
    }

    public function index()
    {
        $gNum = "main";
        $gName = "";
        $sName = "";       
       
        
        $noticePosts = $this->getLatestBoardPosts('notices', 'notice');
        $dataRoomPosts = $this->getLatestBoardPosts('library', 'dataroom');
        $faqPosts = $this->getLatestBoardPosts('faq', 'faq');
        
        // 활성화된 팝업 조회 (쿠키 확인하여 숨겨진 팝업 제외)
        $popups = Popup::select('id', 'title', 'popup_type', 'popup_display_type', 'popup_image', 'popup_content', 'url', 'url_target', 'width', 'height', 'position_top', 'position_left')
            ->active()
            ->inPeriod()
            ->ordered()
            ->get()
            ->filter(function($popup) {
                // 서버사이드에서 쿠키 확인하여 숨겨진 팝업 제외
                $cookieName = 'popup_hide_' . $popup->id;
                return !isset($_COOKIE[$cookieName]) || $_COOKIE[$cookieName] !== '1';
            });

        // 활성화된 배너 조회
        $banners = Banner::active()
            ->inPeriod()
            ->ordered()
            ->get();
        
        return view('home.index', compact('gNum', 'gName', 'sName', 'noticePosts', 'dataRoomPosts', 'faqPosts', 'popups', 'banners'));
    }
    
    /**
     * 특정 게시판의 최신글을 가져옵니다.
     */
    private function getLatestPosts($boardSlug, $limit = 4)
    {
        try {
            $board = Board::where('slug', $boardSlug)->first();
            if (!$board) {
                return collect();
            }
            
            $tableName = "board_{$boardSlug}";
            
            // 테이블 존재 여부 확인
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                return collect();
            }
            
            return DB::table($tableName)
                ->select('id', 'title', 'created_at', 'thumbnail', 'category')
                ->where('deleted_at', null)
                ->where('category', '국문')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($post) use ($boardSlug) {
                    return (object) [
                        'id' => $post->id,
                        'title' => $post->title,
                        'created_at' => $post->created_at,
                        'thumbnail' => $post->thumbnail,
                        'url' => route('backoffice.board-posts.show', [$boardSlug, $post->id])
                    ];
                });
                
        } catch (\Exception $e) {
            Log::error("게시판 데이터 조회 오류: " . $e->getMessage());
            return collect();
        }
    }

    private function getLatestBoardPosts(string $boardSlug, string $routeNameBase, int $limit = 4)
    {
        try {
            $board = $this->boardContentService->getBoard($boardSlug);
        } catch (\Exception $e) {
            Log::error("게시판 데이터 조회 오류: " . $e->getMessage());
            return collect();
        }

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
        $paginator = $this->boardContentService->getPostPaginator($board, ['per_page' => $limit]);
        $posts = collect($paginator->items());

        return $posts->map(function ($post) use ($routeNameBase) {
            return (object) [
                'id' => $post->id,
                'title' => $post->title,
                'created_at' => $post->created_at,
                'category' => $post->category ?? null,
                'url' => $this->resolveBoardPostUrl($routeNameBase, $post->id)
            ];
        });
    }

    private function resolveBoardPostUrl(string $routeNameBase, int $postId): string
    {
        $detailRoute = "board.{$routeNameBase}.view";

        if (Route::has($detailRoute)) {
            return route($detailRoute, [$postId]);
        }

        return route("board.{$routeNameBase}");
    }
}