<?php

namespace App\Services\Member;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;

class SchoolSearchService
{
    public function search(array $filters): LengthAwarePaginator
    {
        $query = School::query()->active();

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('school_name', 'like', '%' . $keyword . '%')
                  ->orWhere('school_code', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($filters['city'])) {
            $query->byCity($filters['city']);
        }
        if (!empty($filters['district'])) {
            $query->byDistrict($filters['district']);
        }
        if (!empty($filters['school_level'])) {
            $query->bySchoolLevel($filters['school_level']);
        }

        return $query->orderBy('school_name')
            ->paginate(10);
    }
}


