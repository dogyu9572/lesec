<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * 허용된 Host 헤더만 통과시켜 Host 헤더 기반 공격(피싱 링크 등)을 완화합니다.
 */
class TrustHosts extends Middleware
{
    /**
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $configured = config('security.trusted_hosts', []);
        if (is_array($configured) && $configured !== []) {
            return array_values(array_unique(array_merge(
                $configured,
                ['localhost', '127.0.0.1', '[::1]'],
            )));
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $appHost = is_string($appHost) && $appHost !== '' ? $appHost : null;

        return array_values(array_filter(array_unique(array_merge(
            $appHost !== null ? [$appHost] : [],
            ['localhost', '127.0.0.1', '[::1]'],
        ))));
    }
}
