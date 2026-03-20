<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 신뢰하는 HTTP Host 목록 (Host 헤더 취약점 대응)
    |--------------------------------------------------------------------------
    |
    | 비어 있으면 APP_URL의 호스트와 로컬 개발용 호스트만 허용합니다.
    | 운영에서 www 등 추가 호스트가 있으면 .env에 쉼표로 구분해 지정합니다.
    |
    | 예: TRUSTED_HOSTS=lesec.snu.ac.kr,www.lesec.snu.ac.kr
    |
    */

    'trusted_hosts' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_HOSTS', ''))))),

];
