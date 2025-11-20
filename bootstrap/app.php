<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\BackOfficeAuth;
use App\Http\Middleware\TrackVisitor;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
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
        //
    })->create();
