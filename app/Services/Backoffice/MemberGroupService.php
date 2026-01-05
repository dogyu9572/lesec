<?php

namespace App\Services\Backoffice;

use App\Models\MemberGroup;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class MemberGroupService
{
    /**
     * 필터링된 그룹 목록을 가져옵니다.
     */
    public function getGroupsWithFilters(\Illuminate\Http\Request $request)
    {
        $query = MemberGroup::query();
        
        // 등록일 필터
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }
        
        // 그룹명 검색
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // 목록 개수 설정
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;
        
        return $query->withCount('members')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * 그룹을 생성합니다.
     */
    public function createGroup(array $data): MemberGroup
    {
        $group = MemberGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'member_count' => 0,
            'sort_order' => $data['sort_order'] ?? 0,
            'color' => $data['color'] ?? null,
        ]);
        
        $this->updateMemberCount($group);
        
        return $group;
    }

    /**
     * 그룹을 가져옵니다.
     */
    public function getGroup(int $id): MemberGroup
    {
        return MemberGroup::with('members')->findOrFail($id);
    }

    /**
     * 그룹 정보를 업데이트합니다.
     */
    public function updateGroup(MemberGroup $group, array $data): bool
    {
        $updated = $group->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $group->is_active,
            'sort_order' => $data['sort_order'] ?? $group->sort_order,
            'color' => $data['color'] ?? $group->color,
        ]);
        
        if ($updated) {
            $this->updateMemberCount($group);
        }
        
        return $updated;
    }

    /**
     * 그룹을 삭제합니다.
     */
    public function deleteGroup(MemberGroup $group): bool
    {
        // 그룹에 속한 회원들의 member_group_id를 null로 설정
        Member::where('member_group_id', $group->id)->update(['member_group_id' => null]);
        
        return $group->delete();
    }

    /**
     * 그룹 일괄 삭제
     */
    public function bulkDelete(array $groupIds): int
    {
        $deletedCount = 0;
        
        foreach ($groupIds as $groupId) {
            $group = MemberGroup::find($groupId);
            if ($group && $this->deleteGroup($group)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * 그룹에 회원을 추가합니다.
     */
    public function addMembersToGroup(int $groupId, array $memberIds): int
    {
        $group = MemberGroup::findOrFail($groupId);
        $addedCount = 0;
        
        foreach ($memberIds as $memberId) {
            $member = Member::find($memberId);
            if ($member && !$member->member_group_id) {
                $member->member_group_id = $groupId;
                if ($member->save()) {
                    $addedCount++;
                }
            }
        }
        
        $this->updateMemberCount($group);
        
        return $addedCount;
    }

    /**
     * 그룹에서 회원을 제거합니다.
     */
    public function removeMemberFromGroup(int $groupId, int $memberId): bool
    {
        $member = Member::where('id', $memberId)
            ->where('member_group_id', $groupId)
            ->firstOrFail();
        
        $member->member_group_id = null;
        $success = $member->save();
        
        if ($success) {
            $group = MemberGroup::find($groupId);
            if ($group) {
                $this->updateMemberCount($group);
            }
        }
        
        return $success;
    }

    /**
     * 그룹의 회원 수를 업데이트합니다.
     */
    public function updateMemberCount(MemberGroup $group): void
    {
        $count = Member::where('member_group_id', $group->id)->count();
        $group->update(['member_count' => $count]);
    }

    /**
     * 회원 검색 (팝업용)
     */
    public function searchMembers(\Illuminate\Http\Request $request)
    {
        $query = Member::query()->select('id', 'name', 'login_id', 'email', 'school_name', 'contact');
        
        // 검색어 필터 (search_type + search_keyword)
        $searchKeyword = $request->input('search_keyword', '');
        if (!empty($searchKeyword)) {
            $searchType = $request->input('search_type', 'all');
            
            $query->where(function($q) use ($searchType, $searchKeyword) {
                if ($searchType === 'all') {
                    $q->where('name', 'like', "%{$searchKeyword}%")
                      ->orWhere('login_id', 'like', "%{$searchKeyword}%")
                      ->orWhere('school_name', 'like', "%{$searchKeyword}%")
                      ->orWhere('email', 'like', "%{$searchKeyword}%")
                      ->orWhere('contact', 'like', "%{$searchKeyword}%");
                } else {
                    $q->where($searchType, 'like', "%{$searchKeyword}%");
                }
            });
        }
        
        // 그룹에 속하지 않은 회원만 표시
        if ($request->filled('exclude_grouped') && $request->exclude_grouped === 'true') {
            $query->whereNull('member_group_id');
        }
        
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}

