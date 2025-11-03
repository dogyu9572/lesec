<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminGroupRequest;
use App\Http\Requests\UpdateAdminGroupRequest;
use App\Services\Backoffice\AdminGroupService;
use App\Models\AdminGroup;
use Illuminate\Http\Request;

class AdminGroupController extends Controller
{
    protected $adminGroupService;

    public function __construct(AdminGroupService $adminGroupService)
    {
        $this->adminGroupService = $adminGroupService;
    }

    /**
     * 그룹 목록을 표시
     */
    public function index(Request $request)
    {
        $groups = $this->adminGroupService->getGroupsWithFilters($request);
        return view('backoffice.admin-groups.index', compact('groups'));
    }

    /**
     * 그룹 생성 폼 표시
     */
    public function create()
    {
        $menus = $this->adminGroupService->getAllMenus();
        return view('backoffice.admin-groups.create', compact('menus'));
    }

    /**
     * 그룹 저장
     */
    public function store(StoreAdminGroupRequest $request)
    {
        $data = $request->validated();
        $group = $this->adminGroupService->createGroup($data);

        // 권한 저장
        $permissions = $request->input('permissions', []);
        if (!empty($permissions)) {
            $this->adminGroupService->saveGroupMenuPermissions($group->id, $permissions);
        }

        return redirect()->route('backoffice.admin-groups.index')
            ->with('success', '권한 그룹이 추가되었습니다.');
    }

    /**
     * 그룹 수정 폼 표시
     */
    public function edit($id)
    {
        $group = $this->adminGroupService->getGroup($id);
        $menus = $this->adminGroupService->getAllMenus();
        $groupPermissions = $this->adminGroupService->getGroupMenuPermissions($id);
        return view('backoffice.admin-groups.edit', compact('group', 'menus', 'groupPermissions'));
    }

    /**
     * 그룹 정보 업데이트
     */
    public function update(UpdateAdminGroupRequest $request, $id)
    {
        $group = $this->adminGroupService->getGroup($id);
        $data = $request->validated();
        $this->adminGroupService->updateGroup($group, $data);

        // 권한 업데이트
        $permissions = $request->input('permissions', []);
        $this->adminGroupService->saveGroupMenuPermissions($id, $permissions);

        return redirect()->route('backoffice.admin-groups.index')
            ->with('success', '권한 그룹 정보가 수정되었습니다.');
    }

    /**
     * 그룹 삭제
     */
    public function destroy($id)
    {
        try {
            $group = $this->adminGroupService->getGroup($id);
            $this->adminGroupService->deleteGroup($group);

            return redirect()->route('backoffice.admin-groups.index')
                ->with('success', '권한 그룹이 삭제되었습니다.');
        } catch (\Exception $e) {
            return redirect()->route('backoffice.admin-groups.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * 그룹 권한 설정 폼 표시
     */
    public function editPermissions($id)
    {
        $group = $this->adminGroupService->getGroup($id);
        $menus = $this->adminGroupService->getAllMenus();
        $groupPermissions = $this->adminGroupService->getGroupMenuPermissions($id);

        return view('backoffice.admin-groups.permissions', compact('group', 'menus', 'groupPermissions'));
    }

    /**
     * 그룹 권한 설정 저장
     */
    public function updatePermissions(Request $request, $id)
    {
        $permissions = $request->input('permissions', []);
        $this->adminGroupService->saveGroupMenuPermissions($id, $permissions);

        return redirect()->route('backoffice.admin-groups.index')
            ->with('success', '권한 설정이 저장되었습니다.');
    }
}

