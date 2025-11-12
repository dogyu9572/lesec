<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolSearchController extends Controller
{
    /**
     * 학교 검색 결과 반환
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'school_level' => ['nullable', 'in:elementary,middle,high'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $query = School::query()
            ->where('status', 'normal')
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->input('city')))
            ->when($request->filled('district'), fn ($q) => $q->where('district', $request->input('district')))
            ->when($request->filled('school_level'), fn ($q) => $q->where('school_level', $request->input('school_level')))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = trim($request->input('keyword'));
                $q->where('school_name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('school_name');

        $schools = $query->limit(50)->get([
            'id',
            'city',
            'district',
            'school_level',
            'school_name',
        ]);

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
            'data' => $schools->map(function (School $school) use ($levelLabels) {
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
                'count' => $schools->count(),
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


