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
        $group = $request->get('group', 'board');
        $categories = $this->categoryService->getCategoryTree($group);
        $groups = $this->categoryService->getAllGroups();

        return view('backoffice.categories.index', compact('categories', 'groups', 'group'));
    }

    /**
     * 카테고리 등록 폼
     */
    public function create(Request $request)
    {
        $group = $request->get('group', 'board');
        $parentId = $request->get('parent_id');
        $groups = $this->categoryService->getAllGroups();
        
        $categories = Category::byGroup($group)->get();
        
        return view('backoffice.categories.create', compact('group', 'parentId', 'categories', 'groups'));
    }

    /**
     * 카테고리 저장
     */
    public function store(CategoryRequest $request)
    {
        try {
            $validated = $request->validated();
            $this->categoryService->createCategory($validated);
            
            return redirect()
                ->route('backoffice.categories.index', ['group' => $validated['category_group']])
                ->with('success', '카테고리가 등록되었습니다.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '카테고리 등록 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 카테고리 수정 폼
     */
    public function edit(Category $category)
    {
        $groups = $this->categoryService->getAllGroups();
        $categories = Category::byGroup($category->category_group)
            ->where('id', '!=', $category->id)
            ->get();
        
        return view('backoffice.categories.edit', compact('category', 'categories', 'groups'));
    }

    /**
     * 카테고리 업데이트
     */
    public function update(CategoryRequest $request, Category $category)
    {
        try {
            $this->categoryService->updateCategory($category, $request->validated());
            
            return redirect()
                ->route('backoffice.categories.index', ['group' => $category->category_group])
                ->with('success', '카테고리가 수정되었습니다.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '카테고리 수정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 카테고리 삭제
     */
    public function destroy(Category $category)
    {
        try {
            $group = $category->category_group;
            $this->categoryService->deleteCategory($category);
            
            return redirect()
                ->route('backoffice.categories.index', ['group' => $group])
                ->with('success', '카테고리가 삭제되었습니다.');
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 특정 그룹의 활성 카테고리 조회 (AJAX - 셀렉박스용)
     */
    public function getActiveCategories(string $group)
    {
        $categories = $this->categoryService->getActiveCategories($group);
        
        return response()->json($categories);
    }
}
