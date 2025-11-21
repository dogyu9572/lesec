<?php

namespace App\Http\Controllers\Introduction;

use App\Http\Controllers\Controller;
use App\Services\Board\BoardContentService;

class GreetingContactController extends Controller
{
    protected BoardContentService $boardContentService;

    public function __construct(BoardContentService $boardContentService)
    {
        $this->boardContentService = $boardContentService;
    }

    /**
     * 인사말 페이지
     */
    public function greeting()
    {
        $gNum = "04"; $sNum = "01"; $gName = "센터소개"; $sName = "인사말";
        $board = $this->boardContentService->getBoard('greetings');
        $post = $this->boardContentService->getLatestPost($board);

        return view('introduction.greeting', compact('gNum', 'sNum', 'gName', 'sName', 'post'));
    }

    /**
     * 연락처 페이지
     */
    public function contact()
    {
        $gNum = "04"; $sNum = "03"; $gName = "센터소개"; $sName = "인사말";
        $board = $this->boardContentService->getBoard('contacts');
        $contacts = $this->boardContentService->getActivePosts($board);

        return view('introduction.contact', compact('gNum', 'sNum', 'gName', 'sName', 'contacts'));
    }
}

