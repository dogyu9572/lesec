<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\RosterService;
use App\Services\Backoffice\SmsKakaoApiService;
use App\Services\ProgramReservationService;
use App\Models\ProgramReservation;
use App\Models\Member;
use App\Models\IndividualApplication;
use App\Models\GroupApplication;
use App\Models\GroupApplicationParticipant;
use App\Models\MailSmsMessage;
use App\Models\MailSmsMessageMember;
use App\Models\MailSmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RosterController extends BaseController
{
    protected RosterService $rosterService;
    protected ProgramReservationService $programReservationService;
    protected SmsKakaoApiService $smsKakaoApiService;

    public function __construct(
        RosterService $rosterService,
        ProgramReservationService $programReservationService,
        SmsKakaoApiService $smsKakaoApiService
    ) {
        $this->rosterService = $rosterService;
        $this->programReservationService = $programReservationService;
        $this->smsKakaoApiService = $smsKakaoApiService;
    }

    /**
     * 명단 관리 목록
     */
    public function index(Request $request)
    {
        $rosters = $this->rosterService->getFilteredRosters($request);
        $filters = $this->rosterService->getFilterOptions();

        return $this->view('backoffice.rosters.index', [
            'rosters' => $rosters,
            'filters' => $filters,
        ]);
    }

    /**
     * SMS/메일 발송 팝업 창
     */
    public function popupSmsEmail(Request $request)
    {
        return view('backoffice.popups.sms-email');
    }

    /**
     * 명단 관리 상세 (edit)
     */
    public function edit(ProgramReservation $reservation)
    {
        $detail = $this->rosterService->getRosterDetail($reservation->id);
        $filters = $this->rosterService->getFilterOptions();

        return $this->view('backoffice.rosters.edit', [
            'program' => $detail['program'],
            'rosterList' => $detail['roster_list'],
            'lotteryStatus' => $detail['lottery_status'],
            'filters' => $filters,
        ]);
    }

    /**
     * 추첨 실행
     */
    public function lottery(ProgramReservation $reservation, Request $request)
    {
        $result = $this->rosterService->runLottery($reservation->id);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * SMS/메일 발송 (추후 구현)
     */
    public function sendSmsEmail(ProgramReservation $reservation, Request $request)
    {
        return redirect()->back()->with('info', 'SMS/메일 발송 기능은 추후 구현 예정입니다.');
    }

    /**
     * 명단 선택 SMS 발송
     */
    public function sendSms(Request $request)
    {
        $request->validate([
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'required|integer|exists:program_reservations,id',
            'message_type' => 'required|in:sms,email',
            'content' => 'required|string|max:2000',
        ]);

        $reservationIds = $request->input('reservation_ids', []);
        $messageType = $request->input('message_type');
        $content = $request->input('content');

        // EMAIL은 아직 구현하지 않음
        if ($messageType === 'email') {
            return response()->json([
                'success' => false,
                'message' => '메일 발송 기능은 준비 중입니다.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 명단 정보 가져오기 (제목용)
            $reservations = ProgramReservation::whereIn('id', $reservationIds)->get();
            $programNames = $reservations->pluck('program_name')->toArray();
            $title = '명단 발송: ' . implode(', ', array_slice($programNames, 0, 3));
            if (count($programNames) > 3) {
                $title .= ' 외 ' . (count($programNames) - 3) . '개';
            }

            // MailSmsMessage 생성
            $mailSmsMessage = MailSmsMessage::create([
                'message_type' => MailSmsMessage::TYPE_SMS,
                'title' => $title,
                'content' => $content,
                'writer_id' => auth()->id(),
                'member_group_id' => null,
                'status' => MailSmsMessage::STATUS_SENDING,
                'send_requested_at' => Carbon::now(),
                'send_started_at' => Carbon::now(),
            ]);

            // 개인 신청 회원 정보 수집 (신청 명단의 연락처 사용)
            $individualApplications = IndividualApplication::whereIn('program_reservation_id', $reservationIds)
                ->with('member')
                ->where(function($q) {
                    $q->whereNotNull('applicant_contact')
                      ->orWhereNotNull('guardian_contact');
                })
                ->whereHas('member', function($q) {
                    $q->where('sms_consent', true);
                })
                ->get();

            $recipients = [];
            $usedContacts = []; // 중복 체크용 (연락처 기준)

            foreach ($individualApplications as $application) {
                // 신청 명단의 연락처 사용 (applicant_contact 우선, 없으면 guardian_contact)
                $contact = $application->applicant_contact ?? $application->guardian_contact;
                
                if ($contact && !in_array($contact, $usedContacts)) {
                    $usedContacts[] = $contact;
                    $recipients[] = [
                        'member_id' => $application->member_id,
                        'member_name' => $application->applicant_name ?? $application->member?->name,
                        'member_email' => $application->member?->email,
                        'member_contact' => $contact, // 신청 명단의 연락처 사용
                    ];
                }
            }

            // 단체 신청 회원 정보 수집 (신청 명단의 연락처 사용)
            $groupApplications = GroupApplication::whereIn('program_reservation_id', $reservationIds)
                ->whereNotNull('applicant_contact')
                ->get();

            foreach ($groupApplications as $groupApplication) {
                $contact = $groupApplication->applicant_contact;
                
                // 중복 체크 (같은 연락처가 이미 있으면 스킵)
                if ($contact && !in_array($contact, $usedContacts)) {
                    $usedContacts[] = $contact;
                    $recipients[] = [
                        'member_id' => $groupApplication->member_id,
                        'member_name' => $groupApplication->applicant_name,
                        'member_email' => $groupApplication->member?->email,
                        'member_contact' => $contact, // 신청 명단의 연락처 사용
                    ];
                }
            }

            if (empty($recipients)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '발송할 연락처가 없습니다. (SMS 수신동의한 회원이 없거나 연락처가 없습니다.)',
                ], 400);
            }

            // MailSmsMessageMember 생성
            $messageMembers = [];
            foreach ($recipients as $recipient) {
                $messageMembers[] = MailSmsMessageMember::create([
                    'mail_sms_message_id' => $mailSmsMessage->id,
                    'member_id' => $recipient['member_id'],
                    'member_name' => $recipient['member_name'],
                    'member_email' => $recipient['member_email'],
                    'member_contact' => $recipient['member_contact'],
                    'is_selected' => true,
                ]);
            }

            // SMS 발송 및 로그 저장
            $now = Carbon::now();
            $sendSequence = 1;
            $successCount = 0;
            $failureCount = 0;
            $logs = [];

            foreach ($messageMembers as $messageMember) {
                $phone = $messageMember->member_contact;
                
                if (empty($phone)) {
                    $logs[] = [
                        'send_sequence' => $sendSequence,
                        'mail_sms_message_id' => $mailSmsMessage->id,
                        'mail_sms_message_member_id' => $messageMember->id,
                        'member_id' => $messageMember->member_id,
                        'member_name' => $messageMember->member_name,
                        'member_email' => $messageMember->member_email,
                        'member_contact' => $phone,
                        'result_status' => 'failure',
                        'sent_at' => null,
                        'response_code' => 'NO_PHONE',
                        'response_message' => '전화번호가 없습니다.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $failureCount++;
                    continue;
                }

                try {
                    $result = $this->smsKakaoApiService->sendSms($phone, $content);
                    
                    $logs[] = [
                        'send_sequence' => $sendSequence,
                        'mail_sms_message_id' => $mailSmsMessage->id,
                        'mail_sms_message_member_id' => $messageMember->id,
                        'member_id' => $messageMember->member_id,
                        'member_name' => $messageMember->member_name,
                        'member_email' => $messageMember->member_email,
                        'member_contact' => $phone,
                        'result_status' => $result['success'] ? 'success' : 'failure',
                        'sent_at' => $result['success'] ? $now : null,
                        'response_code' => $result['response_code'] ?? 'UNKNOWN',
                        'response_message' => $result['response_message'] ?? '발송 실패',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failureCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('SMS 발송 중 오류 발생', [
                        'message_id' => $mailSmsMessage->id,
                        'member_id' => $messageMember->member_id,
                        'phone' => $phone,
                        'error' => $e->getMessage(),
                    ]);

                    $logs[] = [
                        'send_sequence' => $sendSequence,
                        'mail_sms_message_id' => $mailSmsMessage->id,
                        'mail_sms_message_member_id' => $messageMember->id,
                        'member_id' => $messageMember->member_id,
                        'member_name' => $messageMember->member_name,
                        'member_email' => $messageMember->member_email,
                        'member_contact' => $phone,
                        'result_status' => 'failure',
                        'sent_at' => null,
                        'response_code' => 'EXCEPTION',
                        'response_message' => '발송 중 오류가 발생했습니다: ' . $e->getMessage(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $failureCount++;
                }
            }

            // MailSmsLog 일괄 저장
            MailSmsLog::insert($logs);

            // MailSmsMessage 업데이트
            $mailSmsMessage->update([
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'send_completed_at' => $now,
                'last_error_message' => $failureCount > 0 ? "{$failureCount}건 발송 실패" : null,
            ]);

            DB::commit();

            $message = "총 {$successCount}건 발송 완료";
            if ($failureCount > 0) {
                $message .= ", 실패 {$failureCount}건";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'stats' => [
                    'total' => count($recipients),
                    'success' => $successCount,
                    'failure' => $failureCount,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS 발송 중 오류 발생', [
                'reservation_ids' => $reservationIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 명단 다운로드
     */
    public function download(ProgramReservation $reservation, Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        $selectedColumns = $request->input('columns', []);

        if (empty($selectedColumns)) {
            return redirect()->back()->with('error', '다운로드할 항목을 선택해주세요.');
        }

        try {
            return $this->rosterService->downloadRoster($reservation->id, $selectedIds, $selectedColumns);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '다운로드 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 명단 리스트 다운로드
     */
    public function downloadList(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);

        try {
            return $this->rosterService->downloadRosterList($selectedIds);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '다운로드 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 명단에 회원 추가
     */
    public function storeMembers(ProgramReservation $reservation, Request $request)
    {
        $memberIds = $request->input('member_ids', []);

        if (empty($memberIds)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => '추가할 회원을 선택해주세요.'], 400);
            }
            return redirect()->back()->with('error', '추가할 회원을 선택해주세요.');
        }

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errorMessages = [];
            $groupApplication = null; // 단체 신청용 변수

            foreach ($memberIds as $memberId) {
                $member = Member::find($memberId);
                if (!$member) {
                    $errorMessages[] = "회원 ID {$memberId}를 찾을 수 없습니다.";
                    continue;
                }

                if ($reservation->application_type === 'individual') {
                    // 개인 신청
                    try {
                        $this->programReservationService->createIndividualApplication(
                            $reservation,
                            [
                                'member_id' => $member->id,
                                'applicant_name' => $member->name,
                                'applicant_school_name' => $member->school_name,
                                'applicant_grade' => $member->grade,
                                'applicant_class' => $member->class_number,
                                'applicant_contact' => $member->contact ?? '',
                                'guardian_contact' => $member->parent_contact ?? '',
                            ],
                            $member,
                            true // allowAdminOverride
                        );
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorMessages[] = "{$member->name}: " . $e->getMessage();
                    }
                } else {
                    // 단체 신청
                    // 첫 번째 회원이거나 기존 GroupApplication이 없으면 새로 생성
                    if (!$groupApplication) {
                        $groupApplication = GroupApplication::where('program_reservation_id', $reservation->id)
                            ->first();

                        if (!$groupApplication) {
                            // 새 GroupApplication 생성 (첫 번째 회원 기준)
                            $groupApplication = $this->programReservationService->createGroupApplication(
                                $reservation,
                                [
                                    'member_id' => $member->id,
                                    'applicant_name' => $member->name,
                                    'school_name' => $member->school_name,
                                    'applicant_contact' => $member->contact ?? '',
                                    'applicant_count' => count($memberIds), // 전체 회원 수
                                ]
                            );
                        }
                    }

                    // Participant 추가
                    GroupApplicationParticipant::create([
                        'group_application_id' => $groupApplication->id,
                        'name' => $member->name,
                        'grade' => $member->grade,
                        'class' => $member->class_number,
                        'birthday' => $member->birth_date,
                    ]);

                    $successCount++;
                }
            }

            // 단체 신청의 경우 GroupApplication의 applicant_count 업데이트
            if ($reservation->application_type === 'group' && $groupApplication) {
                $actualCount = $groupApplication->participants()->count();
                $groupApplication->update(['applicant_count' => $actualCount]);
            }

            DB::commit();

            $message = "{$successCount}명의 회원이 추가되었습니다.";
            if (!empty($errorMessages)) {
                $message .= "\n오류: " . implode(', ', $errorMessages);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => '회원 추가 중 오류가 발생했습니다: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', '회원 추가 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 명단에서 회원 삭제
     */
    public function removeMembers(ProgramReservation $reservation, Request $request)
    {
        $memberIds = $request->input('member_ids', []);
        $participantIds = $request->input('participant_ids', []);

        if ($reservation->application_type === 'individual') {
            // 개인 신청: member_ids는 실제로 application_id 배열
            if (empty($memberIds)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => '삭제할 회원을 선택해주세요.'], 400);
                }
                return redirect()->back()->with('error', '삭제할 회원을 선택해주세요.');
            }
        } else {
            // 단체 신청
            if (empty($participantIds)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => '삭제할 회원을 선택해주세요.'], 400);
                }
                return redirect()->back()->with('error', '삭제할 회원을 선택해주세요.');
            }
        }

        try {
            DB::beginTransaction();

            if ($reservation->application_type === 'individual') {
                // 개인 신청 삭제: member_ids는 실제로 application_id 배열
                $deletedCount = IndividualApplication::where('program_reservation_id', $reservation->id)
                    ->whereIn('id', $memberIds)  // application_id로 삭제
                    ->delete();

                // applied_count 감소
                if (!$reservation->is_unlimited_capacity && $deletedCount > 0) {
                    $reservation->decrement('applied_count', $deletedCount);
                }
            } else {
                // 단체 신청 삭제
                // 명단에서 participant_id를 받음 (data-member-id에 participant id가 들어있음)
                $participantIds = $request->input('participant_ids', $memberIds);

                $deletedCount = GroupApplicationParticipant::whereIn('id', $participantIds)->delete();

                // GroupApplication의 applicant_count 업데이트
                $groupApplications = GroupApplication::where('program_reservation_id', $reservation->id)
                    ->with('participants')
                    ->get();

                foreach ($groupApplications as $groupApplication) {
                    $participantCount = $groupApplication->participants()->count();
                    if ($participantCount === 0) {
                        // 참가자가 없으면 GroupApplication도 삭제
                        $groupApplication->delete();
                    } else {
                        // 참가자 수 업데이트
                        $groupApplication->update(['applicant_count' => $participantCount]);
                    }
                }

                // reservation의 applied_count 업데이트
                if (!$reservation->is_unlimited_capacity && $deletedCount > 0) {
                    $reservation->decrement('applied_count', $deletedCount);
                }
            }

            DB::commit();

            $message = "{$deletedCount}명의 회원이 삭제되었습니다.";
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => '회원 삭제 중 오류가 발생했습니다: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', '회원 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}