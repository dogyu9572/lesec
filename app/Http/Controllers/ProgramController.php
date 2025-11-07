<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramReservation;
use App\Services\ProgramService;
use App\Services\ProgramReservationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    protected $programService;
    protected $programReservationService;
    
    public function __construct(
        ProgramService $programService,
        ProgramReservationService $programReservationService
    ) {
        $this->programService = $programService;
        $this->programReservationService = $programReservationService;
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

    /**
     * 단체 교육 선택 페이지 (캘린더)
     */
    public function selectGroup($type, Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        // 프로그램 조회
        $programs = $this->programReservationService->getProgramsByMonth($type, 'group', $year, $month);
        
        // 날짜별 그룹화
        $programsByDate = $this->programReservationService->groupProgramsByDate($programs);
        
        // 캘린더 생성
        $calendar = $this->programReservationService->generateCalendar($year, $month, $programsByDate);
        
        // 페이지 정보
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);
        
        return view('program.select-group', compact('gNum', 'sNum', 'gName', 'sName', 'programs', 'programsByDate', 'calendar', 'year', 'month', 'type'));
    }

    /**
     * 개인 교육 선택 페이지 (캘린더)
     */
    public function selectIndividual($type, Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        // 프로그램 조회
        $programs = $this->programReservationService->getProgramsByMonth($type, 'individual', $year, $month);
        
        // 날짜별 그룹화
        $programsByDate = $this->programReservationService->groupProgramsByDate($programs);
        
        // 캘린더 생성
        $calendar = $this->programReservationService->generateCalendar($year, $month, $programsByDate);
        
        // 페이지 정보
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);
        
        return view('program.select-individual', compact('gNum', 'sNum', 'gName', 'sName', 'programs', 'programsByDate', 'calendar', 'year', 'month', 'type'));
    }

    /**
     * 단체 신청 완료 페이지
     */
    public function completeGroup($type)
    {
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);
        
        return view('program.complete-group', compact('gNum', 'sNum', 'gName', 'sName', 'type'));
    }

    /**
     * 개인 신청 완료 페이지
     */
    public function completeIndividual($type)
    {
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);
        
        return view('program.complete-individual', compact('gNum', 'sNum', 'gName', 'sName', 'type'));
    }
}
