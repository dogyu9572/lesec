<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberLoginRequest;
use Illuminate\Http\Request;
use App\Services\Member\MemberAuthService;

class MemberAuthController extends Controller
{
    public function __construct(private readonly MemberAuthService $authService)
    {
    }
    /**
     * 로그인 페이지 표시
     */
    public function showLoginForm(): \Illuminate\View\View
    {
        $gNum = '00';
        $sNum = '01';
        $gName = '로그인';
        $sName = '로그인';

        return view('member.login', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 로그인 처리
     */
    public function login(MemberLoginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = [
            'login_id' => $request->get('login_id'),
            'password' => $request->get('password'),
            'is_active' => true,
        ];

        if ($this->authService->attemptLogin($credentials, $request->boolean('remember_login_id'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['login_failed' => '아이디 또는 비밀번호가 올바르지 않습니다.'])
            ->onlyInput('login_id');
    }

    /**
     * 로그아웃 처리
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('member.login')
            ->with('status', '로그아웃되었습니다.');
    }
}

