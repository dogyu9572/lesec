<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\MemberGroupService;
use App\Models\MemberGroup;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:members,id',
        ]);

        Log::info('=== 회원 그룹 생성 요청 ===', [
            'all_request' => $request->all(),
            'member_ids' => $request->input('member_ids'),
            'member_ids_type' => gettype($request->input('member_ids')),
            'member_ids_filled' => $request->filled('member_ids'),
        ]);

        try {
            $group = $this->memberGroupService->createGroup($request->all());

            Log::info('그룹 생성 완료', ['group_id' => $group->id, 'group_name' => $group->name]);

            // 회원 추가 처리
            if ($request->filled('member_ids')) {
                $memberIds = $request->input('member_ids');
                Log::info('회원 추가 시작', [
                    'group_id' => $group->id,
                    'member_ids' => $memberIds,
                    'member_ids_count' => count($memberIds),
                ]);
                
                $addedCount = $this->memberGroupService->addMembersToGroup($group->id, $memberIds);
                
                Log::info('회원 추가 완료', [
                    'group_id' => $group->id,
                    'added_count' => $addedCount,
                    'requested_count' => count($memberIds),
                ]);
            } else {
                Log::warning('member_ids가 요청에 없음');
            }

            return redirect()->route('backoffice.member-groups.index')
                ->with('success', '회원 그룹이 추가되었습니다.');
        } catch (\Exception $e) {
            Log::error('회원 그룹 생성 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '회원 그룹 등록에 실패했습니다.');
        }
    }

    /**
     * 회원 검증 (이미 그룹에 속한 회원 확인)
     */
    public function validateMembers(Request $request)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
        ]);

        $memberIds = $request->input('member_ids');
        
        // 이미 다른 그룹에 속한 회원 확인
        $existingGroupMembers = Member::whereIn('id', $memberIds)
            ->whereNotNull('member_group_id')
            ->get();

        if ($existingGroupMembers->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => '모든 회원을 추가할 수 있습니다.',
            ]);
        }

        $memberNames = $existingGroupMembers->pluck('name')->toArray();
        $memberIds = $existingGroupMembers->pluck('id')->toArray();

        return response()->json([
            'success' => false,
            'message' => '다음 회원들은 이미 다른 그룹에 속해 있어 추가할 수 없습니다: ' . implode(', ', $memberNames),
            'member_names' => $memberNames,
            'member_ids' => $memberIds,
        ], 422);
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
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:members,id',
        ]);

        try {
            $this->memberGroupService->updateGroup($memberGroup, $request->all());

            // 회원 추가/삭제 처리
            if ($request->filled('member_ids')) {
                $newMemberIds = $request->input('member_ids');
                
                // 기존 회원 ID 목록
                $existingMemberIds = $memberGroup->members->pluck('id')->toArray();
                
                // 추가할 회원 (새로 추가된 회원)
                $memberIdsToAdd = array_diff($newMemberIds, $existingMemberIds);
                
                // 삭제할 회원 (기존에 있던 회원 중 제거된 회원)
                $memberIdsToRemove = array_diff($existingMemberIds, $newMemberIds);
                
                // 추가할 회원 검증 (이미 다른 그룹에 속한 회원 확인)
                if (!empty($memberIdsToAdd)) {
                    $existingGroupMembers = Member::whereIn('id', $memberIdsToAdd)
                        ->whereNotNull('member_group_id')
                        ->where('member_group_id', '!=', $memberGroup->id)
                        ->get();
                    
                    if ($existingGroupMembers->isNotEmpty()) {
                        $memberNames = $existingGroupMembers->pluck('name')->implode(', ');
                        return redirect()->back()
                            ->withInput()
                            ->with('error', '다음 회원들은 이미 다른 그룹에 속해 있어 추가할 수 없습니다: ' . $memberNames);
                    }
                }
                
                // 회원 추가
                if (!empty($memberIdsToAdd)) {
                    $this->memberGroupService->addMembersToGroup($memberGroup->id, array_values($memberIdsToAdd));
                }
                
                // 회원 삭제 (member_group_id를 null로 설정)
                if (!empty($memberIdsToRemove)) {
                    Member::whereIn('id', $memberIdsToRemove)
                        ->where('member_group_id', $memberGroup->id)
                        ->update(['member_group_id' => null]);
                }
                
                // 회원 수 업데이트 (추가/삭제 후)
                if (!empty($memberIdsToAdd) || !empty($memberIdsToRemove)) {
                    $this->memberGroupService->updateMemberCount($memberGroup);
                }
            }

            return redirect()->route('backoffice.member-groups.index')
                ->with('success', '회원 그룹 정보가 수정되었습니다.');
        } catch (\Exception $e) {
            Log::error('회원 그룹 수정 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
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
}

