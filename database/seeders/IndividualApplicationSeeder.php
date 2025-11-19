<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IndividualApplication;
use App\Models\ProgramReservation;
use App\Models\Member;
use Carbon\Carbon;

class IndividualApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 개인 프로그램 예약 조회
        $individualPrograms = ProgramReservation::where('application_type', 'individual')
            ->where('is_active', true)
            ->get();

        if ($individualPrograms->isEmpty()) {
            $this->command->warn('개인 프로그램이 없어 개인 신청 데이터를 생성할 수 없습니다.');
            return;
        }

        // 회원 조회
        $members = Member::where('is_active', true)->get();

        $applications = [];

        foreach ($individualPrograms as $index => $program) {
            // 각 프로그램당 2-5개의 신청 생성
            $count = rand(2, 5);
            
            for ($i = 0; $i < $count; $i++) {
                $member = $members->random();
                $appliedDate = Carbon::parse($program->application_start_date)->addDays(rand(0, 5));

                $paymentStatuses = ['unpaid', 'paid', 'refunded', 'cancelled'];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

                $drawResults = ['pending', 'win', 'waitlist', 'fail'];
                $drawResult = $drawResults[array_rand($drawResults)];

                $paymentMethods = ['bank_transfer', 'on_site_card', 'online_card'];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                $applications[] = [
                    'program_reservation_id' => $program->id,
                    'member_id' => $member->id,
                    'application_number' => 'IND-' . now()->format('Ymd') . '-' . str_pad($index * 10 + $i + 1, 4, '0', STR_PAD_LEFT),
                    'education_type' => $program->education_type,
                    'reception_type' => $program->reception_type,
                    'program_name' => $program->program_name,
                    'participation_date' => $program->education_start_date,
                    'participation_fee' => $program->education_fee,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'draw_result' => $drawResult,
                    'applicant_name' => $member->name,
                    'applicant_school_name' => $member->school_name,
                    'applicant_grade' => $member->grade,
                    'applicant_class' => $member->class_number,
                    'applicant_contact' => $member->contact,
                    'guardian_contact' => $member->parent_contact,
                    'applied_at' => $appliedDate,
                    'created_at' => $appliedDate,
                    'updated_at' => $appliedDate,
                ];
            }
        }

        foreach ($applications as $application) {
            IndividualApplication::create($application);
        }

        $this->command->info('개인 신청 ' . count($applications) . '건이 생성되었습니다.');
    }
}

