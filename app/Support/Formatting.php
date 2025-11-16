<?php

namespace App\Support;

use Carbon\Carbon;

class Formatting
{
    public static function formatPhone(?string $digits): ?string
    {
        if (empty($digits)) {
            return null;
        }
        $only = preg_replace('/[^0-9]/', '', $digits);
        if (strlen($only) === 11) {
            return preg_replace('/(\\d{3})(\\d{4})(\\d{4})/', '$1-$2-$3', $only);
        }
        if (strlen($only) === 10) {
            return preg_replace('/(\\d{3})(\\d{3})(\\d{4})/', '$1-$2-$3', $only);
        }
        return $digits;
    }

    public static function parseYmdToDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        $only = preg_replace('/[^0-9]/', '', $value);
        if (strlen($only) !== 8) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Ymd', $only)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}


