<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MemberAccountRecoveryController extends Controller
{
    /**
     * 아이디 찾기 페이지를 노출합니다.
     */
    public function showFindIdForm(): View
    {
        return view('member.find_id');
    }

    /**
     * 아이디 찾기 결과 페이지를 노출합니다.
     */
    public function showFindIdResultPage(): View
    {
        return view('member.find_id_end');
    }

    /**
     * 비밀번호 찾기 페이지를 노출합니다.
     */
    public function showFindPasswordForm(): View
    {
        return view('member.find_pw');
    }

    /**
     * 비밀번호 변경 페이지를 노출합니다.
     */
    public function showPasswordChangeForm(): View
    {
        return view('member.find_pw_change');
    }

    /**
     * 비밀번호 변경 완료 페이지를 노출합니다.
     */
    public function showPasswordChangeCompletePage(): View
    {
        return view('member.find_pw_end');
    }
}

