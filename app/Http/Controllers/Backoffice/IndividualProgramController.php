<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\IndividualProgramService;
use App\Models\ProgramReservation;
use Illuminate\Http\Request;

class IndividualProgramController extends BaseController
{
    protected $individualProgramService;

    public function __construct(IndividualProgramService $individualProgramService)
    {
        $this->individualProgramService = $individualProgramService;
    }

    /**
     * 개인 프로그램 목록
     */
    public function index(Request $request)
    {
        $programs = $this->individualProgramService->getFilteredPrograms($request);
        $educationTypes = $this->individualProgramService->getEducationTypes();
        $receptionTypes = $this->individualProgramService->getReceptionTypes();

        return $this->view('backoffice.individual-programs.index', [
            'programs' => $programs,
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
        ]);
    }

    /**
     * 개인 프로그램 등록 폼
     */
    public function create()
    {
        $educationTypes = $this->individualProgramService->getEducationTypes();
        $receptionTypes = $this->individualProgramService->getReceptionTypes();
        $paymentMethods = $this->individualProgramService->getPaymentMethods();

        return $this->view('backoffice.individual-programs.create', [
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * 개인 프로그램 등록 처리
     */
    public function store(Request $request)
    {
        // TODO: Form Request로 유효성 검사 추가 예정
        $this->individualProgramService->createProgram($request->all());

        return redirect()->route('backoffice.individual-programs.index')
            ->with('success', '개인 프로그램이 등록되었습니다.');
    }

    /**
     * 개인 프로그램 수정 폼
     */
    public function edit(ProgramReservation $programReservation)
    {
        $educationTypes = $this->individualProgramService->getEducationTypes();
        $receptionTypes = $this->individualProgramService->getReceptionTypes();
        $paymentMethods = $this->individualProgramService->getPaymentMethods();

        return $this->view('backoffice.individual-programs.edit', [
            'program' => $programReservation,
            'educationTypes' => $educationTypes,
            'receptionTypes' => $receptionTypes,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * 개인 프로그램 수정 처리
     */
    public function update(Request $request, ProgramReservation $programReservation)
    {
        // TODO: Form Request로 유효성 검사 추가 예정
        $this->individualProgramService->updateProgram($programReservation, $request->all());

        return redirect()->route('backoffice.individual-programs.index')
            ->with('success', '개인 프로그램이 수정되었습니다.');
    }

    /**
     * 개인 프로그램 삭제
     */
    public function destroy(ProgramReservation $programReservation)
    {
        $this->individualProgramService->deleteProgram($programReservation);

        return redirect()->route('backoffice.individual-programs.index')
            ->with('success', '개인 프로그램이 삭제되었습니다.');
    }

    /**
     * 프로그램 검색 팝업 페이지
     */
    public function popupProgramSearch(Request $request)
    {
        $searchAction = $request->get('search_action', '');
        $mode = $request->get('mode', 'reservation');
        $educationTypes = $this->individualProgramService->getEducationTypes();
        
        return $this->view('backoffice.popups.program-search', [
            'searchAction' => $searchAction,
            'mode' => $mode,
            'educationTypes' => $educationTypes,
            'modalTitle' => '프로그램명 검색',
            'showDirectInputNotice' => $mode === 'name',
            'confirmButtonLabel' => '확인',
            'programInputId' => 'program_name',
            'showFooter' => $mode === 'reservation',
        ]);
    }

    /**
     * 프로그램명 검색 (모달용)
     */
    public function searchPrograms(Request $request)
    {
        try {
            $programs = $this->individualProgramService->searchPrograms($request);
            
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

