<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Services\Backoffice\AdminService;
use App\Models\User;
use App\Models\AdminGroup;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * 관리자 목록을 표시
     */
    public function index(Request $request)
    {
        $admins = $this->adminService->getAdminsWithFilters($request);

        return view('backoffice.admins.index', compact('admins'));
    }

    /**
     * 관리자 생성 폼 표시
     */
    public function create()
    {
        $groups = AdminGroup::where('is_active', true)->get();
        return view('backoffice.admins.create', compact('groups'));
    }

    /**
     * 관리자 저장
     */
    public function store(StoreAdminRequest $request)
    {
        $data = $request->validated();
        $admin = $this->adminService->createAdmin($data);

        return redirect()->route('backoffice.admins.index')
            ->with('success', '관리자가 추가되었습니다.');
    }

    /**
     * 관리자 상세 정보 표시
     */
    public function show($id)
    {
        $admin = $this->adminService->getAdmin($id);
        return view('backoffice.admins.show', compact('admin'));
    }

    /**
     * 관리자 수정 폼 표시
     */
    public function edit($id)
    {
        $admin = $this->adminService->getAdmin($id);
        $groups = AdminGroup::where('is_active', true)->get();
        return view('backoffice.admins.edit', compact('admin', 'groups'));
    }

    /**
     * 관리자 정보 업데이트
     */
    public function update(UpdateAdminRequest $request, $id)
    {
        $admin = $this->adminService->getAdmin($id);
        $data = $request->validated();
        $this->adminService->updateAdmin($admin, $data);

        return redirect()->route('backoffice.admins.index')
            ->with('success', '관리자 정보가 수정되었습니다.');
    }

    /**
     * 관리자 삭제
     */
    public function destroy($id)
    {
        $admin = $this->adminService->getAdmin($id);
        $this->adminService->deleteAdmin($admin);

        return redirect()->route('backoffice.admins.index')
            ->with('success', '관리자가 삭제되었습니다.');
    }

    /**
     * 관리자 일괄 삭제
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'integer|exists:users,id'
        ]);

        $adminIds = $request->input('admin_ids');
        
        try {
            // 서비스를 통해 일괄 삭제
            $deletedCount = $this->adminService->bulkDelete($adminIds);

            return response()->json([
                'success' => true,
                'message' => $deletedCount . '명의 관리자가 삭제되었습니다.',
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
     * 아이디 중복 체크
     */
    public function checkLoginId(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string|max:255'
        ]);

        $loginId = $request->input('login_id');
        $exists = User::where('login_id', $loginId)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? '이미 사용 중인 아이디입니다.' : '사용 가능한 아이디입니다.'
        ]);
    }
}
