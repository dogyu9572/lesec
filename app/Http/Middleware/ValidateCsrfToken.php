<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

/**
 * 서울대·Sparrow 규칙 등에서 요구하는 CSRF 필드명(CSRFToken, OWASP_CSRFTOKEN, anticsrf)과
 * Laravel 기본 _token을 동일하게 검증한다.
 */
class ValidateCsrfToken extends Middleware
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    protected function getTokenFromRequest($request): ?string
    {
        foreach (['_token', 'CSRFToken', 'OWASP_CSRFTOKEN', 'anticsrf'] as $key) {
            $value = $request->input($key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return parent::getTokenFromRequest($request);
    }
}
