<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\SchoolService;
use App\Services\Backoffice\NeisApiService;
use App\Http\Requests\Backoffice\SchoolStoreRequest;
use App\Http\Requests\Backoffice\SchoolUpdateRequest;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends BaseController
{
    protected $schoolService;
    protected $neisApiService;

    public function __construct(SchoolService $schoolService, NeisApiService $neisApiService)
    {
        $this->schoolService = $schoolService;
        $this->neisApiService = $neisApiService;
    }

    /**
     * 학교 목록
     */
    public function index(Request $request)
    {
        $schools = $this->schoolService->getFilteredSchools($request);
        $schoolLevels = $this->schoolService->getSchoolLevels();

        return $this->view('backoffice.schools.index', [
            'schools' => $schools,
            'schoolLevels' => $schoolLevels,
        ]);
    }

    /**
     * 학교 등록 폼
     */
    public function create()
    {
        $schoolLevels = $this->schoolService->getSchoolLevels();
        $cities = $this->schoolService->getCities();
        $districts = $this->schoolService->getDistricts();

        return $this->view('backoffice.schools.create', [
            'schoolLevels' => $schoolLevels,
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    /**
     * 학교 등록 처리
     */
    public function store(SchoolStoreRequest $request)
    {
        $this->schoolService->createSchool($request->validated());

        return redirect()->route('backoffice.schools.index')
            ->with('success', '학교가 등록되었습니다.');
    }

    /**
     * 학교 수정 폼
     */
    public function edit(School $school)
    {
        $schoolLevels = $this->schoolService->getSchoolLevels();
        $cities = $this->schoolService->getCities();
        $districts = $this->schoolService->getDistricts($school->city);

        return $this->view('backoffice.schools.edit', [
            'school' => $school,
            'schoolLevels' => $schoolLevels,
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    /**
     * 학교 수정 처리
     */
    public function update(SchoolUpdateRequest $request, School $school)
    {
        $this->schoolService->updateSchool($school, $request->validated());

        return redirect()->route('backoffice.schools.index')
            ->with('success', '학교 정보가 수정되었습니다.');
    }

    /**
     * 학교 삭제
     */
    public function destroy(School $school)
    {
        $this->schoolService->deleteSchool($school);

        return redirect()->route('backoffice.schools.index')
            ->with('success', '학교가 삭제되었습니다.');
    }

    /**
     * 학교 상세 정보 (API 등록된 학교 확인용)
     */
    public function show(School $school)
    {
        $schoolLevels = $this->schoolService->getSchoolLevels();

        return $this->view('backoffice.schools.show', [
            'school' => $school,
            'schoolLevels' => $schoolLevels,
        ]);
    }

    /**
     * API에서 학교 정보 동기화
     */
    public function syncFromApi(Request $request)
    {
        $apiKey = env('NEIS_API_KEY');

        if (empty($apiKey)) {
            return redirect()->back()
                ->with('error', 'NEIS API 키가 설정되지 않았습니다.');
        }

        $params = [
            'school_name' => $request->input('school_name'),
            'city_code' => $request->input('city_code'),
            'pIndex' => $request->input('pIndex', 1),
            'pSize' => $request->input('pSize', 100),
        ];

        $result = $this->neisApiService->syncSchoolsFromApi($params);

        if ($result['success']) {
            return redirect()->route('backoffice.schools.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * 학교 검색 (AJAX, 회원 정보 편집용)
     */
    public function search(Request $request)
    {
        try {
            $schools = $this->schoolService->searchSchools($request);

            return response()->json([
                'schools' => $schools->items(),
                'pagination' => [
                    'current_page' => $schools->currentPage(),
                    'last_page' => $schools->lastPage(),
                    'per_page' => $schools->perPage(),
                    'total' => $schools->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '학교 검색 중 오류가 발생했습니다: ' . $e->getMessage(),
                'schools' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ]
            ], 500);
        }
    }

    /**
     * 시/군/구 목록 조회 (AJAX)
     */
    public function getDistricts(Request $request)
    {
        $city = $request->input('city');
        $districts = $this->schoolService->getDistricts($city);

        return response()->json([
            'districts' => $districts,
        ]);
    }
}

