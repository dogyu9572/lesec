<?php

namespace App\Services\Backoffice;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsKakaoApiService
{
    /**
     * SMS API URL 가져오기
     */
    private function getSmsApiUrl(): string
    {
        return config('services.sms_kakao.sms_api_url', 'https://lesecsms.hk-test.co.kr/api/sms');
    }

    /**
     * 카카오 알림톡 API URL 가져오기
     */
    private function getKakaoApiUrl(): string
    {
        return config('services.sms_kakao.kakao_api_url', 'https://lesecsms.hk-test.co.kr/api/kakao');
    }

    /**
     * 기본 카카오 템플릿 코드
     */
    private const DEFAULT_TEMPLATE_CODE = 'bizp_2022012015300394819857304';

    /**
     * 고정 번호
     */
    private const FIXED_NO = '100001';

    /**
     * SMS 발송
     *
     * @param string $phone 전화번호 (01012345678 형식)
     * @param string $message 메시지 내용
     * @return array ['success' => bool, 'response_code' => string, 'response_message' => string]
     */
    public function sendSms(string $phone, string $message): array
    {
        $phone = $this->formatPhone($phone);

        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'response_code' => 'INVALID_PHONE',
                'response_message' => '전화번호 형식이 올바르지 않습니다.',
            ];
        }

        $apiKey = config('services.sms_kakao.api_key', env('SMS_KAKAO_API_KEY', 'secure-api-key'));

        $data = [
            'no' => self::FIXED_NO,
            'to' => $phone,
            'text' => $message,
            'apiKey' => $apiKey,
        ];

        try {
            Log::info('SMS 발송 API 호출 시작', [
                'phone' => $phone,
                'request_data' => $data,
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getSmsApiUrl(), $data);

            $responseBody = trim($response->body());
            $isSuccess = $response->successful() && !empty($responseBody) && strpos($responseBody, '-') !== 0;

            Log::info('SMS 발송 API 호출 완료', [
                'phone' => $phone,
                'success' => $isSuccess,
                'status' => $response->status(),
                'response' => $responseBody,
                'request_data' => $data,
            ]);

            return [
                'success' => $isSuccess,
                'response_code' => $response->status(),
                'response_message' => $responseBody ?: ($isSuccess ? '발송 성공' : '발송 실패'),
            ];

        } catch (\Exception $e) {
            Log::error('SMS 발송 API 호출 중 오류 발생', [
                'phone' => $phone,
                'request_data' => $data ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'response_code' => 'EXCEPTION',
                'response_message' => 'API 호출 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 카카오 알림톡 발송
     *
     * @param string $phone 전화번호 (01012345678 형식)
     * @param string $message 메시지 내용
     * @param string|null $templateCode 템플릿 코드 (기본값 사용 시 null)
     * @return array ['success' => bool, 'response_code' => string, 'response_message' => string]
     */
    public function sendKakao(string $phone, string $message, ?string $templateCode = null): array
    {
        $phone = $this->formatPhone($phone);

        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'response_code' => 'INVALID_PHONE',
                'response_message' => '전화번호 형식이 올바르지 않습니다.',
            ];
        }

        $apiKey = config('services.sms_kakao.api_key', env('SMS_KAKAO_API_KEY', 'secure-api-key'));
        $templateCode = $templateCode ?? config('services.sms_kakao.kakao_template_code', self::DEFAULT_TEMPLATE_CODE);

        $data = [
            'no' => self::FIXED_NO,
            'to' => $phone,
            'tml' => $templateCode,
            'text' => $message,
            'apiKey' => $apiKey,
        ];

        try {
            Log::info('카카오 알림톡 발송 API 호출 시작', [
                'phone' => $phone,
                'template_code' => $templateCode,
                'request_data' => $data,
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getKakaoApiUrl(), $data);

            $responseBody = trim($response->body());
            $isSuccess = $response->successful() && !empty($responseBody) && strpos($responseBody, '-') !== 0;

            Log::info('카카오 알림톡 발송 API 호출 완료', [
                'phone' => $phone,
                'template_code' => $templateCode,
                'success' => $isSuccess,
                'status' => $response->status(),
                'response' => $responseBody,
                'request_data' => $data,
            ]);

            return [
                'success' => $isSuccess,
                'response_code' => $response->status(),
                'response_message' => $responseBody ?: ($isSuccess ? '발송 성공' : '발송 실패'),
            ];

        } catch (\Exception $e) {
            Log::error('카카오 알림톡 발송 API 호출 중 오류 발생', [
                'phone' => $phone,
                'template_code' => $templateCode,
                'request_data' => $data ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'response_code' => 'EXCEPTION',
                'response_message' => 'API 호출 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 전화번호 유효성 검증
     *
     * @param string $phone 전화번호
     * @return bool
     */
    private function validatePhone(string $phone): bool
    {
        // 01012345678 형식 (11자리 숫자)
        $phonePattern = '/^\d{3}\d{4}\d{4}$/';
        return preg_match($phonePattern, $phone) === 1;
    }

    /**
     * 전화번호 포맷팅 (하이픈 제거)
     *
     * @param string $phone 전화번호
     * @return string
     */
    private function formatPhone(string $phone): string
    {
        // 하이픈, 공백 제거
        return preg_replace('/[-\s]/', '', $phone);
    }
}

