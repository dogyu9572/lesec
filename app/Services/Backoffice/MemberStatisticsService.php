<?php

namespace App\Services\Backoffice;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MemberStatisticsService
{
    private const REGION_ORDER = [
        '강원', '경기', '경남', '경북', '기타/해외', '대구', '대전', '부산', '서울',
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
        '세종' => '기타/해외', '세종특별자치시' => '기타/해외',
        '광주' => '기타/해외', '광주광역시' => '기타/해외',
        '기타/외국' => '기타/해외',
        '기타/해외' => '기타/해외',
        '외국학교' => '기타/해외',
    ];

    private const GRADE_LABELS = [
        '초1', '초2', '초3', '초4', '초5', '초6',
        '중1', '중2', '중3',
        '고1', '고2', '고3',
        '기타',
    ];

    private const SCHOOL_LEVELS = ['초등학교', '중학교', '고등학교', '기타'];

    public function getStatistics(Request $request): array
    {
        $baseQuery = $this->buildMemberFilterQuery($request);

        $totalMembers = (clone $baseQuery)->count();
        $teacherCount = (clone $baseQuery)->where('member_type', 'teacher')->count();
        $studentCount = (clone $baseQuery)->where('member_type', 'student')->count();

        $regionStats = $this->buildRegionStatistics($baseQuery, $totalMembers);
        $gradeStats = $this->buildGradeStatistics($baseQuery, $studentCount);
        $schoolLevelStats = $this->buildSchoolLevelStatistics($baseQuery, $totalMembers);

        return [
            'total_members' => $totalMembers,
            'student_ratio' => $this->formatPercentage($studentCount, $totalMembers),
            'teacher_ratio' => $this->formatPercentage($teacherCount, $totalMembers),
            'region_stats' => $regionStats,
            'grade_stats' => $gradeStats,
            'school_level_stats' => $schoolLevelStats,
        ];
    }

    private function buildRegionStatistics(Builder $baseQuery, int $totalMembers): array
    {
        $base = array_fill_keys(self::REGION_ORDER, 0);

        (clone $baseQuery)
            ->select('city')
            ->selectRaw('COUNT(*) as total')
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

    private function buildGradeStatistics(Builder $baseQuery, int $studentTotal): array
    {
        $base = array_fill_keys(self::GRADE_LABELS, 0);

        (clone $baseQuery)
            ->leftJoin('schools', 'members.school_id', '=', 'schools.id')
            ->select('members.grade', 'schools.school_level', 'members.school_name')
            ->selectRaw('COUNT(*) as total')
            ->where('members.member_type', 'student')
            ->groupBy('members.grade', 'schools.school_level', 'members.school_name')
            ->get()
            ->each(function ($row) use (&$base) {
                $label = $this->resolveGradeLabelBySchoolLevel($row->grade, $row->school_level, $row->school_name);
                $base[$label] += $row->total;
            });

        return collect($base)->map(function ($count, $label) use ($studentTotal) {
            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $this->formatPercentage($count, $studentTotal),
            ];
        })->values()->all();
    }

    private function buildSchoolLevelStatistics(Builder $baseQuery, int $totalMembers): array
    {
        $base = array_fill_keys(self::SCHOOL_LEVELS, 0);
        (clone $baseQuery)
            ->leftJoin('schools', 'members.school_id', '=', 'schools.id')
            ->select('schools.school_level', 'members.school_name')
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
            return '기타/해외';
        }

        $city = trim($city);
        if (isset(self::REGION_NORMALIZE_MAP[$city])) {
            return self::REGION_NORMALIZE_MAP[$city];
        }

        foreach (self::REGION_NORMALIZE_MAP as $key => $value) {
            if ($key !== '기타/해외' && str_contains($city, $key)) {
                return $value;
            }
        }

        return '기타/해외';
    }

    private function buildMemberFilterQuery(Request $request): Builder
    {
        $query = Member::query();

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('member_group_id')) {
            $query->where('member_group_id', $request->member_group_id);
        }

        if ($request->filled('city')) {
            $city = trim((string) $request->city);
            if ($city === '기타/해외') {
                $query->where(function ($q) {
                    $q->whereNull('city')
                        ->orWhere('city', '')
                        ->orWhere('city', '기타/해외')
                        ->orWhere('city', '기타/외국')
                        ->orWhere('city', '외국학교');
                });
            } else {
                $query->where('city', $city);
            }
        }

        if ($request->filled('joined_from')) {
            $query->whereDate('joined_at', '>=', $request->joined_from);
        }
        if ($request->filled('joined_to')) {
            $query->whereDate('joined_at', '<=', $request->joined_to);
        }

        if ($request->filled('email_consent')) {
            $query->where('email_consent', $request->email_consent);
        }
        if ($request->filled('sms_consent')) {
            $query->where('sms_consent', $request->sms_consent);
        }
        if ($request->filled('kakao_consent')) {
            $query->where('kakao_consent', $request->kakao_consent);
        }

        if ($request->filled('search_keyword')) {
            $searchType = $request->get('search_type', 'all');
            $searchKeyword = trim((string) $request->search_keyword);
            $normalizedKeyword = str_replace('-', '', $searchKeyword);

            if ($searchType === 'all') {
                $query->where(function ($q) use ($searchKeyword, $normalizedKeyword) {
                    $q->where('login_id', 'like', "%{$searchKeyword}%")
                        ->orWhere('name', 'like', "%{$searchKeyword}%")
                        ->orWhere('school_name', 'like', "%{$searchKeyword}%")
                        ->orWhere('email', 'like', "%{$searchKeyword}%")
                        ->orWhere(function ($contactQ) use ($normalizedKeyword) {
                            Member::applyWhereDeterministicFieldMatches($contactQ, 'contact', $normalizedKeyword);
                            $contactQ->orWhere('contact', 'like', "%{$normalizedKeyword}%");
                        })
                        ->orWhere('city', 'like', "%{$searchKeyword}%")
                        ->orWhere('grade', 'like', "%{$searchKeyword}%");

                    if ($searchKeyword === '기타/해외' || $searchKeyword === '기타') {
                        $q->orWhereNull('city')
                            ->orWhere('city', '')
                            ->orWhere('city', '기타/해외')
                            ->orWhere('city', '기타/외국')
                            ->orWhere('city', '외국학교');
                    }
                });
            } else {
                if ($searchType === 'contact') {
                    $query->where(function ($contactQ) use ($normalizedKeyword) {
                        Member::applyWhereDeterministicFieldMatches($contactQ, 'contact', $normalizedKeyword);
                        $contactQ->orWhere('contact', 'like', "%{$normalizedKeyword}%");
                    });
                } elseif ($searchType === 'grade') {
                    if (is_numeric($searchKeyword)) {
                        $query->where('grade', (int) $searchKeyword);
                    }
                } elseif ($searchType === 'city') {
                    if ($searchKeyword === '기타/해외' || $searchKeyword === '기타') {
                        $query->where(function ($q) {
                            $q->whereNull('city')
                                ->orWhere('city', '')
                                ->orWhere('city', '기타/해외')
                                ->orWhere('city', '기타/외국')
                                ->orWhere('city', '외국학교');
                        });
                    } else {
                        $query->where('city', 'like', "%{$searchKeyword}%");
                    }
                } else {
                    $query->where($searchType, 'like', "%{$searchKeyword}%");
                }
            }
        }

        return $query;
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

    private function resolveGradeLabelBySchoolLevel(?int $grade, ?string $schoolLevel, ?string $schoolName): string
    {
        if ($grade === null) {
            return '기타';
        }

        $resolvedSchoolLevel = $schoolLevel ?? $this->inferSchoolLevelFromName($schoolName);

        return match ($resolvedSchoolLevel) {
            'elementary' => in_array($grade, [1, 2, 3, 4, 5, 6], true) ? '초' . $grade : '기타',
            'middle' => in_array($grade, [1, 2, 3], true) ? '중' . $grade : '기타',
            'high' => in_array($grade, [1, 2, 3], true) ? '고' . $grade : '기타',
            default => '기타',
        };
    }

    private function formatPercentage(int $count, int $total): string
    {
        if ($total === 0) {
            return '0.00%';
        }

        return number_format(($count / $total) * 100, 2) . '%';
    }
}

