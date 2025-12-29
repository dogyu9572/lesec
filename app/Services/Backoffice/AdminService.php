<?php

namespace App\Services\Backoffice;

use App\Models\User;
use App\Models\UserMenuPermission;
use App\Models\AdminMenu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class AdminService
{
    /**
     * 관리자 목록을 가져옵니다.
     */
    public function getAdmins(): Collection
    {
        return User::whereIn('role', ['super_admin', 'admin'])->get();
    }

    /**
     * 필터링된 관리자 목록을 가져옵니다.
     */
    public function getAdminsWithFilters(\Illuminate\Http\Request $request)
    {
        $query = User::whereIn('role', ['super_admin', 'admin']);
        
        // 통합 검색 (이름 또는 아이디)
        if ($request->filled('search_keyword')) {
            $searchType = $request->get('search_type', 'all');
            $searchKeyword = $request->search_keyword;
            
            if ($searchType === 'name') {
                $query->where('name', 'like', '%' . $searchKeyword . '%');
            } elseif ($searchType === 'login_id') {
                $query->where('login_id', 'like', '%' . $searchKeyword . '%');
            } else {
                // 전체 검색: 이름 또는 아이디
                $query->where(function($q) use ($searchKeyword) {
                    $q->where('name', 'like', '%' . $searchKeyword . '%')
                      ->orWhere('login_id', 'like', '%' . $searchKeyword . '%');
                });
            }
        }
        
        // 권한 필터
        if ($request->filled('role')) {
            $query->where('role', $request->role);
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
     * 관리자를 생성합니다.
     */
    public function createAdmin(array $data): User
    {
        $adminData = [
            'login_id' => $data['login_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'admin_group_id' => $data['admin_group_id'] ?? null,
            'is_active' => true,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'contact' => $data['contact'] ?? null,
        ];

        return User::create($adminData);
    }

    /**
     * 관리자를 가져옵니다.
     */
    public function getAdmin(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * 관리자 정보를 업데이트합니다.
     */
    public function updateAdmin(User $admin, array $data): bool
    {
        $admin->login_id = $data['login_id'] ?? null;
        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->admin_group_id = $data['admin_group_id'] ?? null;
        $admin->department = $data['department'] ?? null;
        $admin->position = $data['position'] ?? null;
        $admin->contact = $data['contact'] ?? null;

        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        return $admin->save();
    }

    /**
     * 관리자를 삭제합니다.
     */
    public function deleteAdmin(User $admin): bool
    {
        return $admin->delete();
    }

    /**
     * 관리자 일괄 삭제
     */
    public function bulkDelete(array $adminIds): int
    {
        $deletedCount = 0;
        
        foreach ($adminIds as $adminId) {
            $admin = User::find($adminId);
            
            // 슈퍼관리자는 삭제 불가
            if ($admin && $admin->role !== 'super_admin') {
                if ($admin->delete()) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    /**
     * 관리자의 메뉴 권한을 저장합니다. (레거시 - 더 이상 사용 안 함)
     */
    public function saveMenuPermissions(int $userId, array $permissions): void
    {
        // 그룹 기반 권한으로 변경됨 - 더 이상 사용하지 않음
    }

    /**
     * 관리자의 메뉴 권한을 가져옵니다.
     */
    public function getMenuPermissions(int $userId): array
    {
        $user = User::findOrFail($userId);
        return $user->getAllMenuPermissions();
    }

    /**
     * 모든 메뉴 목록을 가져옵니다.
     */
    public function getAllMenus(): Collection
    {
        return AdminMenu::getPermissionMenuTree();
    }
}
