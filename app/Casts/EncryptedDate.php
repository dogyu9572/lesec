<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * 생년월일: DB에는 Laravel encrypt(Y-m-d), 앱에서는 Carbon|null 로 사용
 */
class EncryptedDate implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        try {
            $decrypted = Crypt::decrypt($value);
            if (! is_string($decrypted)) {
                $decrypted = (string) $decrypted;
            }

            return Carbon::parse($decrypted)->startOfDay();
        } catch (\Throwable) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return [$key => null];
        }

        if ($value instanceof Carbon) {
            $plain = $value->format('Y-m-d');
        } else {
            $s = (string) $value;
            if (preg_match('/^\d{8}$/', $s)) {
                $plain = Carbon::createFromFormat('Ymd', $s)->format('Y-m-d');
            } else {
                $plain = Carbon::parse($s)->format('Y-m-d');
            }
        }

        return [$key => Crypt::encrypt($plain)];
    }
}
