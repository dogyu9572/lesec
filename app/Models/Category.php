<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'category_group',
        'name',
        'depth',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 부모 카테고리
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 자식 카테고리들
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('display_order');
    }

    /**
     * 모든 하위 카테고리들 (재귀)
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * 특정 그룹의 카테고리만 조회
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('category_group', $group);
    }

    /**
     * 활성화된 카테고리만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 최상위 카테고리만 조회 (depth=1)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('display_order');
    }

    /**
     * 전체 경로 (예: 대분류 > 중분류 > 소분류)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
}
