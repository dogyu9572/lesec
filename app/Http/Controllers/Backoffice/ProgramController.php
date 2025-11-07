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
     * 프로그램 정보 관리 페이지 (타입별)
     */
    public function index(Request $request, ?string $type = null)
    {
        $requestedType = $type ?? $request->get('type');
        
        // 유효한 타입인지 확인
        $types = $this->programService->getAllTypes();
        $defaultType = array_key_first($types) ?? 'middle_semester';
        $selectedType = $requestedType ?: $defaultType;
        if (!isset($types[$selectedType])) {
            $types[$selectedType] = $this->programService->getTypeName($selectedType);
        }
        
        // 타입별 프로그램 조회 (없으면 생성)
        $program = $this->programService->getOrCreateProgram($selectedType);
        
        return $this->view('backoffice.programs.index', [
            'program' => $program,
            'currentType' => $selectedType,
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

        return redirect()->route('backoffice.programs.index', ['type' => $program->type])
                        ->with('success', '프로그램 정보가 성공적으로 저장되었습니다.');
    }
}
