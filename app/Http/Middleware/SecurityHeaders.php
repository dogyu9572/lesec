<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sparrow/WSCAN 대응: 보안 헤더 및 CSP
 * - CSP는 응답에 이미 있으면 생략 (nginx 등과 이중 헤더 방지)
 * - 요청마다 nonce 발급 후 View 공유, 인라인 script/style에 nonce 속성 필요
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
        $request->attributes->set('csp_nonce', $nonce);
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));
        }

        return $response;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        $nonce = $request->attributes->get('csp_nonce', '');

        if ($request->is('backoffice') || $request->is('backoffice/*')) {
            return $this->backofficePolicy($nonce);
        }

        return $this->frontPolicy($nonce);
    }

    /**
     * 사용자 사이트: script-src 에 unsafe-inline 미사용.
     * style-src 에는 레이어 팝업·게시 HTML 등 인라인 style 속성 대응을 위해 unsafe-inline 유지.
     */
    private function frontPolicy(string $nonce): string
    {
        $styleSrc = [
            "'self'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://*.kakaocdn.net',
            "'unsafe-inline'",
        ];

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://ssl.daumcdn.net https://*.daumcdn.net https://*.kakaocdn.net",
            'style-src ' . implode(' ', $styleSrc),
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://api.tosspayments.com https://log.tosspayments.com https://*.tosspayments.com https://*.daumcdn.net https://*.kakao.com",
            "frame-src 'self' https://*.tosspayments.com",
            "form-action 'self' https://*.tosspayments.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);
    }

    /**
     * 백오피스: Summernote·다수 onclick 등으로 script/style unsafe-inline 유지.
     * (nonce가 함께 있으면 unsafe-inline이 무시되어 이벤트 핸들러가 차단될 수 있음)
     */
    private function backofficePolicy(string $nonce): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://api.tosspayments.com https://log.tosspayments.com https://*.tosspayments.com",
            "frame-src 'self' https://*.tosspayments.com",
            "form-action 'self' https://*.tosspayments.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);
    }
}
