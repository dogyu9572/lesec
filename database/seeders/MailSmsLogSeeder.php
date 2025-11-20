<?php

namespace Database\Seeders;

use App\Models\MailSmsLog;
use App\Models\MailSmsMessage;
use App\Models\MailSmsMessageMember;
use App\Models\Member;
use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailSmsLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 기존 데이터 삭제
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MailSmsLog::truncate();
        MailSmsMessageMember::truncate();
        MailSmsMessage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 관리자 사용자 조회
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            $admin = User::first();
        }

        // 회원 그룹 조회
        $memberGroup = MemberGroup::first();

        // 회원 조회 (최소 10명)
        $members = Member::limit(10)->get();
        if ($members->isEmpty()) {
            $this->command->warn('회원이 없어서 시더를 실행할 수 없습니다. MemberSeeder를 먼저 실행해주세요.');
            return;
        }

        $now = now();

        // 발송 완료된 메시지 생성
        $messages = [
            [
                'message_type' => MailSmsMessage::TYPE_EMAIL,
                'title' => '2025년 1월 프로그램 안내',
                'content' => "안녕하세요.\n\n2025년 1월 프로그램 안내입니다.\n\n많은 참여 부탁드립니다.",
                'writer_id' => $admin->id,
                'member_group_id' => $memberGroup?->id,
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => 8,
                'failure_count' => 2,
                'send_requested_at' => $now->copy()->subDays(5)->setTime(10, 0),
                'send_started_at' => $now->copy()->subDays(5)->setTime(10, 1),
                'send_completed_at' => $now->copy()->subDays(5)->setTime(10, 5),
                'created_at' => $now->copy()->subDays(5)->setTime(9, 0),
            ],
            [
                'message_type' => MailSmsMessage::TYPE_SMS,
                'title' => '긴급 공지사항',
                'content' => '긴급 공지사항이 있습니다. 확인 부탁드립니다.',
                'writer_id' => $admin->id,
                'member_group_id' => $memberGroup?->id,
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => 7,
                'failure_count' => 1,
                'send_requested_at' => $now->copy()->subDays(3)->setTime(14, 0),
                'send_started_at' => $now->copy()->subDays(3)->setTime(14, 1),
                'send_completed_at' => $now->copy()->subDays(3)->setTime(14, 3),
                'created_at' => $now->copy()->subDays(3)->setTime(13, 0),
            ],
            [
                'message_type' => MailSmsMessage::TYPE_KAKAO,
                'title' => '카카오 알림톡 테스트',
                'content' => '카카오 알림톡 발송 테스트입니다.',
                'writer_id' => $admin->id,
                'member_group_id' => $memberGroup?->id,
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => 6,
                'failure_count' => 0,
                'send_requested_at' => $now->copy()->subDays(1)->setTime(16, 0),
                'send_started_at' => $now->copy()->subDays(1)->setTime(16, 1),
                'send_completed_at' => $now->copy()->subDays(1)->setTime(16, 2),
                'created_at' => $now->copy()->subDays(1)->setTime(15, 0),
            ],
        ];

        foreach ($messages as $messageData) {
            $message = MailSmsMessage::create($messageData);

            // 수신 대상 회원 선택 (메시지당 8-10명)
            $selectedMembers = $members->random(min(8, $members->count()));
            $successCount = 0;
            $failureCount = 0;

            foreach ($selectedMembers as $index => $member) {
                // 수신 대상 연결
                $messageMember = MailSmsMessageMember::create([
                    'mail_sms_message_id' => $message->id,
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'member_email' => $member->email,
                    'member_contact' => $member->contact,
                    'is_selected' => true,
                ]);

                // 발송 로그 생성 (성공/실패 랜덤)
                $isSuccess = $index < ($message->success_count);
                $resultStatus = $isSuccess ? 'success' : 'failure';

                MailSmsLog::create([
                    'mail_sms_message_id' => $message->id,
                    'mail_sms_message_member_id' => $messageMember->id,
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'member_email' => $member->email,
                    'member_contact' => $member->contact,
                    'result_status' => $resultStatus,
                    'sent_at' => $message->send_completed_at->copy()->addSeconds($index),
                    'response_code' => $isSuccess ? '200' : '500',
                    'response_message' => $isSuccess ? null : '발송 실패: 수신자 정보 오류',
                ]);

                if ($isSuccess) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            // 메시지의 성공/실패 건수 업데이트
            $message->update([
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);
        }

        $this->command->info('메일/SMS 발송 로그 시더가 완료되었습니다.');
    }
}
