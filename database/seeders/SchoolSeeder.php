<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * 실행될 시더
     */
    public function run(): void
    {
        $now = now();

        $schools = [
            [
                'source_type' => 'admin_registration',
                'city' => '서울특별시',
                'district' => '강남구',
                'school_level' => 'high',
                'school_name' => '서울고등학교',
                'school_code' => 'S001',
                'address' => '서울특별시 강남구 테헤란로 123',
                'phone' => '02-123-4567',
                'homepage' => 'https://seoul-high.example.com',
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1980-03-02',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '서울특별시',
                'district' => '마포구',
                'school_level' => 'middle',
                'school_name' => '마포중학교',
                'school_code' => 'S002',
                'address' => '서울특별시 마포구 월드컵북로 29',
                'phone' => '02-234-5678',
                'homepage' => 'https://mapo-middle.example.com',
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1992-03-02',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '서울특별시',
                'district' => '송파구',
                'school_level' => 'elementary',
                'school_name' => '잠실초등학교',
                'school_code' => 'S003',
                'address' => '서울특별시 송파구 올림픽로 300',
                'phone' => '02-345-6789',
                'homepage' => null,
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1975-04-01',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '경기도',
                'district' => '수원시',
                'school_level' => 'high',
                'school_name' => '수원과학고등학교',
                'school_code' => 'G001',
                'address' => '경기도 수원시 영통구 대학로 10',
                'phone' => '031-111-2222',
                'homepage' => 'https://su-won-science.example.com',
                'is_coed' => false,
                'day_night_division' => '주간',
                'founding_date' => '2002-03-02',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '경기도',
                'district' => '고양시',
                'school_level' => 'middle',
                'school_name' => '고양중학교',
                'school_code' => 'G002',
                'address' => '경기도 고양시 일산동구 중앙로 65',
                'phone' => '031-222-3333',
                'homepage' => null,
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1995-09-01',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '경상북도',
                'district' => '포항시',
                'school_level' => 'high',
                'school_name' => '포항제철고등학교',
                'school_code' => 'K001',
                'address' => '경상북도 포항시 남구 지곡로 100',
                'phone' => '054-123-4567',
                'homepage' => 'https://posco-high.example.com',
                'is_coed' => false,
                'day_night_division' => '주간',
                'founding_date' => '1985-03-01',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '부산광역시',
                'district' => '해운대구',
                'school_level' => 'elementary',
                'school_name' => '해운대초등학교',
                'school_code' => 'B001',
                'address' => '부산광역시 해운대구 해운대로 570',
                'phone' => '051-111-2233',
                'homepage' => null,
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1970-05-01',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '대전광역시',
                'district' => '유성구',
                'school_level' => 'middle',
                'school_name' => '대전과학중학교',
                'school_code' => 'D001',
                'address' => '대전광역시 유성구 과학로 123',
                'phone' => '042-555-6655',
                'homepage' => 'https://daejeon-science-middle.example.com',
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '2010-03-02',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '강원특별자치도',
                'district' => '춘천시',
                'school_level' => 'high',
                'school_name' => '춘천여자고등학교',
                'school_code' => 'C001',
                'address' => '강원특별자치도 춘천시 방송길 25',
                'phone' => '033-333-4444',
                'homepage' => null,
                'is_coed' => false,
                'day_night_division' => '주간',
                'founding_date' => '1968-03-05',
            ],
            [
                'source_type' => 'admin_registration',
                'city' => '제주특별자치도',
                'district' => '제주시',
                'school_level' => 'high',
                'school_name' => '제주중앙고등학교',
                'school_code' => 'J001',
                'address' => '제주특별자치도 제주시 연동 123-4',
                'phone' => '064-123-7890',
                'homepage' => null,
                'is_coed' => true,
                'day_night_division' => '주간',
                'founding_date' => '1978-04-01',
            ],
        ];

        foreach ($schools as $school) {
            School::create(array_merge($school, [
                'status' => 'normal',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}


