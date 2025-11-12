<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\MemberService;
use App\Models\Member;
use App\Models\MemberGroup;
use Illuminate\Http\Request;

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
        
        return view('backoffice.members.index', compact('members', 'memberGroups'));
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
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $memberGroups = MemberGroup::active()->ordered()->get();
        return view('backoffice.members.edit', compact('member', 'memberGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $request->validate([
            'password' => 'nullable|min:4|confirmed',
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
        $filename = 'members_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($members) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, $this->getExportHeaders());
            
            $no = 1;
            foreach ($members as $member) {
                fputcsv($file, $this->formatMemberRow($member, $no++));
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Excel 내보내기 헤더
     */
    private function getExportHeaders(): array
    {
        return ['No', '회원구분', 'ID', '이름', '성별', '소속 시/도', '소속학교', '학년', '반',
                '생년월일', '연락처1', '연락처2', '이메일주소', '가입일시'];
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
}
