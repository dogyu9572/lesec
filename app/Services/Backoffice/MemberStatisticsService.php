<?php

namespace App\Services\Backoffice;

use App\Models\Member;

class MemberStatisticsService
{
    private const REGION_ORDER = [
        '강원', '경기', '경남', '경북', '기타/외국', '대구', '대전', '부산', '서울',
        '울산', '인천', '전남', '전북', '제주', '충남', '충북',
    ];

    private const REGION_NORMALIZE_MAP = [
        '강원' => '강원', '강원도' => '강원',
        '경기' => '경기', '경기도' => '경기',
        '경남' => '경남', '경상남도' => '경남',
        '경북' => '경북', '경상북도' => '경북',
        '대구' => '대구', '대구광역시' => '대구',
        '대전' => '대전', '대전광역시' => '대전',
        '부산' => '부산', '부산광역시' => '부산',
        '서울' => '서울', '서울특별시' => '서울',
        '울산' => '울산', '울산광역시' => '울산',
        '인천' => '인천', '인천광역시' => '인천',
        '전남' => '전남', '전라남도' => '전남',
        '전북' => '전북', '전라북도' => '전북',
        '제주' => '제주', '제주특별자치도' => '제주',
        '충남' => '충남', '충청남도' => '충남',
        '충북' => '충북', '충청북도' => '충북',
        '세종' => '기타/외국', '세종특별자치시' => '기타/외국',
        '광주' => '기타/외국', '광주광역시' => '기타/외국',
        '기타/외국' => '기타/외국',
    ];

    private const GRADE_LABELS = [
        1 => '초1',
        2 => '초2',
        3 => '초3',
        4 => '초4',
        5 => '초5',
        6 => '초6',
        7 => '중1',
        8 => '중2',
        9 => '중3',
        10 => '고1',
        11 => '고2',
        12 => '고3',
    ];

    private const SCHOOL_LEVELS = ['초등학교', '중학교', '고등학교', '기타'];

    public function getStatistics(): array
    {
        $totalMembers = Member::where('is_active', true)->count();
        $teacherCount = Member::where('is_active', true)
            ->where('member_type', 'teacher')
            ->count();
        $studentCount = Member::where('is_active', true)
            ->where('member_type', 'student')
            ->count();

        $regionStats = $this->buildRegionStatistics($totalMembers);
        $gradeStats = $this->buildGradeStatistics($studentCount);
        $schoolLevelStats = $this->buildSchoolLevelStatistics($totalMembers);

        return [
            'total_members' => $totalMembers,
            'student_ratio' => $this->formatPercentage($studentCount, $totalMembers),
            'teacher_ratio' => $this->formatPercentage($teacherCount, $totalMembers),
            'region_stats' => $regionStats,
            'grade_stats' => $gradeStats,
            'school_level_stats' => $schoolLevelStats,
        ];
    }

    private function buildRegionStatistics(int $totalMembers): array
    {
        $base = array_fill_keys(self::REGION_ORDER, 0);

        Member::select('city')
            ->selectRaw('COUNT(*) as total')
            ->where('is_active', true)
            ->groupBy('city')
            ->get()
            ->each(function ($row) use (&$base) {
                $label = $this->normalizeRegion($row->city);
                $base[$label] += $row->total;
            });

        return collect($base)->map(function ($count, $label) use ($totalMembers) {
            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $this->formatPercentage($count, $totalMembers),
            ];
        })->values()->all();
    }

    private function buildGradeStatistics(int $studentTotal): array
    {
        $base = array_fill_keys(array_values(self::GRADE_LABELS), 0);
        $base['기타'] = 0;

        Member::select('grade')
            ->selectRaw('COUNT(*) as total')
            ->where('is_active', true)
            ->where('member_type', 'student')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->get()
            ->each(function ($row) use (&$base) {
                $label = $this->gradeToLabel($row->grade);
                $base[$label] += $row->total;
            });

        $unknownCount = Member::where('is_active', true)
            ->where('member_type', 'student')
            ->whereNull('grade')
            ->count();
        $base['기타'] += $unknownCount;

        return collect($base)->map(function ($count, $label) use ($studentTotal) {
            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $this->formatPercentage($count, $studentTotal),
            ];
        })->values()->all();
    }

    private function buildSchoolLevelStatistics(int $totalMembers): array
    {
        $base = array_fill_keys(self::SCHOOL_LEVELS, 0);
        Member::query()
            ->leftJoin('schools', 'members.school_id', '=', 'schools.id')
            ->select('schools.school_level', 'members.school_name')
            ->where('members.is_active', true)
            ->get()
            ->each(function ($row) use (&$base) {
                $label = $this->resolveSchoolLevel($row->school_level, $row->school_name);
                $base[$label]++;
            });

        return collect($base)->map(function ($count, $label) use ($totalMembers) {
            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $this->formatPercentage($count, $totalMembers),
            ];
        })->values()->all();
    }

    private function normalizeRegion(?string $city): string
    {
        if (!$city) {
            return '기타/외국';
        }

        $city = trim($city);
        if (isset(self::REGION_NORMALIZE_MAP[$city])) {
            return self::REGION_NORMALIZE_MAP[$city];
        }

        foreach (self::REGION_NORMALIZE_MAP as $key => $value) {
            if ($key !== '기타/외국' && str_contains($city, $key)) {
                return $value;
            }
        }

        return '기타/외국';
    }

    private function gradeToLabel(?int $grade): string
    {
        return self::GRADE_LABELS[$grade] ?? '기타';
    }

    private function resolveSchoolLevel(?string $schoolLevel, ?string $schoolName): string
    {
        if ($schoolLevel) {
            return $this->schoolLevelToLabel($schoolLevel);
        }

        $inferred = $this->inferSchoolLevelFromName($schoolName);
        return $this->schoolLevelToLabel($inferred);
    }

    private function schoolLevelToLabel(?string $schoolLevel): string
    {
        return match ($schoolLevel) {
            'elementary' => '초등학교',
            'middle' => '중학교',
            'high' => '고등학교',
            default => '기타',
        };
    }

    private function inferSchoolLevelFromName(?string $schoolName): ?string
    {
        if (!$schoolName) {
            return null;
        }

        $name = trim($schoolName);

        if (str_contains($name, '초등')) {
            return 'elementary';
        }

        if (str_contains($name, '중학교')) {
            return 'middle';
        }

        if (str_contains($name, '고등학교') || str_contains($name, '고교')) {
            return 'high';
        }

        return null;
    }

    private function formatPercentage(int $count, int $total): string
    {
        if ($total === 0) {
            return '0.00%';
        }

        return number_format(($count / $total) * 100, 2) . '%';
    }
}

