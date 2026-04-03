<?php

namespace App\Console\Commands;

use App\Casts\DeterministicEncryptedString;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * 기존 평문 PII를 암호화 저장 형식으로 일괄 변환합니다.
 * 배포 순서: (1) 컬럼 타입 마이그레이션 (2) 이 커맨드 (3) encrypted / 결정적 캐스트가 포함된 앱 배포 권장
 *
 * 주의: Eloquent `encrypted` 캐스트는 encrypt($v, false)와 동일(serialize 없이 암호화)합니다.
 *       `encrypt($v)` 단독 호출은 PHP serialize가 끼어 화면에 s:N:"..." 형태로 노출될 수 있습니다.
 */
class EncryptExistingPiiDataCommand extends Command
{
    protected $signature = 'pii:encrypt-existing {--dry-run : DB 반영 없이 건수만 출력}';

    protected $description = 'members / group_applications / individual_applications PII 평문을 암호화 저장 형식으로 변환';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY-RUN] DB를 수정하지 않습니다.');
        }

        $this->encryptMembers($dryRun);
        $this->encryptGroupApplications($dryRun);
        $this->encryptIndividualApplications($dryRun);

        $this->info('완료.');

        return self::SUCCESS;
    }

    /**
     * Laravel 암호문인지 (decryptString 또는 구형 serialize 포함 decrypt)
     */
    private function isLaravelEncryptedPayload(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            try {
                Crypt::decrypt($value);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }
    }

    /**
     * Eloquent `encrypted` 캐스트와 동일하게 저장하려면 DB 값이 평문이거나,
     * decryptString 결과가 이미 평문이어야 합니다. decryptString 결과가 s:N:" 형태면 레거시(잘못된 encrypt())이므로 정규화 필요.
     */
    private function eloquentEncryptedAttributeNeedsNormalize(string $raw): bool
    {
        if ($raw === '') {
            return false;
        }

        if (! $this->isLaravelEncryptedPayload($raw)) {
            return true;
        }

        try {
            $inner = Crypt::decryptString($raw);
        } catch (\Throwable) {
            return true;
        }

        return is_string($inner) && preg_match('/^s:\d+:"/u', $inner);
    }

    /**
     * 평문 추출: 평문 행 | Laravel 암호문(decryptString 후 PHP 직렬 문자열이면 한 번 풀기)
     */
    private function plainTextForEloquentEncryptedAttribute(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        if (! $this->isLaravelEncryptedPayload($raw)) {
            return $raw;
        }

        try {
            $inner = Crypt::decryptString($raw);
        } catch (\Throwable) {
            try {
                return $this->stringFromDecrypt($raw);
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_string($inner) && preg_match('/^s:\d+:"/u', $inner)) {
            $un = @unserialize($inner);
            if (is_string($un)) {
                return $un;
            }
        }

        return is_string($inner) ? $inner : null;
    }

    private function stringFromDecrypt(string $payload): ?string
    {
        $decrypted = Crypt::decrypt($payload);

        if (is_string($decrypted)) {
            return $decrypted;
        }

        if (is_scalar($decrypted)) {
            return (string) $decrypted;
        }

        return null;
    }

    private function storeForEloquentEncryptedCast(?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        return encrypt($plain, false);
    }

    /**
     * 결정적 컬럼: 이미 결정적 암호문이면 스킵. Laravel encrypt(과거 오동작)·평문이면 평문으로 재암호화
     */
    private function resolvePlaintextForDeterministicColumn(string $column, string $value): ?string
    {
        if (DeterministicEncryptedString::isEncryptedPayload($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decrypt($value);

            return is_string($decrypted) ? $decrypted : (string) $decrypted;
        } catch (\Throwable) {
            return $value;
        }
    }

    private function encryptMembers(bool $dryRun): void
    {
        $table = 'members';
        $cols = ['email', 'birth_date', 'contact', 'parent_contact', 'school_name'];

        $count = 0;
        DB::table($table)->orderBy('id')->chunk(200, function ($rows) use ($table, $cols, $dryRun, &$count) {
            foreach ($rows as $row) {
                $updates = [];
                foreach ($cols as $col) {
                    $v = $row->{$col} ?? null;
                    if ($v === null || $v === '') {
                        continue;
                    }
                    $v = (string) $v;

                    if (in_array($col, ['email', 'contact', 'parent_contact'], true)) {
                        $plain = $this->resolvePlaintextForDeterministicColumn($col, $v);
                        if ($plain === null) {
                            continue;
                        }
                        $updates[$col] = DeterministicEncryptedString::encryptForQuery($col, $plain);

                        continue;
                    }

                    if ($col === 'birth_date') {
                        if ($this->isLaravelEncryptedPayload($v)) {
                            continue;
                        }
                        try {
                            $plain = Carbon::parse($v)->format('Y-m-d');
                        } catch (\Throwable) {
                            $this->warn("members id={$row->id} birth_date 파싱 스킵: {$v}");

                            continue;
                        }
                        $updates[$col] = Crypt::encrypt($plain);

                        continue;
                    }

                    if ($col === 'school_name') {
                        if (! $this->eloquentEncryptedAttributeNeedsNormalize($v)) {
                            continue;
                        }
                        $plain = $this->plainTextForEloquentEncryptedAttribute($v);
                        $stored = $this->storeForEloquentEncryptedCast($plain);
                        if ($stored === null) {
                            continue;
                        }
                        $updates[$col] = $stored;
                    }
                }
                if ($updates === []) {
                    continue;
                }
                $count++;
                if (! $dryRun) {
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            }
        });

        $this->line("members: {$count}건 " . ($dryRun ? '(갱신 예정)' : '갱신'));
    }

    private function encryptGroupApplications(bool $dryRun): void
    {
        $table = 'group_applications';
        $cols = ['applicant_contact', 'school_name'];

        $count = 0;
        DB::table($table)->orderBy('id')->chunk(200, function ($rows) use ($table, $cols, $dryRun, &$count) {
            foreach ($rows as $row) {
                $updates = [];
                foreach ($cols as $col) {
                    $v = $row->{$col} ?? null;
                    if ($v === null || $v === '') {
                        continue;
                    }
                    $v = (string) $v;
                    if (! $this->eloquentEncryptedAttributeNeedsNormalize($v)) {
                        continue;
                    }
                    $plain = $this->plainTextForEloquentEncryptedAttribute($v);
                    $stored = $this->storeForEloquentEncryptedCast($plain);
                    if ($stored === null) {
                        continue;
                    }
                    $updates[$col] = $stored;
                }
                if ($updates === []) {
                    continue;
                }
                $count++;
                if (! $dryRun) {
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            }
        });

        $this->line("group_applications: {$count}건 " . ($dryRun ? '(갱신 예정)' : '갱신'));
    }

    private function encryptIndividualApplications(bool $dryRun): void
    {
        $table = 'individual_applications';
        $cols = ['applicant_school_name', 'applicant_contact', 'guardian_contact'];

        $count = 0;
        DB::table($table)->orderBy('id')->chunk(200, function ($rows) use ($table, $cols, $dryRun, &$count) {
            foreach ($rows as $row) {
                $updates = [];
                foreach ($cols as $col) {
                    $v = $row->{$col} ?? null;
                    if ($v === null || $v === '') {
                        continue;
                    }
                    $v = (string) $v;
                    if (! $this->eloquentEncryptedAttributeNeedsNormalize($v)) {
                        continue;
                    }
                    $plain = $this->plainTextForEloquentEncryptedAttribute($v);
                    $stored = $this->storeForEloquentEncryptedCast($plain);
                    if ($stored === null) {
                        continue;
                    }
                    $updates[$col] = $stored;
                }
                if ($updates === []) {
                    continue;
                }
                $count++;
                if (! $dryRun) {
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            }
        });

        $this->line("individual_applications: {$count}건 " . ($dryRun ? '(갱신 예정)' : '갱신'));
    }
}
