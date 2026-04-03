<?php

namespace App\Services\Backoffice;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SchoolService
{
    /**
     * 필터링된 학교 목록 조회
     */
    public function getFilteredSchools(Request $request): LengthAwarePaginator
    {
        $query = School::query()->orderBy('created_at', 'desc');

        // 구분 필터
        if ($request->filled('source_type')) {
            $query->bySourceType($request->source_type);
        }

        // 시/도 필터
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        // 시/군/구 필터
        if ($request->filled('district')) {
            $query->byDistrict($request->district);
        }

        // 학교급 필터
        if ($request->filled('school_level')) {
            $query->bySchoolLevel($request->school_level);
        }

        // 학교명 검색
        if ($request->filled('school_name')) {
            $query->where('school_name', 'like', "%{$request->school_name}%");
        }

        $perPage = $request->get('per_page', 20);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 학교급 목록 반환
     */
    public function getSchoolLevels(): array
    {
        return [
            'elementary' => '초등학교',
            'middle' => '중학교',
            'high' => '고등학교',
        ];
    }

    /**
     * 시/도 목록 조회 (등록된 학교에 쓰인 city 기준 + 없으면 맨 뒤에 검정고시·홈스쿨링·기타/해외)
     */
    public function getCities(): array
    {
        $cities = School::query()
            ->whereNotNull('city')
            ->distinct()
            ->orderByRaw("CASE WHEN city = '기타/해외' THEN 1 ELSE 0 END")
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->unique()
            ->values();

        foreach (['검정고시', '홈스쿨링', '기타/해외'] as $fixedCity) {
            if (!$cities->contains($fixedCity)) {
                $cities->push($fixedCity);
            }
        }

        return $cities->toArray();
    }

    /**
     * 시/군/구 목록 조회
     */
    public function getDistricts(?string $city = null): array
    {
        // 시·군·구 없이 직접 입력하는 특수 시/도 선택 시 빈 배열 반환
        if (in_array($city, ['기타/해외', '검정고시', '홈스쿨링'], true)) {
            return [];
        }

        $query = School::query()
            ->whereNotNull('district');

        if ($city) {
            $query->where('city', $city);
        }

        return $query
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * 학교 등록
     */
    public function createSchool(array $data): School
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return School::create($data);
    }

    /**
     * 학교 수정
     */
    public function updateSchool(School $school, array $data): bool
    {
        $data['updated_by'] = Auth::id();

        return $school->update($data);
    }

    /**
     * 학교 삭제
     */
    public function deleteSchool(School $school): bool
    {
        return $school->delete();
    }

    /**
     * 학교 검색 (회원 정보 편집용)
     */
    public function searchSchools(Request $request): LengthAwarePaginator
    {
        $query = School::query()
            ->select('id', 'school_name', 'city', 'district', 'school_level')
            ->orderByRaw("
                CASE 
                    WHEN school_name REGEXP '^[가-힣]' THEN 0
                    WHEN school_name REGEXP '^[0-9]' THEN 1
                    WHEN school_name REGEXP '^[A-Za-z]' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('school_name', 'asc');

        // 학교명 검색
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where('school_name', 'like', "%{$keyword}%");
        }

        // 시/도 필터
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // 시/군/구 필터
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        // 학교급 필터
        if ($request->filled('school_level')) {
            $query->where('school_level', $request->school_level);
        }

        $perPage = $request->get('per_page', 10);

        return $query->paginate($perPage)->withQueryString();
    }
}
