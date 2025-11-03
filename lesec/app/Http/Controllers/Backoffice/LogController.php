<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\UserAccessLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * 접속 로그 목록을 표시
     */
    public function access()
    {
        // 실제 구현에서는 접속 로그 모델에서 데이터를 가져와야 함
        // 현재는 더미 데이터로 구현
        $logs = [
            [
                'id' => 1,
                'ip' => '192.168.1.101',
                'user' => '관리자',
                'accessed_at' => now()->subMinutes(5),
                'status' => 'success'
            ],
            [
                'id' => 2,
                'ip' => '118.235.12.45',
                'user' => 'user@example.com',
                'accessed_at' => now()->subHours(1),
                'status' => 'success'
            ],
            [
                'id' => 3,
                'ip' => '121.143.88.201',
                'user' => 'unknown',
                'accessed_at' => now()->subHours(2),
                'status' => 'fail'
            ],
        ];

        return view('backoffice.logs.access', compact('logs'));
    }

    /**
     * 사용자 접속로그 목록
     */
    public function userAccessLogs(Request $request)
    {
        $logs = UserAccessLog::with('user')
            ->users()
            ->search($request)
            ->orderBy('login_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return view('backoffice.logs.user-access', compact('logs'));
    }

    /**
     * 관리자 접속로그 목록
     */
    public function adminAccessLogs(Request $request)
    {
        $logs = UserAccessLog::with('user')
            ->admins()
            ->search($request)
            ->orderBy('login_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return view('backoffice.logs.admin-access', compact('logs'));
    }
}
