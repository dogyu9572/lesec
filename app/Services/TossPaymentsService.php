<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TossPaymentsService
{
    private const CONFIRM_URL = 'https://api.tosspayments.com/v1/payments/confirm';

    public function generateOrderId(): string
    {
        $suffix = Str::lower(Str::replace('-', '', Str::uuid()->toString()));

        return 'lesec_indiv_' . $suffix;
    }

    /**
     * 결제 승인 API 호출
     *
     * @param  string  $paymentKey  토스 리다이렉트 쿼리 paymentKey
     * @param  string  $orderId     주문번호
     * @param  int  $amount       결제 금액(원)
     * @return array Payment 객체 (연관 배열)
     *
     * @throws \RuntimeException API 오류 시
     */
    public function confirmPayment(string $paymentKey, string $orderId, int $amount): array
    {
        $secretKey = config('services.toss.secret_key');
        if (empty($secretKey)) {
            throw new \RuntimeException('토스페이먼츠 시크릿 키가 설정되지 않았습니다.');
        }

        $response = Http::timeout(15)
            ->withBasicAuth($secretKey, '')
            ->asJson()
            ->post(self::CONFIRM_URL, [
                'paymentKey' => $paymentKey,
                'orderId' => $orderId,
                'amount' => $amount,
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $message = $body['message'] ?? '결제 승인에 실패했습니다.';
            throw new \RuntimeException($message);
        }

        return $response->json();
    }

    public function getClientKey(): ?string
    {
        return config('services.toss.client_key');
    }
}
