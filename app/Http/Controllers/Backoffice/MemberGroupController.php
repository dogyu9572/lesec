<?php

namespace App\Http\Controllers\Backoffice;

use App\Models\Member;
use App\Services\Backoffice\MemberGroupService;
use App\Models\MemberGroup;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberGroupController extends BaseController
{
    protected $memberGroupService;

    public function __construct(MemberGroupService $memberGroupService)
    {
        $this->memberGroupService = $memberGroupService;
    }

    /**
     * 그룹 목록을 표시
     */
    public function index(Request $request)
    {
        $groups = $this->memberGroupService->getGroupsWithFilters($request);
        return view('backoffice.member-groups.index', compact('groups'));
    }

    /**
     * 그룹 생성 폼 표시
     */
    public function create()
    {
        return view('backoffice.member-groups.create');
    }

    /**
     * 그룹 저장
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            $this->memberGroupService->createGroup($request->all());

            return redirect()->route('backoffice.member-groups.index')
                ->with('success', '회원 그룹이 추가되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '회원 그룹 등록에 실패했습니다.');
        }
    }

    /**
     * 그룹 수정 폼 표시
     */
    public function edit(MemberGroup $memberGroup)
    {
        $group = $memberGroup->load('members');
        return view('backoffice.member-groups.edit', compact('group'));
    }

    /**
     * 그룹 정보 업데이트
     */
    public function update(Request $request, MemberGroup $memberGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            $this->memberGroupService->updateGroup($memberGroup, $request->all());

            return redirect()->route('backoffice.member-groups.index')
                ->with('success', '회원 그룹 정보가 수정되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '회원 그룹 수정에 실패했습니다.');
        }
    }

    /**
     * 그룹 삭제
     */
    public function destroy(MemberGroup $memberGroup)
    {
        try {
            $this->memberGroupService->deleteGroup($memberGroup);

            return redirect()->route('backoffice.member-groups.index')
                ->with('success', '회원 그룹이 삭제되었습니다.');
        } catch (\Exception $e) {
            return redirect()->route('backoffice.member-groups.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * 그룹 일괄 삭제
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'group_ids' => 'required|array',
            'group_ids.*' => 'integer|exists:member_groups,id'
        ]);

        $groupIds = $request->input('group_ids');
        
        try {
            $deletedCount = $this->memberGroupService->bulkDelete($groupIds);

            return response()->json([
                'success' => true,
                'message' => $deletedCount . '개의 그룹이 삭제되었습니다.',
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
     * 회원 검색 (팝업용)
     */
    public function searchMembers(Request $request)
    {
        try {
            $members = $this->memberGroupService->searchMembers($request);
            
            return response()->json([
                'members' => $members->items(),
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '회원 검색 중 오류가 발생했습니다: ' . $e->getMessage(),
                'members' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ]
            ], 500);
        }
    }

    /**
     * 그룹에 회원 추가
     */
    public function addMembers(Request $request, MemberGroup $memberGroup)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id'
        ]);

        $memberIds = $request->input('member_ids');
        
        try {
            $addedCount = $this->memberGroupService->addMembersToGroup($memberGroup->id, $memberIds);

            return response()->json([
                'success' => true,
                'message' => $addedCount . '명의 회원이 추가되었습니다.',
                'added_count' => $addedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '회원 추가 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 그룹에서 회원 제거
     */
    public function removeMember(Request $request, MemberGroup $memberGroup)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id'
        ]);

        $memberId = $request->input('member_id');
        
        try {
            $this->memberGroupService->removeMemberFromGroup($memberGroup->id, $memberId);

            return response()->json([
                'success' => true,
                'message' => '회원이 그룹에서 제거되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '회원 제거 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 해당 그룹 소속 회원 엑셀(CSV) 다운로드
     */
    public function exportMembers(MemberGroup $memberGroup): StreamedResponse
    {
        $group = $memberGroup->load('members');
        $members = $group->members;
        $filename = '회원그룹_' . \Str::slug($group->name) . '_회원목록_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($members) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM (엑셀 한글)

            fputcsv($file, $this->getMemberExportHeaders());

            $no = 1;
            foreach ($members as $member) {
                fputcsv($file, $this->formatMemberExportRow($member, $no++));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 회원 엑셀 다운로드 컬럼 헤더
     */
    private function getMemberExportHeaders(): array
    {
        return [
            'No',
            '이름',
            '로그인ID',
            '회원구분',
            '이메일',
            '생년월일',
            '성별',
            '(학생)연락처',
            '부모 연락처',
            '학교',
            '학년',
            '반',
            '주소',
            '등록일',
        ];
    }

    /**
     * 회원 한 행 포맷
     */
    private function formatMemberExportRow(Member $member, int $no): array
    {
        return [
            $no,
            $member->name ?? '',
            $member->login_id ?? '',
            $member->member_type === 'teacher' ? '교사' : '학생',
            $member->email ?? '',
            $member->birth_date ? $member->birth_date->format('Y-m-d') : '',
            $member->gender === 'male' ? '남' : ($member->gender === 'female' ? '여' : ''),
            $member->contact ?? '',
            $member->parent_contact ?? '',
            $member->school_name ?? '',
            $member->grade ?? '',
            $member->class_number ?? '',
            $member->address ?? '',
            $member->created_at ? $member->created_at->format('Y-m-d H:i') : '',
        ];
    }
}

