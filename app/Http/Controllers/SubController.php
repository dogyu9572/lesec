<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Board\BoardContentService;

class SubController extends Controller
{
    protected BoardContentService $boardContentService;

    public function __construct(BoardContentService $boardContentService)
    {
        $this->boardContentService = $boardContentService;
    }

//게시판
	//공지사항
    public function notice(Request $request)
    {
        $gNum = "02"; $sNum = "01"; $gName = "게시판"; $sName = "공지사항";
        $board = $this->boardContentService->getBoard('notices');
        $filters = [
            'keyword' => $request->input('keyword'),
            'search_type' => $request->input('search_type'),
        ];
        $posts = $this->boardContentService->getPostPaginator($board, $filters);

        return view('board.notice', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'board' => $board,
            'posts' => $posts,
            'filters' => $filters,
            'totalCount' => $posts->total(),
        ]);
    }
	//공지사항 - 상세
    public function notice_view(int $postId)
    {
        $gNum = "02"; $sNum = "01"; $gName = "게시판"; $sName = "공지사항";
        $board = $this->boardContentService->getBoard('notices');
        $detail = $this->boardContentService->getPostDetail($board, $postId);

        if (!$detail) {
            abort(404, '요청하신 게시글을 찾을 수 없습니다.');
        }

        return view('board.notice_view', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'board' => $board,
            'post' => $detail['post'],
            'previousPost' => $detail['previous'],
            'nextPost' => $detail['next'],
        ]);
    }
	//FAQ
    public function faq(Request $request)
    {
        $gNum = "02"; $sNum = "02"; $gName = "게시판"; $sName = "FAQ";
        $board = $this->boardContentService->getBoard('faq');
        $filters = [
            'keyword' => $request->input('keyword'),
            'search_type' => $request->input('search_type'),
            'category' => $request->input('category'),
        ];
        $posts = $this->boardContentService->getPostPaginator($board, $filters);
        $categories = collect([
            '신청/입금/환불',
            '수료증',
            '대기자',
            '회원정보',
            '일반',
        ]);

        return view('board.faq', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'board' => $board,
            'posts' => $posts,
            'filters' => $filters,
            'categories' => $categories,
            'totalCount' => $posts->total(),
        ]);
    }
	//자료실
    public function dataroom(Request $request)
    {
        $gNum = "02"; $sNum = "03"; $gName = "게시판"; $sName = "자료실";
        $board = $this->boardContentService->getBoard('library');
        $filters = [
            'keyword' => $request->input('keyword'),
            'search_type' => $request->input('search_type'),
        ];
        $posts = $this->boardContentService->getPostPaginator($board, $filters);

        return view('board.dataroom', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'board' => $board,
            'posts' => $posts,
            'filters' => $filters,
            'totalCount' => $posts->total(),
        ]);
    }
	//자료실 - 상세
    public function dataroom_view(int $postId)
    {
        $gNum = "02"; $sNum = "03"; $gName = "게시판"; $sName = "자료실";
        $board = $this->boardContentService->getBoard('library');
        $detail = $this->boardContentService->getPostDetail($board, $postId);

        if (!$detail) {
            abort(404, '요청하신 게시글을 찾을 수 없습니다.');
        }

        return view('board.dataroom_view', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'board' => $board,
            'post' => $detail['post'],
            'previousPost' => $detail['previous'],
            'nextPost' => $detail['next'],
        ]);
    }
    public function downloadBoardAttachment(string $boardType, int $postId, int $attachmentIndex)
    {
        $slug = $this->resolveBoardSlug($boardType);

        if (!$slug) {
            abort(404, '요청하신 첨부파일을 찾을 수 없습니다.');
        }

        $board = $this->boardContentService->getBoard($slug);
        $response = $this->boardContentService->downloadAttachment($board, $postId, $attachmentIndex);

        if (!$response) {
            abort(404, '요청하신 첨부파일을 찾을 수 없습니다.');
        }

        return $response;
    }
//마이페이지
	//신청내역 - 단체목록
    public function application_list()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_list', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 단체상세
    public function application_view()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 단체쓰기
    public function application_write()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_write', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 개인목록
    public function application_indi_list()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_indi_list', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 개인상세
    public function application_indi_view()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_indi_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//센터소개
	//인사말
    public function greeting()
    {
        $gNum = "04"; $sNum = "01"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.greeting', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//설립목적
    public function establishment()
    {
        $gNum = "04"; $sNum = "02"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.establishment', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//연락처
    public function contact()
    {
        $gNum = "04"; $sNum = "03"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.contact', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//위치안내
	//오시는 길
    public function location()
    {
        $gNum = "05"; $sNum = "01"; $gName = "위치안내"; $sName = "오시는 길";
        return view('location.location', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//강의실 안내
    public function classroom()
    {
        $gNum = "05"; $sNum = "02"; $gName = "위치안내"; $sName = "강의실 안내";
        return view('location.classroom', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//주차 안내
    public function parking()
    {
        $gNum = "05"; $sNum = "03"; $gName = "위치안내"; $sName = "주차 안내";
        return view('location.parking', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    private function resolveBoardSlug(string $boardType): ?string
    {
        $mapping = [
            'notice' => 'notices',
            'dataroom' => 'library',
            'faq' => 'faq',
        ];

        return $mapping[$boardType] ?? null;
    }
//member
	//로그인
    public function login()
    {
        $gNum = "00"; $sNum = "01"; $gName = "로그인"; $sName = "로그인";
        return view('member.login', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//회원가입
    public function register()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2_a()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2_b()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2_b', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register3_a()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register3_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register3_b()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register3_b', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register4()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register4', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_id()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "아이디 찾기";
        return view('member.find_id', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_id_end()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "아이디 찾기";
        return view('member.find_id_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw_change()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw_change', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw_end()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//약관
	//개인정보처리방침
    public function privacy_policy()
    {
        $gNum = "terms"; $sNum = ""; $gName = "개인정보처리방침"; $sName = "";
        return view('terms.privacy_policy', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//이메일무단수집거부
    public function no_email_collection()
    {
        $gNum = "terms"; $sNum = ""; $gName = "이메일무단수집거부"; $sName = "";
        return view('terms.no_email_collection', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//인쇄
	//견적서
    public function estimate()
    {
        $gNum = "print"; $sNum = ""; $gName = "견적서"; $sName = "";
        return view('print.estimate', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//오류
	//404 페이지 없음
    public function error404()
    {
        $gNum = "00"; $sNum = ""; $gName = "404"; $sName = "";
        return view('error.error404', compact('gNum', 'sNum', 'gName', 'sName'));
    }
}