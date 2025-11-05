<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberGroup;

class MemberGroupSeeder extends Seeder
{
    /**
     * 회원 그룹 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        MemberGroup::query()->delete();

        $groups = [
            [
                'name' => '일반 회원',
                'description' => '일반 회원 그룹',
                'is_active' => true,
                'member_count' => 0,
                'sort_order' => 1,
                'color' => '#007bff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VIP 회원',
                'description' => 'VIP 회원 그룹',
                'is_active' => true,
                'member_count' => 0,
                'sort_order' => 2,
                'color' => '#ffc107',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '교사 그룹',
                'description' => '교사 회원 그룹',
                'is_active' => true,
                'member_count' => 0,
                'sort_order' => 3,
                'color' => '#28a745',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '학생 그룹',
                'description' => '학생 회원 그룹',
                'is_active' => true,
                'member_count' => 0,
                'sort_order' => 4,
                'color' => '#17a2b8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($groups as $group) {
            MemberGroup::create($group);
        }
    }
}

