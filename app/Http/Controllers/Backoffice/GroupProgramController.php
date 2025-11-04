<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\GroupProgramService;
use App\Models\ProgramReservation;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        // TODO: Form Request로 유효성 검사 추가 예정
        $this->groupProgramService->createProgram($request->all());

        return redirect()->route('backoffice.group-programs.index')
            ->with('success', '단체 프로그램이 등록되었습니다.');
    }

    /**
     * 단체 프로그램 수정 폼
     */
    public function edit(ProgramReservation $programReservation)
    {
        $educationTypes = $this->groupProgramService->getEducationTypes();
        $receptionTypes = $this->groupProgramService->getReceptionTypes();
        $paymentMethods = $this->groupProgramService->getPaymentMethods();

        return $this->view('backoffice.group-programs.edit', [
            'program' => $programReservation,
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * 단체 프로그램 수정 처리
     */
    public function update(Request $request, ProgramReservation $programReservation)
    {
        // TODO: Form Request로 유효성 검사 추가 예정
        $this->groupProgramService->updateProgram($programReservation, $request->all());

        return redirect()->route('backoffice.group-programs.index')
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
