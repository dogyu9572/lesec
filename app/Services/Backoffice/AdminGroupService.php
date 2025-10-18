<?php

namespace App\Services\Backoffice;

use App\Models\AdminGroup;
use App\Models\GroupMenuPermission;
use App\Models\AdminMenu;
use Illuminate\Database\Eloquent\Collection;

class AdminGroupService
{
    /**
     * 그룹 목록을 가져옵니다.
     */
    public function getGroups(): Collection
    {
        return AdminGroup::orderBy('created_at', 'desc')->get();
    }

    /**
     * 필터링된 그룹 목록을 가져옵니다.
     */
    public function getGroupsWithFilters(\Illuminate\Http\Request $request)
    {
        $query = AdminGroup::query();
        
        // 이름 검색
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // 상태 필터
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // 목록 개수 설정
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * 그룹을 생성합니다.
     */
    public function createGroup(array $data): AdminGroup
    {
        return AdminGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * 그룹을 가져옵니다.
     */
    public function getGroup(int $id): AdminGroup
    {
        return AdminGroup::findOrFail($id);
    }

    /**
     * 그룹 정보를 업데이트합니다.
     */
    public function updateGroup(AdminGroup $group, array $data): bool
    {
        return $group->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * 그룹을 삭제합니다.
     */
    public function deleteGroup(AdminGroup $group): bool
    {
        // 이 그룹을 사용하는 관리자가 있는지 확인
        if ($group->users()->count() > 0) {
            throw new \Exception('이 그룹을 사용 중인 관리자가 있어 삭제할 수 없습니다.');
        }

        return $group->delete();
    }

    /**
     * 그룹의 메뉴 권한을 저장합니다.
     */
    public function saveGroupMenuPermissions(int $groupId, array $permissions): void
    {
        $group = AdminGroup::findOrFail($groupId);
        
        // 모든 메뉴 ID 가져오기
        $allMenuIds = AdminMenu::where('is_active', true)->pluck('id')->toArray();
        
        // 전송된 권한 처리
        foreach ($permissions as $menuId => $granted) {
            GroupMenuPermission::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'menu_id' => $menuId
                ],
                [
                    'granted' => (bool) $granted
                ]
            );
        }
        
        // 전송되지 않은 메뉴는 권한 제거
        $submittedMenuIds = array_keys($permissions);
        $removedMenuIds = array_diff($allMenuIds, $submittedMenuIds);
        
        if (!empty($removedMenuIds)) {
            GroupMenuPermission::where('group_id', $groupId)
                ->whereIn('menu_id', $removedMenuIds)
                ->update(['granted' => false]);
        }
    }

    /**
     * 그룹의 메뉴 권한을 가져옵니다.
     */
    public function getGroupMenuPermissions(int $groupId): array
    {
        $group = AdminGroup::findOrFail($groupId);
        return $group->getAllMenuPermissions();
    }

    /**
     * 모든 메뉴 목록을 가져옵니다.
     */
    public function getAllMenus(): Collection
    {
        return AdminMenu::getPermissionMenuTree();
    }
}

