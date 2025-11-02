<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\Backoffice\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * 카테고리 목록
     */
    public function index(Request $request)
    {
        // 그룹 목록 조회
        $groups = $this->categoryService->getGroups();
        
        // 선택된 그룹 ID (파라미터 또는 첫 번째 그룹)
        $selectedGroupId = $request->get('group', $groups->first()?->id);
        
        // 선택된 그룹 정보 조회
        $selectedGroup = $selectedGroupId ? Category::find($selectedGroupId) : null;
        
        // 선택된 그룹의 하위 카테고리만 조회
        $categories = $selectedGroupId 
            ? $this->categoryService->getGroupChildren($selectedGroupId)
            : collect();

        return view('backoffice.categories.index', compact('groups', 'categories', 'selectedGroupId', 'selectedGroup'));
    }


    /**
     * 카테고리 삭제
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);
            
            return redirect()
                ->route('backoffice.categories.index')
                ->with('success', '카테고리가 삭제되었습니다.');
        } catch (\Exception $e) {
            return back()
                ->with('error', '카테고리 삭제 중 오류가 발생했습니다.');
        }
    }

    /**
     * 카테고리 순서 변경 (AJAX)
     */
    public function updateOrder(Request $request)
    {
        try {
            $this->categoryService->updateOrder($request->orders);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '순서 변경 중 오류가 발생했습니다.'
            ], 500);
        }
    }


    /**
     * 인라인 수정 (AJAX)
     */
    public function updateInline(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'code' => 'nullable|string|max:50',
                'name' => 'nullable|string|max:100',
            ]);

            $category = $this->categoryService->updateInline($category, $validated);
            
            return response()->json([
                'success' => true,
                'message' => '카테고리가 수정되었습니다.',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * 특정 그룹의 1차 카테고리 조회 (AJAX)
     */
    public function getByGroup($groupId)
    {
        try {
            $categories = $this->categoryService->getFirstLevelCategoriesByGroup($groupId);
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * 카테고리 수정용 데이터 조회 (AJAX)
     */
    public function getEditData(Category $category)
    {
        try {
            return response()->json([
                'success' => true,
                'category' => $this->categoryService->getCategoryForEdit($category)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카테고리 정보를 불러올 수 없습니다.'
            ], 500);
        }
    }

    /**
     * 모달 수정 (AJAX)
     */
    public function updateModal(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'code' => 'nullable|string|max:50',
                'name' => 'required|string|max:100',
                'display_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            $category = Category::findOrFail($validated['category_id']);
            unset($validated['category_id']);

            $category = $this->categoryService->updateCategory($category, $validated);
            
            return response()->json([
                'success' => true,
                'message' => '카테고리가 수정되었습니다.',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카테고리 수정 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 모달 등록 (AJAX)
     */
    public function storeModal(Request $request)
    {
        try {
            $validated = $request->validate([
                'depth_type' => 'nullable|in:0,1,2',
                'group_id' => 'nullable|exists:categories,id',
                'parent_category_id' => 'nullable|exists:categories,id',
                'parent_id' => 'nullable|exists:categories,id',
                'code' => 'nullable|string|max:50',
                'name' => 'required|string|max:100',
                'display_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            // depth_type에 따라 parent_id 설정
            if (isset($validated['depth_type'])) {
                if ($validated['depth_type'] === '0') {
                    $validated['parent_id'] = null; // 그룹은 부모 없음
                } else if ($validated['depth_type'] === '1') {
                    $validated['parent_id'] = $validated['group_id'] ?? null; // 1차는 그룹을 부모로
                } else if ($validated['depth_type'] === '2') {
                    $validated['parent_id'] = $validated['parent_category_id'] ?? null; // 2차는 1차를 부모로
                }
            }
            
            // 불필요한 필드 제거
            unset($validated['depth_type'], $validated['group_id'], $validated['parent_category_id']);

            $category = $this->categoryService->createCategory($validated);
            
            return response()->json([
                'success' => true,
                'message' => '카테고리가 등록되었습니다.',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카테고리 등록 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 미리 생성될 코드 조회 (AJAX)
     */
    public function generatePreviewCode()
    {
        try {
            $code = $this->categoryService->generateCode(0);
            
            return response()->json([
                'success' => true,
                'code' => $code
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '코드 생성 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}
