<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * 동일 평문 → 동일 암호문 (DB 등호 비교·unique 검증용).
 * members.email, contact, parent_contact 등에 사용.
 */
class DeterministicEncryptedString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::decryptPayload((string) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return [$key => null];
        }

        $plain = self::normalizePlaintext($key, (string) $value);

        return [$key => self::encrypt($plain)];
    }

    public static function normalizePlaintext(string $attribute, string $plain): string
    {
        if ($attribute === 'email') {
            return strtolower(trim($plain));
        }

        if ($attribute === 'contact' || $attribute === 'parent_contact') {
            $digits = preg_replace('/[^0-9]/', '', $plain);

            return $digits !== '' ? $digits : $plain;
        }

        return $plain;
    }

    public static function encryptForQuery(string $attribute, ?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        return self::encrypt(self::normalizePlaintext($attribute, $plain));
    }

    public static function encrypt(string $plain): string
    {
        $key = substr(hash('sha256', config('app.key'), true), 0, 32);
        $iv = substr(hash('sha256', config('app.key') . '|det|pii|' . $plain, true), 0, 16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            return $plain;
        }

        return base64_encode($iv . $cipher);
    }

    public static function isEncryptedPayload(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $raw = base64_decode($value, true);
        if ($raw === false || strlen($raw) < 17) {
            return false;
        }

        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $key = substr(hash('sha256', config('app.key'), true), 0, 32);
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return $plain !== false;
    }

    public static function decryptPayload(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $raw = base64_decode($value, true);
        if ($raw !== false && strlen($raw) >= 17) {
            $iv = substr($raw, 0, 16);
            $cipher = substr($raw, 16);
            $key = substr(hash('sha256', config('app.key'), true), 0, 32);
            $plain = openssl_decrypt($cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            if ($plain !== false) {
                return $plain;
            }
        }

        try {
            $decrypted = Crypt::decrypt($value);

            return is_string($decrypted) ? $decrypted : (string) $decrypted;
        } catch (\Throwable) {
            // 마이그레이션 전 평문
            return $value;
        }
    }
}
