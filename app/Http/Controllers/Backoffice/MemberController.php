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
     * 회원 검색 팝업 페이지
     */
    public function popupMemberSearch(Request $request)
    {
        $formAction = $request->get('form_action');
        $groupName = null;

        // 그룹 ID가 전달된 경우 그룹 이름 조회
        $groupId = $request->get('group_id');
        if ($groupId) {
            $memberGroup = \App\Models\MemberGroup::find($groupId);
            if ($memberGroup) {
                $groupName = $memberGroup->name;
            }
        }

        return $this->view('backoffice.popups.member-search', [
            'formAction' => $formAction,
            'groupName' => $groupName,
        ]);
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
        // 이메일 조합 (아이디 + 도메인 선택)
        $emailId = trim((string) $request->input('email_id', ''));
        $emailDomainSelected = (string) $request->input('email_domain', '');
        $emailDomainCustom = trim((string) $request->input('email_domain_custom', ''));
        $emailDomain = $emailDomainSelected === 'custom' ? $emailDomainCustom : $emailDomainSelected;
        if ($emailId !== '' && $emailDomain !== '') {
            $request->merge(['email' => $emailId . '@' . $emailDomain]);
        }

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
        // 이메일 조합 (아이디 + 도메인 선택)
        $emailId = trim((string) $request->input('email_id', ''));
        $emailDomainSelected = (string) $request->input('email_domain', '');
        $emailDomainCustom = trim((string) $request->input('email_domain_custom', ''));
        $emailDomain = $emailDomainSelected === 'custom' ? $emailDomainCustom : $emailDomainSelected;
        if ($emailId !== '' && $emailDomain !== '') {
            $request->merge(['email' => $emailId . '@' . $emailDomain]);
        }

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

        foreach ($members as $member) {
            $rowData = $this->formatMemberRow($member, $no);
            $columnIndex = 1;
            foreach ($rowData as $cellValue) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($columnLetter . $row, $cellValue);
                $columnIndex++;
            }
            $row++;
            $no++;
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

        return response()->streamDownload(function () use ($spreadsheet) {
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
            'No',
            '회원구분',
            'ID',
            '이름',
            '성별',
            '지역',
            '소속학교',
        ];
    }

    /**
     * 회원 행 포맷팅
     */
    private function formatMemberRow(Member $member, int $no): array
    {
        $city = (string) ($member->city ?? '');
        $district = (string) ($member->district ?? '');
        $region = trim($city) !== '' ? trim($city . ' ' . $district) : '기타';

        return [
            $no,
            $member->member_type === 'teacher' ? '교사' : '학생',
            $member->login_id,
            $member->name,
            $member->gender === 'male' ? '남' : '여',
            $region,
            $member->school_name ?? '-',
        ];
    }

    // (Excel) 회원 신청내역 포함 내보내기 기능은 현재 사용하지 않음
}
