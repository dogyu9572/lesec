<?php

namespace App\Services\Member;

use App\Models\PhoneVerification;
use App\Services\Backoffice\SmsKakaoApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PhoneVerificationService
{
    private const CODE_LENGTH = 6;
    private const CODE_EXPIRY_MINUTES = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly SmsKakaoApiService $smsKakaoApiService
    ) {
    }

    /**
     * 인증번호 생성 및 발송
     */
    public function sendVerificationCode(string $phone, string $purpose, string $sessionId): array
    {
        $phone = $this->normalizePhone($phone);

        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'message' => '올바른 휴대폰 번호 형식이 아닙니다.',
            ];
        }

        // 재발송 가능 여부 확인
        if (!$this->canResend($phone, $sessionId)) {
            return [
                'success' => false,
                'message' => '1분 이내에 재발송할 수 없습니다. 잠시 후 다시 시도해주세요.',
            ];
        }

        // 기존 활성 인증번호 만료 처리
        PhoneVerification::where('phone', $phone)
            ->where('session_id', $sessionId)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['verified_at' => now()]);

        // 새 인증번호 생성
        $code = $this->generateCode();
        $expiresAt = Carbon::now()->addMinutes(self::CODE_EXPIRY_MINUTES);

        $verification = PhoneVerification::create([
            'phone' => $phone,
            'verification_code' => $code,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'purpose' => $purpose,
            'session_id' => $sessionId,
        ]);

        // SMS 발송
        $message = "[생명·환경과학교육센터] 인증번호는 {$code}입니다. 5분간 유효합니다.";
        $smsResult = $this->smsKakaoApiService->sendSms($phone, $message);

        if (!$smsResult['success']) {
            Log::error('SMS 인증번호 발송 실패', [
                'phone' => $phone,
                'purpose' => $purpose,
                'session_id' => $sessionId,
                'error' => $smsResult['response_message'],
            ]);

            return [
                'success' => false,
                'message' => '인증번호 발송에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ];
        }

        return [
            'success' => true,
            'message' => '인증번호가 발송되었습니다.',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 인증번호 검증
     */
    public function verifyCode(string $phone, string $code, string $purpose, string $sessionId): array
    {
        $phone = $this->normalizePhone($phone);

        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'message' => '올바른 휴대폰 번호 형식이 아닙니다.',
            ];
        }

        // 활성 인증번호 조회
        $verification = PhoneVerification::where('phone', $phone)
            ->where('session_id', $sessionId)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (!$verification) {
            return [
                'success' => false,
                'message' => '유효한 인증번호가 없습니다. 인증번호를 다시 발송해주세요.',
            ];
        }

        // 시도 횟수 확인
        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            return [
                'success' => false,
                'message' => '인증 시도 횟수를 초과했습니다. 인증번호를 다시 발송해주세요.',
            ];
        }

        // 시도 횟수 증가
        $verification->increment('attempts');

        // 인증번호 비교
        if ($verification->verification_code !== $code) {
            $remainingAttempts = self::MAX_ATTEMPTS - $verification->attempts;
            return [
                'success' => false,
                'message' => "인증번호가 일치하지 않습니다. (남은 시도 횟수: {$remainingAttempts}회)",
            ];
        }

        // 인증 완료 처리
        $verification->update([
            'verified_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => '인증이 완료되었습니다.',
        ];
    }

    /**
     * 재발송 가능 여부 확인
     */
    public function canResend(string $phone, string $sessionId): bool
    {
        $phone = $this->normalizePhone($phone);

        $recentVerification = PhoneVerification::where('phone', $phone)
            ->where('session_id', $sessionId)
            ->where('created_at', '>', Carbon::now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))
            ->exists();

        return !$recentVerification;
    }

    /**
     * 6자리 랜덤 숫자 생성
     */
    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * 휴대폰 번호 정규화 (하이픈 제거)
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[-\s]/', '', $phone);
    }

    /**
     * 휴대폰 번호 유효성 검증
     */
    private function validatePhone(string $phone): bool
    {
        // 01012345678 형식 (11자리 숫자)
        return preg_match('/^\d{11}$/', $phone) === 1;
    }

    /**
     * 만료된 인증번호 정리
     */
    public function cleanupExpired(): int
    {
        return PhoneVerification::expired()
            ->where('created_at', '<', Carbon::now()->subDays(1))
            ->delete();
    }
}
