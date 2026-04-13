<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberLoginRequest;
use Illuminate\Http\Request;
use App\Services\Member\MemberAuthService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class MemberAuthController extends Controller
{
    /** 연속 실패 이 횟수 이상이면 잠금 (5회 실패 후 6번째 요청부터 차단) */
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_LOCKOUT_SECONDS = 600;

    public function __construct(private readonly MemberAuthService $authService) {}
    /**
     * 로그인 페이지 표시
     */
    public function showLoginForm(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if ($this->hasSuspiciousQuery($request)) {
            abort(400);
        }

        // 로그인 성공 후 돌아갈 URL 설정 (오픈 리다이렉트 방지: 상대 경로만 허용)
        $redirect = $request->query('redirect');
        if (is_string($redirect) && $redirect !== '') {
            $redirect = trim($redirect);
            // "/path?query" 형태만 허용
            if (str_starts_with($redirect, '/')) {
                $request->session()->put('url.intended', $redirect);
            }
        }

        return view('member.login', $this->loginPageData($request, new ViewErrorBag()));
    }

    /**
     * 로그인 처리
     */
    public function login(MemberLoginRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    {
        $limiterKey = $this->buildLoginRateLimitKey(
            (string) $request->input('login_id'),
            (string) $request->ip()
        );

        if (RateLimiter::tooManyAttempts($limiterKey, self::LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            $minutes = (int) ceil(max($seconds, 1) / 60);

            $errors = (new ViewErrorBag())->put(
                'default',
                new MessageBag(['login_failed' => ["로그인 시도 횟수를 초과했습니다. {$minutes}분 후 다시 시도해 주세요."]])
            );

            return response()->view(
                'member.login',
                $this->loginPageData($request, $errors),
                429
            );
        }

        $credentials = [
            'login_id' => $request->get('login_id'),
            'password' => $request->get('password'),
            'is_active' => true,
        ];

        if ($this->authService->attemptLogin($credentials, $request->boolean('remember_login_id'))) {
            RateLimiter::clear($limiterKey);
            $request->session()->regenerate();

            $response = redirect()->intended(route('home'));

            // 아이디 저장 기능
            if ($request->boolean('remember_login_id')) {
                // 쿠키에 아이디 저장 (1년 유지, 525600분 = 60 * 24 * 365)
                $response->cookie('saved_login_id', $request->get('login_id'), 525600, '/', null, false, false);
            } else {
                // 체크 해제 시 쿠키 삭제
                $response->withoutCookie('saved_login_id');
            }

            return $response;
        }

        RateLimiter::hit($limiterKey, self::LOGIN_LOCKOUT_SECONDS);
        $failedCount = RateLimiter::attempts($limiterKey);

        $lockoutMinutes = (int) (self::LOGIN_LOCKOUT_SECONDS / 60);
        $errors = (new ViewErrorBag())->put(
            'default',
            new MessageBag([
                'login_failed' => '아이디 또는 비밀번호가 올바르지 않습니다. '
                    . "(연속 실패 {$failedCount}회, ".self::LOGIN_MAX_ATTEMPTS."회 실패 시 {$lockoutMinutes}분간 로그인이 제한됩니다.)",
            ])
        );

        // POST 응답 본문에 실패 횟수가 포함되도록 302 대신 422 + 뷰 반환 (DAST 등 동일 본문 오탐 방지)
        return response()->view('member.login', $this->loginPageData($request, $errors), 422);
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

    private function hasSuspiciousQuery(Request $request): bool
    {
        $allowedKeys = ['redirect'];
        $queryKeys = array_keys($request->query());

        foreach ($queryKeys as $key) {
            if (!in_array((string) $key, $allowedKeys, true)) {
                return true;
            }
        }

        return false;
    }

    private function buildLoginRateLimitKey(string $loginId, string $ipAddress): string
    {
        return 'member-login:' . sha1(strtolower(trim($loginId)) . '|' . $ipAddress);
    }

    /**
     * 로그인 폼 뷰 데이터 (GET / 실패 POST / 잠금 429 공통)
     */
    private function loginPageData(Request $request, ViewErrorBag $errors): array
    {
        $savedLoginId = null;
        if (! $request->old('login_id')) {
            $savedLoginId = $request->cookie('saved_login_id');
        }

        $hasLoginFailed = $errors->has('login_failed');

        if ($request->isMethod('POST') && $hasLoginFailed) {
            $loginIdField = (string) $request->input('login_id', '');
            $rememberLoginChecked = $request->boolean('remember_login_id');
        } else {
            $loginIdField = old('login_id', $savedLoginId ?? '');
            $rememberLoginChecked = (bool) old('remember_login_id') || (bool) $savedLoginId;
        }

        return [
            'gNum' => '00',
            'sNum' => '01',
            'gName' => '로그인',
            'sName' => '로그인',
            'savedLoginId' => $savedLoginId,
            'loginIdField' => $loginIdField,
            'rememberLoginChecked' => $rememberLoginChecked,
            'loginFailedMessage' => $errors->first('login_failed'),
            'errors' => $errors,
        ];
    }

}
