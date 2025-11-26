<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'neis' => [
        'api_key' => env('NEIS_API_KEY'),
        'endpoint' => 'https://open.neis.go.kr/hub/schoolInfo',
    ],

    'schoolinfo' => [
        'api_key' => env('SCHOOLINFO_API_KEY'),
        'api_endpoint' => 'https://www.schoolinfo.go.kr/openApi.do',
    ],

    'sms_kakao' => [
        'api_key' => env('SMS_KAKAO_API_KEY', 'secure-api-key'),
        'sms_api_url' => env('SMS_KAKAO_SMS_API_URL', 'https://lesecsms.hk-test.co.kr/api/sms'),
        'kakao_api_url' => env('SMS_KAKAO_KAKAO_API_URL', 'https://lesecsms.hk-test.co.kr/api/kakao'),
        'kakao_template_code' => env('SMS_KAKAO_TEMPLATE_CODE', 'bizp_2022012015300394819857304'),
    ],

];
