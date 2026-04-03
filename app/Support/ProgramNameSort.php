<?php

namespace App\Support;

/**
 * 예약 캘린더·명단 관리 공통 정렬
 * 1) 교육유형: 고등(학기·방학) → 중등(학기·방학) → 특별
 * 2) 프로그램명: 고등(P·H 번호순) → 중등(M 번호순, 동일 레벨은 오전 → 오후) → 기타
 */
final class ProgramNameSort
{
    /**
     * 교육유형 정렬 순서: 고등학기 → 고등방학 → 중등학기 → 중등방학 → 특별
     */
    public static function educationTypeSortOrder(string $educationType): int
    {
        return match ($educationType) {
            'high_semester' => 0,
            'high_vacation' => 1,
            'middle_semester' => 2,
            'middle_vacation' => 3,
            'special' => 4,
            default => 99,
        };
    }

    public static function compare(string $programNameA, string $programNameB): int
    {
        $tupleA = self::sortTuple($programNameA);
        $tupleB = self::sortTuple($programNameB);

        return $tupleA <=> $tupleB;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function sortTuple(string $programName): array
    {
        $name = trim($programName);

        if (preg_match('/^P\s*(\d+)/iu', $name, $m)) {
            return [0, (int) $m[1], 0];
        }

        if (preg_match('/^H\s*(\d+)/iu', $name, $m)) {
            return [0, (int) $m[1], 1];
        }

        if (preg_match('/^M\s*(\d+)/iu', $name, $m)) {
            $level = (int) $m[1];
            $slot = 0;
            if (mb_strpos($name, '오후') !== false) {
                $slot = 1;
            } elseif (mb_strpos($name, '오전') !== false) {
                $slot = 0;
            }

            return [1, $level, $slot];
        }

        return [2, 0, 0];
    }

    /**
     * 캘린더 우측 패널 등 program_reservation 행 배열 정렬 (교육유형 → 프로그램명)
     *
     * @param  array<int, array{program_name?: string, education_type?: string}>  $rows
     */
    public static function sortReservationRows(array &$rows): void
    {
        usort($rows, function (array $a, array $b) {
            $eduA = (string) ($a['education_type'] ?? '');
            $eduB = (string) ($b['education_type'] ?? '');
            $eduCmp = self::educationTypeSortOrder($eduA) <=> self::educationTypeSortOrder($eduB);
            if ($eduCmp !== 0) {
                return $eduCmp;
            }

            $nameA = (string) ($a['program_name'] ?? '');
            $nameB = (string) ($b['program_name'] ?? '');
            $nameCmp = self::compare($nameA, $nameB);
            if ($nameCmp !== 0) {
                return $nameCmp;
            }

            return strcmp($nameA, $nameB);
        });
    }
}
