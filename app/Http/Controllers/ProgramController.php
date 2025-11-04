<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProgramService;
use App\Models\Program;

class ProgramController extends Controller
{
    protected $programService;
    
    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }
    
    /**
     * 유형 선택 페이지
     */
    public function show($type)
    {
        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        
        return view('program.show', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type'));
    }
    
    /**
     * 단체 신청 페이지
     */
    public function applyGroup($type)
    {
        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        $programService = $this->programService;
        
        return view('program.apply-group', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type', 'programService'));
    }
    
    /**
     * 개인 신청 페이지
     */
    public function applyIndividual($type)
    {
        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        $programService = $this->programService;
        
        return view('program.apply-individual', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type', 'programService'));
    }
}
