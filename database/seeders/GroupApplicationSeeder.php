<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupApplication;
use App\Models\ProgramReservation;
use App\Models\Member;
use Carbon\Carbon;

class GroupApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 단체 프로그램 예약 조회
        $groupPrograms = ProgramReservation::where('application_type', 'group')
            ->where('is_active', true)
            ->get();

        if ($groupPrograms->isEmpty()) {
            $this->command->warn('단체 프로그램이 없어 단체 신청 데이터를 생성할 수 없습니다.');
            return;
        }

        // 교사 회원 조회
        $teachers = Member::where('member_type', 'teacher')
            ->where('is_active', true)
            ->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('교사 회원이 없어 단체 신청 데이터를 생성할 수 없습니다.');
            return;
        }

        $applications = [];

        foreach ($groupPrograms as $index => $program) {
            // 각 프로그램당 1-3개의 신청 생성
            $count = rand(1, 3);
            
            for ($i = 0; $i < $count; $i++) {
                $teacher = $teachers->random();
                $appliedDate = Carbon::parse($program->application_start_date)->addDays(rand(0, 3));

                $applicationStatuses = ['pending', 'approved', 'cancelled'];
                $applicationStatus = $applicationStatuses[array_rand($applicationStatuses)];

                $receptionStatuses = ['application', 'in_progress', 'completed', 'cancelled'];
                $receptionStatus = $receptionStatuses[array_rand($receptionStatuses)];

                $paymentStatuses = ['unpaid', 'paid', 'cancelled'];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

                $paymentMethods = ['bank_transfer', 'on_site_card', 'online_card'];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                $applicantCount = rand(10, 30);
                $participationFee = $program->education_fee ? $program->education_fee * $applicantCount : null;

                $applications[] = [
                    'program_reservation_id' => $program->id,
                    'application_number' => 'GRP-' . now()->format('Ymd') . '-' . str_pad($index * 10 + $i + 1, 4, '0', STR_PAD_LEFT),
                    'education_type' => $program->education_type,
                    'payment_methods' => $program->payment_methods,
                    'payment_method' => $paymentMethod,
                    'application_status' => $applicationStatus,
                    'reception_status' => $receptionStatus,
                    'applicant_name' => $teacher->name,
                    'member_id' => $teacher->id,
                    'applicant_contact' => $teacher->contact,
                    'school_level' => $program->education_type === 'middle_semester' || $program->education_type === 'middle_vacation' ? '중학교' : '고등학교',
                    'school_name' => $teacher->school_name,
                    'applicant_count' => $applicantCount,
                    'payment_status' => $paymentStatus,
                    'participation_fee' => $participationFee,
                    'participation_date' => $program->education_start_date,
                    'applied_at' => $appliedDate,
                    'created_at' => $appliedDate,
                    'updated_at' => $appliedDate,
                ];
            }
        }

        foreach ($applications as $application) {
            GroupApplication::create($application);
        }

        $this->command->info('단체 신청 ' . count($applications) . '건이 생성되었습니다.');
    }
}

