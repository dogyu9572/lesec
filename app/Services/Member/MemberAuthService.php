<?php

namespace App\Services\Member;

use Illuminate\Support\Facades\Auth;

class MemberAuthService
{
    public function attemptLogin(array $credentials, bool $remember): bool
    {
        return Auth::guard('member')->attempt($credentials, $remember);
    }

    public function logout(): void
    {
        Auth::guard('member')->logout();
    }
}


