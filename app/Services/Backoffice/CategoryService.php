<?php

namespace App\Services\Backoffice;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * 특정 그룹의 카테고리 트리 조회
     */
    public function getCategoryTree(string $group)
    {
        return Category::byGroup($group)
            ->roots()
            ->with('allChildren')
            ->get();
    }

    /**
     * 모든 그룹 목록 조회
     */
    public function getAllGroups()
    {
        return Category::select('category_group')
            ->distinct()
            ->pluck('category_group');
    }

    /**
     * 카테고리 생성
     */
    public function createCategory(array $data)
    {
        DB::beginTransaction();
        try {
            // depth 자동 계산
            if (isset($data['parent_id'])) {
                $parent = Category::find($data['parent_id']);
                $data['depth'] = $parent->depth + 1;
            } else {
                $data['depth'] = 1;
            }

            // display_order 자동 설정 (같은 부모의 마지막 순서 + 1)
            if (!isset($data['display_order'])) {
                $maxOrder = Category::where('parent_id', $data['parent_id'] ?? null)
                    ->where('category_group', $data['category_group'])
                    ->max('display_order');
                $data['display_order'] = ($maxOrder ?? 0) + 1;
            }

            $category = Category::create($data);
            
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 카테고리 수정
     */
    public function updateCategory(Category $category, array $data)
    {
        DB::beginTransaction();
        try {
            // 부모 변경 시 depth 재계산
            if (isset($data['parent_id']) && $data['parent_id'] != $category->parent_id) {
                $parent = Category::find($data['parent_id']);
                $data['depth'] = $parent ? $parent->depth + 1 : 1;
            }

            $category->update($data);
            
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 카테고리 삭제
     */
    public function deleteCategory(Category $category)
    {
        DB::beginTransaction();
        try {
            // 하위 카테고리가 있는지 확인
            if ($category->children()->count() > 0) {
                throw new \Exception('하위 카테고리가 있어 삭제할 수 없습니다.');
            }

            $category->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 카테고리 순서 변경
     */
    public function updateOrder(array $orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $order => $id) {
                Category::where('id', $id)->update(['display_order' => $order]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 특정 그룹의 활성화된 카테고리만 조회 (셀렉박스용)
     */
    public function getActiveCategories(string $group)
    {
        return Category::byGroup($group)
            ->active()
            ->orderBy('depth')
            ->orderBy('display_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'full_path' => $category->full_path,
                    'depth' => $category->depth,
                ];
            });
    }
}

