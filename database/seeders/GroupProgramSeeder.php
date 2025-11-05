<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramReservation;
use Carbon\Carbon;

class GroupProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'education_type' => 'middle_semester',
                'application_type' => 'group',
                'program_name' => 'M1. 광학현미경을 이용한 세포 관찰',
                'education_start_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'education_end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'payment_methods' => ['bank_transfer', 'online_card'],
                'reception_type' => 'application',
                'application_start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'application_end_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'capacity' => 30,
                'is_unlimited_capacity' => false,
                'education_fee' => 50000,
                'is_free' => false,
                'author' => '관리자',
                'is_active' => true,
            ],
            [
                'education_type' => 'middle_vacation',
                'application_type' => 'group',
                'program_name' => 'M1. 광학현미경을 이용한 세포 관찰',
                'education_start_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
                'education_end_date' => Carbon::now()->addDays(47)->format('Y-m-d'),
                'payment_methods' => ['bank_transfer', 'on_site_card'],
                'reception_type' => 'remaining',
                'application_start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'application_end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'capacity' => 25,
                'is_unlimited_capacity' => false,
                'education_fee' => 45000,
                'is_free' => false,
                'author' => '관리자',
                'is_active' => true,
            ],
            [
                'education_type' => 'high_semester',
                'application_type' => 'group',
                'program_name' => 'H1. 분자생물학 실험 기초',
                'education_start_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                'education_end_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                'payment_methods' => ['online_card'],
                'reception_type' => 'closed',
                'application_start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'application_end_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'capacity' => 20,
                'is_unlimited_capacity' => false,
                'education_fee' => 60000,
                'is_free' => false,
                'author' => '관리자',
                'is_active' => true,
            ],
            [
                'education_type' => 'high_vacation',
                'application_type' => 'group',
                'program_name' => 'H2. 유전자 분석 실험',
                'education_start_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'education_end_date' => Carbon::now()->addDays(62)->format('Y-m-d'),
                'payment_methods' => ['bank_transfer', 'on_site_card', 'online_card'],
                'reception_type' => 'application',
                'application_start_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'application_end_date' => Carbon::now()->addDays(40)->format('Y-m-d'),
                'capacity' => null,
                'is_unlimited_capacity' => true,
                'education_fee' => null,
                'is_free' => true,
                'author' => '관리자',
                'is_active' => true,
            ],
            [
                'education_type' => 'special',
                'application_type' => 'group',
                'program_name' => 'S1. 특별 프로그램 - 바이오테크놀로지',
                'education_start_date' => Carbon::now()->addDays(50)->format('Y-m-d'),
                'education_end_date' => Carbon::now()->addDays(50)->format('Y-m-d'),
                'payment_methods' => ['bank_transfer', 'online_card'],
                'reception_type' => 'application',
                'application_start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'application_end_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                'capacity' => 40,
                'is_unlimited_capacity' => false,
                'education_fee' => 70000,
                'is_free' => false,
                'author' => '관리자',
                'is_active' => true,
            ],
        ];

        foreach ($programs as $programData) {
            ProgramReservation::create($programData);
        }
    }
}

