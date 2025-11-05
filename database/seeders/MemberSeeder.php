<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\MemberGroup;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * 회원 데이터를 시드합니다.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        Member::query()->delete();

        // 회원 그룹 조회
        $normalGroup = MemberGroup::where('name', '일반 회원')->first();
        $vipGroup = MemberGroup::where('name', 'VIP 회원')->first();
        $teacherGroup = MemberGroup::where('name', '교사 그룹')->first();
        $studentGroup = MemberGroup::where('name', '학생 그룹')->first();

        $members = [
            // 교사 회원
            [
                'login_id' => 'teacher001',
                'password' => Hash::make('password123'),
                'member_type' => 'teacher',
                'member_group_id' => $teacherGroup ? $teacherGroup->id : null,
                'name' => '김교사',
                'email' => 'teacher001@example.com',
                'birth_date' => '1980-05-15',
                'gender' => 'male',
                'contact' => '010-1234-5678',
                'parent_contact' => null,
                'city' => '서울특별시',
                'district' => '강남구',
                'school_name' => '서울고등학교',
                'school_id' => null,
                'grade' => null,
                'class_number' => null,
                'address' => '서울특별시 강남구 테헤란로 123',
                'zipcode' => '06123',
                'emergency_contact' => '010-1111-2222',
                'emergency_contact_relation' => '배우자',
                'profile_image' => null,
                'email_consent' => true,
                'sms_consent' => true,
                'memo' => '담임 교사',
                'is_active' => true,
                'joined_at' => now()->subMonths(6),
                'last_login_at' => now()->subDays(1),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],
            [
                'login_id' => 'teacher002',
                'password' => Hash::make('password123'),
                'member_type' => 'teacher',
                'member_group_id' => $teacherGroup ? $teacherGroup->id : null,
                'name' => '이교사',
                'email' => 'teacher002@example.com',
                'birth_date' => '1985-08-20',
                'gender' => 'female',
                'contact' => '010-2345-6789',
                'parent_contact' => null,
                'city' => '경기도',
                'district' => '성남시',
                'school_name' => '성남중학교',
                'school_id' => null,
                'grade' => null,
                'class_number' => null,
                'address' => '경기도 성남시 분당구 정자로 456',
                'zipcode' => '13579',
                'emergency_contact' => '010-2222-3333',
                'emergency_contact_relation' => '가족',
                'profile_image' => null,
                'email_consent' => true,
                'sms_consent' => false,
                'memo' => '과목 담당 교사',
                'is_active' => true,
                'joined_at' => now()->subMonths(3),
                'last_login_at' => now()->subDays(3),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ],
            // 학생 회원
            [
                'login_id' => 'student001',
                'password' => Hash::make('password123'),
                'member_type' => 'student',
                'member_group_id' => $studentGroup ? $studentGroup->id : null,
                'name' => '김학생',
                'email' => 'student001@example.com',
                'birth_date' => '2010-03-10',
                'gender' => 'male',
                'contact' => '010-3456-7890',
                'parent_contact' => '010-1111-1111',
                'city' => '서울특별시',
                'district' => '송파구',
                'school_name' => '송파초등학교',
                'school_id' => null,
                'grade' => 3,
                'class_number' => 5,
                'address' => '서울특별시 송파구 잠실로 789',
                'zipcode' => '05555',
                'emergency_contact' => '010-1111-1111',
                'emergency_contact_relation' => '부모',
                'profile_image' => null,
                'email_consent' => false,
                'sms_consent' => true,
                'memo' => '활발한 학생',
                'is_active' => true,
                'joined_at' => now()->subMonths(2),
                'last_login_at' => now()->subDays(5),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ],
            [
                'login_id' => 'student002',
                'password' => Hash::make('password123'),
                'member_type' => 'student',
                'member_group_id' => $studentGroup ? $studentGroup->id : null,
                'name' => '이학생',
                'email' => 'student002@example.com',
                'birth_date' => '2009-07-25',
                'gender' => 'female',
                'contact' => '010-4567-8901',
                'parent_contact' => '010-2222-2222',
                'city' => '경기도',
                'district' => '수원시',
                'school_name' => '수원초등학교',
                'school_id' => null,
                'grade' => 4,
                'class_number' => 2,
                'address' => '경기도 수원시 영통구 원천로 321',
                'zipcode' => '16444',
                'emergency_contact' => '010-2222-2222',
                'emergency_contact_relation' => '부모',
                'profile_image' => null,
                'email_consent' => true,
                'sms_consent' => true,
                'memo' => '성실한 학생',
                'is_active' => true,
                'joined_at' => now()->subMonths(1),
                'last_login_at' => now()->subDays(2),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subMonths(1),
                'updated_at' => now(),
            ],
            // 일반 회원
            [
                'login_id' => 'member001',
                'password' => Hash::make('password123'),
                'member_type' => 'student',
                'member_group_id' => $normalGroup ? $normalGroup->id : null,
                'name' => '박회원',
                'email' => 'member001@example.com',
                'birth_date' => '2011-11-30',
                'gender' => 'male',
                'contact' => '010-5678-9012',
                'parent_contact' => '010-3333-3333',
                'city' => '인천광역시',
                'district' => '남동구',
                'school_name' => '인천초등학교',
                'school_id' => null,
                'grade' => 2,
                'class_number' => 1,
                'address' => '인천광역시 남동구 구월로 654',
                'zipcode' => '21555',
                'emergency_contact' => '010-3333-3333',
                'emergency_contact_relation' => '부모',
                'profile_image' => null,
                'email_consent' => true,
                'sms_consent' => true,
                'memo' => null,
                'is_active' => true,
                'joined_at' => now()->subDays(30),
                'last_login_at' => now()->subDays(7),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
            // VIP 회원
            [
                'login_id' => 'vip001',
                'password' => Hash::make('password123'),
                'member_type' => 'teacher',
                'member_group_id' => $vipGroup ? $vipGroup->id : null,
                'name' => '최교사',
                'email' => 'vip001@example.com',
                'birth_date' => '1975-12-05',
                'gender' => 'male',
                'contact' => '010-6789-0123',
                'parent_contact' => null,
                'city' => '부산광역시',
                'district' => '해운대구',
                'school_name' => '부산고등학교',
                'school_id' => null,
                'grade' => null,
                'class_number' => null,
                'address' => '부산광역시 해운대구 해운대해변로 987',
                'zipcode' => '48058',
                'emergency_contact' => '010-4444-4444',
                'emergency_contact_relation' => '가족',
                'profile_image' => null,
                'email_consent' => true,
                'sms_consent' => true,
                'memo' => 'VIP 회원',
                'is_active' => true,
                'joined_at' => now()->subMonths(12),
                'last_login_at' => now()->subHours(12),
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
                'created_at' => now()->subMonths(12),
                'updated_at' => now(),
            ],
        ];

        foreach ($members as $member) {
            Member::create($member);
        }

        // 회원 그룹의 member_count 업데이트
        MemberGroup::all()->each(function ($group) {
            $group->update([
                'member_count' => $group->members()->count(),
            ]);
        });
    }
}

