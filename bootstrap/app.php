<?php

use App\Http\Middleware\BackOfficeAuth;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackVisitor;
use App\Http\Middleware\TrustHosts;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('web', TrustHosts::class);

        $middleware->appendToGroup('web', SecurityHeaders::class);

        // 백오피스 경로에 대해 BackOfficeAuth 미들웨어 등록
        $middleware->group('backoffice', [
            BackOfficeAuth::class,
        ]);

        // 방문자 추적 미들웨어를 전역에 등록
        $middleware->append(TrackVisitor::class);

    })
    ->withSchedule(function ($schedule) {
        // 학교 알리미 API 동기화 스케줄러 (매년 3월 1일)
        $schedule->command('schools:sync-from-schoolinfo')
            ->cron('0 0 1 3 *') // 매년 3월 1일 00:00
            ->description('학교 알리미 API에서 학교 정보 자동 동기화');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '세션이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.',
                ], 419);
            }

            return response()->view('errors.419', [], 419);
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if (!app()->hasDebugModeEnabled()
                && !$e instanceof HttpException
                && !$e instanceof TokenMismatchException
            ) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => '서비스 처리 중 오류가 발생했습니다.'], 500);
                }

                return response()->view('errors.500', [], 500);
            }
        });
    })->create();
