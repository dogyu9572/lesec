<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\ProgramUpdateRequest;
use App\Services\Backoffice\ProgramService;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends BaseController
{
    protected $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }

    /**
     * 프로그램 정보 관리 페이지 (타입별 + 신청유형별)
     */
    public function index(Request $request, ?string $type = null)
    {
        $tabKey = $request->get('tab') ?? $type;

        // 탭 키 파싱
        $parsed = $this->programService->parseTabKey($tabKey ?? '');
        $selectedType = $parsed['type'] ?? 'middle_semester';
        $selectedApplicationType = $parsed['application_type'] ?? 'individual';

        // 유효한 타입인지 확인
        $types = $this->programService->getAllTypes();
        if (!isset($types[$selectedType])) {
            $selectedType = 'middle_semester';
            $selectedApplicationType = 'individual';
        }

        // 모든 탭 목록 가져오기
        $tabs = $this->programService->getAllTabs();
        $currentTabKey = $selectedType . '_' . $selectedApplicationType;

        // 타입별 프로그램 조회 (없으면 생성)
        $program = $this->programService->getOrCreateProgram($selectedType, $selectedApplicationType);

        return $this->view('backoffice.programs.index', [
            'program' => $program,
            'currentType' => $selectedType,
            'currentApplicationType' => $selectedApplicationType,
            'currentTabKey' => $currentTabKey,
            'tabs' => $tabs,
            'types' => $types,
        ]);
    }

    /**
     * 프로그램 정보 업데이트
     */
    public function update(ProgramUpdateRequest $request, Program $program)
    {
        $data = $request->validated();

        $this->programService->updateProgram($program, $data);

        return redirect()->route('backoffice.programs.index', ['tab' => $program->type . '_' . $program->application_type])
            ->with('success', '프로그램 정보가 성공적으로 저장되었습니다.');
    }
}
