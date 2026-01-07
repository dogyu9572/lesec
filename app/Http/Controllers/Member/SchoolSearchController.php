<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\SchoolSearchRequest;
use App\Models\School;
use App\Services\Member\SchoolSearchService;
use Illuminate\Http\JsonResponse;

class SchoolSearchController extends Controller
{
    public function __construct(private readonly SchoolSearchService $schoolSearchService)
    {
    }
    /**
     * 학교 검색 결과 반환
     */
    public function search(SchoolSearchRequest $request): JsonResponse
    {
        $schools = $this->schoolSearchService->search($request->validated());

        $cities = School::query()
            ->where('status', 'normal')
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->values();

        $districtsQuery = School::query()
            ->where('status', 'normal')
            ->whereNotNull('district');

        if ($request->filled('city')) {
            $districtsQuery->where('city', $request->input('city'));
        }

        $districts = $districtsQuery
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->filter()
            ->values();

        $levelLabels = [
            'elementary' => '초등학교',
            'middle' => '중학교',
            'high' => '고등학교',
        ];

        return response()->json([
            'data' => $schools->getCollection()->map(function (School $school) use ($levelLabels) {
                return [
                    'id' => $school->id,
                    'city' => $school->city,
                    'district' => $school->district,
                    'name' => $school->school_name,
                    'level' => $school->school_level,
                    'level_label' => $levelLabels[$school->school_level] ?? null,
                ];
            }),
            'meta' => [
                'count' => $schools->total(),
                'current_page' => $schools->currentPage(),
                'last_page' => $schools->lastPage(),
                'per_page' => $schools->perPage(),
            ],
            'filters' => [
                'cities' => $cities,
                'districts' => $districts,
                'levels' => collect($levelLabels)->map(function ($label, $value) {
                    return [
                        'value' => $value,
                        'label' => $label,
                    ];
                })->values(),
            ],
        ]);
    }
}


