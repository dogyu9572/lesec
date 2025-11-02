<?php

namespace App\Services\Backoffice;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * 모든 그룹(depth=0) 조회
     */
    public function getAllGroups()
    {
        return Category::groups()
            ->with('allChildren')
            ->get();
    }

    /**
     * 그룹(0차)만 조회 (depth=0, parent_id=NULL)
     */
    public function getGroups()
    {
        return Category::groups()->get();
    }

    /**
     * 특정 그룹의 하위 카테고리만 조회 (1,2,3차)
     */
    public function getGroupChildren(int $groupId)
    {
        if (!$this->validateGroup($groupId)) {
            return collect();
        }

        return Category::where('parent_id', $groupId)
            ->with('allChildren')
            ->orderBy('display_order', 'desc')
            ->get();
    }

    /**
     * 특정 그룹의 1차 카테고리만 조회
     */
    public function getFirstLevelCategoriesByGroup(int $groupId)
    {
        if (!$this->validateGroup($groupId)) {
            return collect();
        }

        return Category::where('parent_id', $groupId)
            ->where('depth', 1)
            ->orderBy('display_order', 'desc')
            ->select('id', 'name', 'depth')
            ->get();
    }

    /**
     * 그룹 검증 (depth=0, parent_id=NULL)
     */
    public function validateGroup(int $groupId): bool
    {
        $group = Category::find($groupId);
        return $group && $group->depth === 0 && $group->parent_id === null;
    }

    /**
     * 수정용 카테고리 데이터 포맷팅
     */
    public function getCategoryForEdit(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
            'depth' => $category->depth,
            'display_order' => $category->display_order,
            'is_active' => $category->is_active,
        ];
    }

    /**
     * 카테고리 생성
     */
    public function createCategory(array $data)
    {
        // depth 자동 계산
        if (isset($data['parent_id'])) {
            $parent = Category::find($data['parent_id']);
            $data['depth'] = $parent->depth + 1;
        } else {
            // 그룹(0차) 생성 시 depth=0
            $data['depth'] = 0;
        }

        // display_order 자동 설정 (같은 부모의 마지막 순서 + 1)
        if (!isset($data['display_order'])) {
            $maxOrder = Category::where('parent_id', $data['parent_id'] ?? null)
                ->max('display_order');
            $data['display_order'] = ($maxOrder ?? 0) + 1;
        }

        // 코드 자동 생성 (모든 뎁스에 대해 통합된 코드 생성)
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode($data['depth'] ?? 0);
        }

        return Category::create($data);
    }

    /**
     * 카테고리 수정
     */
    public function updateCategory(Category $category, array $data)
    {
        try {
            // 부모 변경 시 depth 재계산
            if (isset($data['parent_id']) && $data['parent_id'] != $category->parent_id) {
                $parent = Category::find($data['parent_id']);
                $data['depth'] = $parent ? $parent->depth + 1 : 0;
            }

            $category->update($data);
            
            return $category;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 카테고리 삭제
     */
    public function deleteCategory(Category $category)
    {
        // 하위 카테고리가 있는지 확인
        if ($category->children()->count() > 0) {
            throw new \Exception('하위 카테고리가 있어 삭제할 수 없습니다.');
        }

        $category->delete();
        return true;
    }

    /**
     * 카테고리 순서 변경
     */
    public function updateOrder(array $orders)
    {
        foreach ($orders as $order => $id) {
            Category::where('id', $id)->update(['display_order' => $order]);
        }
        return true;
    }

    /**
     * 코드 자동 생성 (통합 - 모든 뎁스에 대해)
     */
    public function generateCode(int $depth = 0): string
    {
        // 기존 코드 중 가장 큰 숫자 찾기
        $existingCodes = Category::whereNotNull('code')
            ->where('code', 'like', 'C%')
            ->pluck('code')
            ->map(function ($code) {
                // C001 형식에서 숫자 부분 추출
                if (preg_match('/^C(\d+)$/', $code, $matches)) {
                    return (int)$matches[1];
                }
                return 0;
            });

        $nextNumber = $existingCodes->isEmpty() ? 1 : $existingCodes->max() + 1;
        
        return 'C' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 코드 중복 체크
     */
    public function checkCodeDuplicate(?string $code, ?int $excludeId = null): bool
    {
        if (!$code) {
            return false;
        }

        $query = Category::where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 인라인 수정 (코드, 명칭)
     */
    public function updateInline(Category $category, array $data): Category
    {
        // 코드 중복 체크
        if (isset($data['code'])) {
            $code = $data['code'];
            
            if ($this->checkCodeDuplicate($code, $category->id)) {
                throw new \Exception('이미 사용 중인 코드입니다.');
            }
        }

        $category->update($data);
        return $category;
    }
}

