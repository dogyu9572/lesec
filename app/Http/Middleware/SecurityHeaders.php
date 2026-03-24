<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sparrow 웹 취약점 점검 대응: 보안 헤더 추가
 * - X-Frame-Options, Referrer-Policy, X-Content-Type-Options, X-XSS-Protection
 * - Content-Security-Policy (응답에 이미 있으면 생략 — nginx 등과 이중 헤더 방지)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
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

    /**
     * 사용자·백오피스 공통 CSP (인라인 이벤트·레거시 스크립트·CDN·토스 위젯 대응).
     * 프론트 style-src에 unsafe-inline: 다음·카카오 로드맵/지도 SDK가 JS로 style 속성·CSSOM 인라인 적용함.
     */
    private function contentSecurityPolicy(Request $request): string
    {
        if ($request->is('backoffice') || $request->is('backoffice/*')) {
            return implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://api.tosspayments.com https://log.tosspayments.com https://*.tosspayments.com",
                "frame-src 'self' https://*.tosspayments.com",
                "form-action 'self' https://*.tosspayments.com",
                "object-src 'none'",
                "base-uri 'self'",
            ]);
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://ssl.daumcdn.net https://*.daumcdn.net https://*.kakaocdn.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://*.kakaocdn.net",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://api.tosspayments.com https://log.tosspayments.com https://*.tosspayments.com https://*.daumcdn.net https://*.kakao.com",
            "frame-src 'self' https://*.tosspayments.com",
            "form-action 'self' https://*.tosspayments.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);
    }
}
