<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\GroupProgramService;
use App\Models\ProgramReservation;
use App\Http\Requests\Backoffice\GroupProgramStoreRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupProgramController extends BaseController
{
    protected $groupProgramService;

    public function __construct(GroupProgramService $groupProgramService)
    {
        $this->groupProgramService = $groupProgramService;
    }

    /**
     * 단체 프로그램 목록
     */
    public function index(Request $request)
    {
        $programs = $this->groupProgramService->getFilteredPrograms($request);
        $educationTypes = $this->groupProgramService->getEducationTypes();
        $receptionTypes = $this->groupProgramService->getReceptionTypes();

        return $this->view('backoffice.group-programs.index', [
            'programs' => $programs,
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
        ]);
    }

    /**
     * 단체 프로그램 등록 폼
     */
    public function create()
    {
        $educationTypes = $this->groupProgramService->getEducationTypes();
        $receptionTypes = $this->groupProgramService->getReceptionTypes();
        $paymentMethods = $this->groupProgramService->getPaymentMethods();

        return $this->view('backoffice.group-programs.create', [
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * 단체 프로그램 등록 처리
     */
    public function store(GroupProgramStoreRequest $request)
    {
        $data = $request->validated();
        $educationType = $data['education_type'] ?? '';

        // 교육신청 불가 기간 포함 여부를 전체 기간으로 먼저 검증 (학기형 일괄 생성 시 저장 방지)
        $this->groupProgramService->validateEducationScheduleAvailability(
            $data['education_start_date'] ?? null,
            $data['education_end_date'] ?? null
        );

        // 중등학기 또는 고등학기인 경우, 교육 시작일부터 종료일까지 각 날짜별로 프로그램 생성
        if (in_array($educationType, ['middle_semester', 'high_semester'])) {
            $startDate = Carbon::parse($data['education_start_date']);
            $endDate = Carbon::parse($data['education_end_date']);
            $createdCount = 0;

            // 각 날짜별로 프로그램 생성
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayData = $data;
                $dayData['education_start_date'] = $date->format('Y-m-d');
                $dayData['education_end_date'] = $date->format('Y-m-d');

                $this->groupProgramService->createProgram($dayData);
                $createdCount++;
            }

            return redirect()->route('backoffice.group-programs.index')
                ->with('success', "단체 프로그램 {$createdCount}개가 등록되었습니다.");
        } else {
            // 특별프로그램이나 방학은 기존 로직 유지
            $this->groupProgramService->createProgram($data);

            return redirect()->route('backoffice.group-programs.index')
                ->with('success', '단체 프로그램이 등록되었습니다.');
        }
    }

    /**
     * 단체 프로그램 수정 폼
     */
    public function edit(ProgramReservation $programReservation)
    {
        $educationTypes = $this->groupProgramService->getEducationTypes();
        $receptionTypes = $this->groupProgramService->getReceptionTypes();
        $paymentMethods = $this->groupProgramService->getPaymentMethods();

        // 현재 신청인원 합계 계산
        $totalApplicantCount = \App\Models\GroupApplication::where('program_reservation_id', $programReservation->id)
            ->sum('applicant_count') ?? 0;

        return $this->view('backoffice.group-programs.edit', [
            'program' => $programReservation,
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
            'paymentMethods' => $paymentMethods,
            'totalApplicantCount' => $totalApplicantCount,
        ]);
    }

    /**
     * 단체 프로그램 수정 처리
     */
    public function update(Request $request, ProgramReservation $programReservation)
    {
        // 결제수단은 등록 화면과 동일하게 최소 1개 이상 필수
        $request->validate([
            'payment_methods' => ['required', 'array', 'min:1'],
            'payment_methods.*' => ['in:bank_transfer,on_site_card,online_card'],
        ], [
            'payment_methods.required' => '결제수단을 최소 1개 이상 선택해주세요.',
            'payment_methods.array' => '결제수단을 올바르게 선택해주세요.',
            'payment_methods.min' => '결제수단을 최소 1개 이상 선택해주세요.',
        ]);

        $this->groupProgramService->updateProgram($programReservation, $request->all());

        return redirect()->route('backoffice.group-programs.edit', $programReservation)
            ->with('success', '단체 프로그램이 수정되었습니다.');
    }

    /**
     * 단체 프로그램 삭제
     */
    public function destroy(ProgramReservation $programReservation)
    {
        $this->groupProgramService->deleteProgram($programReservation);

        return redirect()->route('backoffice.group-programs.index')
            ->with('success', '단체 프로그램이 삭제되었습니다.');
    }

    /**
     * 단체 프로그램 일괄 삭제
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'program_ids' => 'required|array',
            'program_ids.*' => 'integer|exists:program_reservations,id',
        ]);

        $programIds = $request->input('program_ids');

        try {
            $deletedCount = $this->groupProgramService->bulkDelete($programIds);

            return response()->json([
                'success' => true,
                'message' => "선택한 {$deletedCount}건이 삭제되었습니다.",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 프로그램명 검색 (모달용)
     */
    public function searchPrograms(Request $request)
    {
        try {
            $programs = $this->groupProgramService->searchPrograms($request);

            return response()->json([
                'programs' => $programs->items(),
                'pagination' => [
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage(),
                    'per_page' => $programs->perPage(),
                    'total' => $programs->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로그램 검색 중 오류가 발생했습니다: ' . $e->getMessage(),
                'programs' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ]
            ], 500);
        }
    }
}
