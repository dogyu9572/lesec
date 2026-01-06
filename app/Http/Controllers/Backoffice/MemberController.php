<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\MemberService;
use App\Models\Member;
use App\Models\MemberGroup;
use App\Models\SidoSggCode;
use App\Models\IndividualApplication;
use App\Models\GroupApplication;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MemberController extends BaseController
{
    protected $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $members = $this->memberService->getMembersWithFilters($request);
        $memberGroups = MemberGroup::active()->ordered()->get();
        $cities = SidoSggCode::select('sido_name')
            ->distinct()
            ->orderBy('sido_name')
            ->pluck('sido_name');
        
        return view('backoffice.members.index', compact('members', 'memberGroups', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $memberGroups = MemberGroup::active()->ordered()->get();
        return view('backoffice.members.create', compact('memberGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'login_id' => 'required|unique:members,login_id',
            'password' => 'required|min:4|confirmed',
            'member_type' => 'required|in:teacher,student',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'contact' => 'nullable|string|max:50',
            'grade' => 'nullable|integer|between:1,3',
            'class_number' => 'nullable|integer|between:1,20',
        ]);

        try {
            $this->memberService->createMember($request->all());
            return redirect()->route('backoffice.members.index')
                ->with('success', '회원이 등록되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '회원 등록에 실패했습니다.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $memberGroups = MemberGroup::active()->ordered()->get();
        return view('backoffice.members.show', compact('member', 'memberGroups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $memberGroups = MemberGroup::active()->ordered()->get();
        
        // 개인 신청 내역
        $individualApplications = \App\Models\IndividualApplication::where('member_id', $member->id)
            ->with('reservation')
            ->orderBy('applied_at', 'desc')
            ->get();
        
        // 단체 신청 내역
        $groupApplications = \App\Models\GroupApplication::where('member_id', $member->id)
            ->with('reservation')
            ->orderBy('applied_at', 'desc')
            ->get();
        
        return view('backoffice.members.edit', compact('member', 'memberGroups', 'individualApplications', 'groupApplications'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $request->validate([
            'password' => 'nullable|min:4',
            'member_type' => 'required|in:teacher,student',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'contact' => 'nullable|string|max:50',
            'grade' => 'nullable|integer|between:1,3',
            'class_number' => 'nullable|integer|between:1,20',
        ]);

        try {
            $this->memberService->updateMember($member, $request->all());
            return redirect()->route('backoffice.members.index')
                ->with('success', '회원 정보가 수정되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '회원 수정에 실패했습니다.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        try {
            $this->memberService->deleteMember($member);
            return redirect()->route('backoffice.members.index')
                ->with('success', '회원이 삭제되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '회원 삭제에 실패했습니다.');
        }
    }
    
    /**
     * 회원 일괄 삭제
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id'
        ]);

        $memberIds = $request->input('member_ids');
        
        try {
            $deletedCount = $this->memberService->bulkDelete($memberIds);

            return response()->json([
                'success' => true,
                'message' => $deletedCount . '명의 회원이 삭제되었습니다.',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 회원 목록 Excel 다운로드
     */
    public function export(Request $request)
    {
        $members = $this->memberService->getMembersForExcel($request);
        $filename = 'members_' . date('YmdHis') . '.xlsx';
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 헤더 설정
        $headers = $this->getExportHeaders();
        $headerCount = count($headers);
        $lastHeaderCol = Coordinate::stringFromColumnIndex($headerCount);
        
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '1', $header);
            $columnIndex++;
        }
        
        // 헤더 스타일 적용
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:' . $lastHeaderCol . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(20);
        
        $row = 2;
        $no = 1;
        $memberBaseColCount = 14; // 회원 기본 정보 컬럼 수
        
        foreach ($members as $member) {
            $applicationRows = $this->formatMemberWithApplications($member, $no);
            $startRow = $row;
            
            foreach ($applicationRows as $index => $rowData) {
                $columnIndex = 1;
                foreach ($rowData as $cellValue) {
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    
                    if ($index === 0) {
                        // 첫 번째 행: 모든 데이터 표시
                        $sheet->setCellValue($columnLetter . $row, $cellValue);
                    } else {
                        // 두 번째 행부터: 회원 정보는 비우고 신청 정보만 표시
                        if ($columnIndex <= $memberBaseColCount) {
                            // 회원 정보 부분은 빈 값
                            $sheet->setCellValue($columnLetter . $row, '');
                        } else {
                            // 신청 정보만 표시
                            $sheet->setCellValue($columnLetter . $row, $cellValue);
                        }
                    }
                    $columnIndex++;
                }
                $row++;
            }
            
            $endRow = $row - 1;
            
            $endRow = $row - 1;
            
            // 회원 정보 부분 배경색 적용 및 병합
            if (count($applicationRows) > 1) {
                // 회원 정보 부분 병합 (첫 번째 행부터 마지막 행까지)
                for ($col = 1; $col <= $memberBaseColCount; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->mergeCells($columnLetter . $startRow . ':' . $columnLetter . $endRow);
                }
            }
            
            // 회원 정보 부분 배경색 적용
            $memberInfoStartCol = Coordinate::stringFromColumnIndex(1);
            $memberInfoEndCol = Coordinate::stringFromColumnIndex($memberBaseColCount);
            $sheet->getStyle($memberInfoStartCol . $startRow . ':' . $memberInfoEndCol . $endRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7F3FF']
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
            
            $no += count($applicationRows);
        }
        
        // 열 너비 자동 조정
        for ($col = 1; $col <= $headerCount; $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
            
        // 테두리 적용
        $lastRow = $row - 1;
        if ($lastRow > 1) {
            $sheet->getStyle('A1:' . $lastHeaderCol . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }
        
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    
    /**
     * Excel 내보내기 헤더
     */
    private function getExportHeaders(): array
    {
        return [
            // 회원 기본 정보
            'No', '회원구분', 'ID', '이름', '성별', '소속 시/도', '소속학교', '학년', '반',
            '생년월일', '연락처1', '연락처2', '이메일주소', '가입일시',
            // 신청 현황 정보
            '신청 번호', '신청 상태', '신청자명', '학교명', '교육 유형', '프로그램명', '신청 인원', 
            '접수 상태', '결제 방법', '교육료', '참가일', '신청일시', '출석 여부'
        ];
    }
    
    /**
     * 회원 행 포맷팅
     */
    private function formatMemberRow(Member $member, int $no): array
    {
        return [
            $no,
            $member->member_type === 'teacher' ? '교사' : '학생',
            $member->login_id,
            $member->name,
            $member->gender === 'male' ? '남' : '여',
            $member->city,
            $member->school_name,
            $member->grade ?? '-',
            $member->class_number ?? '-',
            $member->birth_date ? $member->birth_date->format('Ymd') : '-',
            $member->formatted_contact ?? '-',
            $member->formatted_parent_contact ?? '-',
            $member->email,
            $member->joined_at ? $member->joined_at->format('Y-m-d H:i') : '-',
        ];
    }

    /**
     * 회원 정보와 신청 내역을 포함한 행 포맷팅
     */
    private function formatMemberWithApplications(Member $member, int $no): array
    {
        // 회원 기본 정보
        $memberBase = [
            $no,
            $member->member_type === 'teacher' ? '교사' : '학생',
            $member->login_id,
            $member->name,
            $member->gender === 'male' ? '남' : '여',
            $member->city,
            $member->school_name,
            $member->grade ?? '-',
            $member->class_number ?? '-',
            $member->birth_date ? $member->birth_date->format('Ymd') : '-',
            $member->formatted_contact ?? '-',
            $member->formatted_parent_contact ?? '-',
            $member->email,
            $member->joined_at ? $member->joined_at->format('Y-m-d H:i') : '-',
        ];

        // 개인 신청 내역 조회
        $individualApplications = IndividualApplication::where('member_id', $member->id)
            ->with('reservation')
            ->orderBy('applied_at', 'desc')
            ->get();

        // 단체 신청 내역 조회
        $groupApplications = GroupApplication::where('member_id', $member->id)
            ->with('reservation')
            ->orderBy('applied_at', 'desc')
            ->get();

        $allApplications = collect();

        // 개인 신청 내역 추가
        foreach ($individualApplications as $app) {
            $allApplications->push([
                'type' => 'individual',
                'application_number' => $app->application_number ?? '-',
                'application_status' => $app->draw_result_label,
                'applicant_name' => $app->applicant_name ?? '-',
                'school_name' => $app->applicant_school_name ?? '-',
                'education_type' => $app->education_type_label,
                'program_name' => $app->program_name ?? '-',
                'applicant_count' => 1,
                'reception_status' => $app->reception_type_label,
                'payment_method' => $app->payment_method ? (GroupApplication::PAYMENT_METHOD_LABELS[$app->payment_method] ?? $app->payment_method) : '-',
                'participation_fee' => $app->participation_fee ?? 0,
                'participation_date' => $app->participation_date ? $app->participation_date->format('Y.m.d') : '-',
                'applied_at' => $app->applied_at ? $app->applied_at->format('Y.m.d H:i') : '-',
                'attendance' => '-',
            ]);
        }

        // 단체 신청 내역 추가
        foreach ($groupApplications as $app) {
            $allApplications->push([
                'type' => 'group',
                'application_number' => $app->application_number ?? '-',
                'application_status' => $app->application_status_label,
                'applicant_name' => $app->applicant_name ?? '-',
                'school_name' => $app->school_name ?? '-',
                'education_type' => $app->education_type_label,
                'program_name' => $app->program_name_label,
                'applicant_count' => $app->applicant_count ?? 0,
                'reception_status' => $app->reception_status_label,
                'payment_method' => $app->payment_method_label,
                'participation_fee' => $app->participation_fee ?? 0,
                'participation_date' => $app->participation_date_formatted ?? '-',
                'applied_at' => $app->applied_at_formatted ?? '-',
                'attendance' => '-',
            ]);
        }

        // 신청일시 기준 내림차순 정렬
        $allApplications = $allApplications->sortByDesc(function($app) {
            return $app['applied_at'];
        })->values();

        $rows = [];

        if ($allApplications->count() === 0) {
            // 신청 내역이 없는 경우 회원 정보만 있는 행 1개
            $rows[] = array_merge($memberBase, [
                '', '', '', '', '', '', '', '', '', '', '', '', ''
            ]);
        } else {
            // 신청 내역이 있는 경우 각 신청 내역마다 행 생성
            foreach ($allApplications as $app) {
                $rows[] = array_merge($memberBase, [
                    $app['application_number'],
                    $app['application_status'],
                    $app['applicant_name'],
                    $app['school_name'],
                    $app['education_type'],
                    $app['program_name'],
                    $app['applicant_count'] . '명',
                    $app['reception_status'],
                    $app['payment_method'],
                    number_format($app['participation_fee']) . '원',
                    $app['participation_date'],
                    $app['applied_at'],
                    $app['attendance'],
                ]);
            }
        }

        return $rows;
    }
}
