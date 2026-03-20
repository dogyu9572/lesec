<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\RosterService;
use App\Services\Backoffice\SmsKakaoApiService;
use App\Services\Backoffice\MailSmsService;
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
    protected MailSmsService $mailSmsService;

    public function __construct(
        RosterService $rosterService,
        ProgramReservationService $programReservationService,
        SmsKakaoApiService $smsKakaoApiService,
        MailSmsService $mailSmsService
    ) {
        $this->rosterService = $rosterService;
        $this->programReservationService = $programReservationService;
        $this->smsKakaoApiService = $smsKakaoApiService;
        $this->mailSmsService = $mailSmsService;
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
     * 명단 선택 SMS/메일 발송
     */
    public function sendSms(Request $request)
    {
        try {
            $request->validate([
                'message_type' => 'required|in:sms,email',
                'content' => 'required|string|max:2000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력값을 확인해주세요.',
                'errors' => $e->errors(),
            ], 422);
        }

        $messageType = $request->input('message_type');
        $content = $request->input('content', '');

        if (empty($content)) {
            return response()->json([
                'success' => false,
                'message' => '메시지 내용을 입력해주세요.',
            ], 422);
        }

        // 명단 편집 페이지인지 확인 (reservation_id와 application_ids/participant_ids)
        $reservationId = $request->input('reservation_id');
        $applicationType = $request->input('application_type');
        $applicationIdsInput = $request->input('application_ids', []);
        $participantIdsInput = $request->input('participant_ids', []);

        // reservation_ids 처리 (명단 목록 페이지용)
        $reservationIdsInput = $request->input('reservation_ids', []);
        if (!is_array($reservationIdsInput)) {
            $reservationIdsInput = [];
        }
        $reservationIds = array_map(function ($id) {
            return (int) $id;
        }, array_filter($reservationIdsInput, function ($id) {
            return $id !== null && $id !== '' && is_numeric($id);
        }));

        try {
            // 명단 편집 페이지인 경우 (선택된 신청자 ID로 발송)
            if ($reservationId) {
                // application_ids 또는 participant_ids를 정수 배열로 변환
                $applicationIds = [];
                $participantIds = [];

                if (!empty($applicationIdsInput)) {
                    if (!is_array($applicationIdsInput)) {
                        $applicationIdsInput = explode(',', $applicationIdsInput);
                    }
                    $applicationIds = array_map(function ($id) {
                        return (int) $id;
                    }, array_filter($applicationIdsInput, function ($id) {
                        return $id !== null && $id !== '' && is_numeric($id);
                    }));
                }

                if (!empty($participantIdsInput)) {
                    if (!is_array($participantIdsInput)) {
                        $participantIdsInput = explode(',', $participantIdsInput);
                    }
                    $participantIds = array_map(function ($id) {
                        return (int) $id;
                    }, array_filter($participantIdsInput, function ($id) {
                        return $id !== null && $id !== '' && is_numeric($id);
                    }));
                }

                if (empty($applicationIds) && empty($participantIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => '발송할 명단을 선택해주세요.',
                    ], 422);
                }

                // reservation_id 검증
                $reservation = ProgramReservation::find($reservationId);
                if (!$reservation) {
                    return response()->json([
                        'success' => false,
                        'message' => '유효하지 않은 프로그램 ID입니다.',
                    ], 422);
                }

                $title = '명단 발송: ' . $reservation->program_name;
                $reservationIds = [$reservationId];
            } else {
                // 명단 목록 페이지인 경우 (reservation_ids로 발송)
                if (empty($reservationIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => '발송할 명단을 선택해주세요.',
                    ], 422);
                }

                // 존재하는 reservation_id인지 확인
                $existingIds = ProgramReservation::whereIn('id', $reservationIds)->pluck('id')->toArray();
                $invalidIds = array_diff($reservationIds, $existingIds);
                if (!empty($invalidIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => '유효하지 않은 명단 ID가 있습니다: ' . implode(', ', $invalidIds),
                    ], 422);
                }

                // 명단 정보 가져오기 (제목용)
                $reservations = ProgramReservation::whereIn('id', $reservationIds)->get();
                $programNames = $reservations->pluck('program_name')->toArray();
                $title = '명단 발송: ' . implode(', ', array_slice($programNames, 0, 3));
                if (count($programNames) > 3) {
                    $title .= ' 외 ' . (count($programNames) - 3) . '개';
                }

                $applicationIds = [];
                $participantIds = [];
            }

            // 명단에서 발송할 때는 신청 명단의 연락처를 사용해야 하므로 직접 처리
            DB::beginTransaction();
            try {
                // MailSmsMessage 생성
                $mailSmsMessage = MailSmsMessage::create([
                    'message_type' => $messageType,
                    'title' => $title,
                    'content' => $content,
                    'writer_id' => auth()->id(),
                    'member_group_id' => null,
                    'status' => MailSmsMessage::STATUS_PREPARED,
                ]);

                // 수신자 정보 수집 (신청 명단의 연락처 사용)
                $recipients = [];
                $usedContacts = [];

                if ($messageType === 'sms') {
                    // SMS: 개인 신청 회원 정보 수집
                    $individualQuery = IndividualApplication::whereIn('program_reservation_id', $reservationIds)
                        ->with('member')
                        ->where(function ($q) {
                            $q->whereNotNull('applicant_contact')
                                ->orWhereNotNull('guardian_contact');
                        })
                        ->whereHas('member', function ($q) {
                            $q->where('sms_consent', true);
                        });

                    // 선택된 신청자만 필터링
                    if (!empty($applicationIds)) {
                        $individualQuery->whereIn('id', $applicationIds);
                    }

                    $individualApplications = $individualQuery->get();

                    foreach ($individualApplications as $application) {
                        $contact = $application->applicant_contact ?? $application->guardian_contact;
                        if ($contact && !in_array($contact, $usedContacts)) {
                            $usedContacts[] = $contact;
                            $recipients[] = [
                                'member_id' => $application->member_id,
                                'member_name' => $application->applicant_name ?? $application->member?->name,
                                'member_email' => $application->member?->email,
                                'member_contact' => $contact,
                            ];
                        }
                    }

                    // 단체 신청 회원 정보 수집
                    if (!empty($participantIds)) {
                        // participant_ids로 GroupApplicationParticipant를 찾고, 그들의 group_application_id를 가져옴
                        $groupApplicationIds = \App\Models\GroupApplicationParticipant::whereIn('id', $participantIds)
                            ->pluck('group_application_id')
                            ->unique()
                            ->toArray();

                        $groupApplications = GroupApplication::whereIn('id', $groupApplicationIds)
                            ->whereIn('program_reservation_id', $reservationIds)
                            ->whereNotNull('applicant_contact')
                            ->whereHas('member', function ($q) {
                                $q->where('sms_consent', true);
                            })
                            ->get();
                    } else {
                        $groupApplications = GroupApplication::whereIn('program_reservation_id', $reservationIds)
                            ->whereNotNull('applicant_contact')
                            ->whereHas('member', function ($q) {
                                $q->where('sms_consent', true);
                            })
                            ->get();
                    }

                    foreach ($groupApplications as $groupApplication) {
                        $contact = $groupApplication->applicant_contact;
                        if ($contact && !in_array($contact, $usedContacts)) {
                            $usedContacts[] = $contact;
                            $recipients[] = [
                                'member_id' => $groupApplication->member_id,
                                'member_name' => $groupApplication->applicant_name,
                                'member_email' => $groupApplication->member?->email,
                                'member_contact' => $contact,
                            ];
                        }
                    }
                } else {
                    // EMAIL: 개인 신청 회원 정보 수집
                    $individualQuery = IndividualApplication::whereIn('program_reservation_id', $reservationIds)
                        ->with('member')
                        ->whereHas('member', function ($q) {
                            $q->whereNotNull('email')
                                ->where('email_consent', true);
                        });

                    // 선택된 신청자만 필터링
                    if (!empty($applicationIds)) {
                        $individualQuery->whereIn('id', $applicationIds);
                    }

                    $individualApplications = $individualQuery->get();

                    foreach ($individualApplications as $application) {
                        if ($application->member_id && !in_array($application->member_id, array_column($recipients, 'member_id'))) {
                            $recipients[] = [
                                'member_id' => $application->member_id,
                                'member_name' => $application->applicant_name ?? $application->member?->name,
                                'member_email' => $application->member?->email,
                                'member_contact' => $application->member?->contact,
                            ];
                        }
                    }

                    // 단체 신청 회원 정보 수집
                    if (!empty($participantIds)) {
                        // participant_ids로 GroupApplicationParticipant를 찾고, 그들의 group_application_id를 가져옴
                        $groupApplicationIds = \App\Models\GroupApplicationParticipant::whereIn('id', $participantIds)
                            ->pluck('group_application_id')
                            ->unique()
                            ->toArray();

                        $groupApplications = GroupApplication::whereIn('id', $groupApplicationIds)
                            ->whereIn('program_reservation_id', $reservationIds)
                            ->whereHas('member', function ($q) {
                                $q->whereNotNull('email')
                                    ->where('email_consent', true);
                            })
                            ->get();
                    } else {
                        $groupApplications = GroupApplication::whereIn('program_reservation_id', $reservationIds)
                            ->whereHas('member', function ($q) {
                                $q->whereNotNull('email')
                                    ->where('email_consent', true);
                            })
                            ->get();
                    }

                    foreach ($groupApplications as $groupApplication) {
                        if ($groupApplication->member_id && !in_array($groupApplication->member_id, array_column($recipients, 'member_id'))) {
                            $recipients[] = [
                                'member_id' => $groupApplication->member_id,
                                'member_name' => $groupApplication->applicant_name,
                                'member_email' => $groupApplication->member?->email,
                                'member_contact' => $groupApplication->member?->contact,
                            ];
                        }
                    }
                }

                if (empty($recipients)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => '발송할 대상이 없습니다.',
                    ], 400);
                }

                // MailSmsMessageMember 생성
                $now = Carbon::now();
                $insertRows = [];
                foreach ($recipients as $recipient) {
                    $insertRows[] = [
                        'mail_sms_message_id' => $mailSmsMessage->id,
                        'member_id' => $recipient['member_id'],
                        'member_name' => $recipient['member_name'],
                        'member_email' => $recipient['member_email'],
                        'member_contact' => $recipient['member_contact'],
                        'is_selected' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                MailSmsMessageMember::insert($insertRows);

                DB::commit();

                // 발송 요청
                $message = $this->mailSmsService->requestSend($mailSmsMessage->fresh());

                $stats = [
                    'total' => count($recipients),
                    'success' => $message->success_count ?? 0,
                    'failure' => $message->failure_count ?? 0,
                ];

                $resultMessage = "총 {$stats['success']}건 발송 완료";
                if ($stats['failure'] > 0) {
                    $resultMessage .= ", 실패 {$stats['failure']}건";
                }

                return response()->json([
                    'success' => true,
                    'message' => $resultMessage,
                    'stats' => $stats,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('SMS/메일 발송 중 오류 발생', [
                'reservation_ids' => $reservationIds,
                'message_type' => $messageType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '발송 중 오류가 발생했습니다: ' . $e->getMessage(),
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
            $capacityUpdated = false; // 정원 업데이트 여부
            $oldCapacity = null; // 이전 정원
            $newCapacity = null; // 새 정원

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
                        // 정원 부족 예외인 경우 정원을 자동으로 늘려서 저장
                        if (strpos($e->getMessage(), '잔여 정원이 부족합니다') !== false && !$reservation->is_unlimited_capacity) {
                            // 정원 업데이트는 한 번만 실행
                            if (!$capacityUpdated) {
                                // 현재 신청 인원 수 확인
                                $reservation->refresh();
                                $currentAppliedCount = $reservation->applied_count_display;
                                $currentCapacity = $reservation->capacity ?? 0;

                                // 남은 회원 수 계산 (아직 처리하지 않은 회원 수, 현재 처리 중인 회원 포함)
                                $remainingMemberCount = count($memberIds) - $successCount;

                                // 필요한 만큼 정원 증가 (현재 신청 인원 + 남은 회원 수)
                                $calculatedCapacity = $currentAppliedCount + $remainingMemberCount;

                                // 현재 정원보다 작으면 현재 정원 유지
                                if ($calculatedCapacity < $currentCapacity) {
                                    $calculatedCapacity = $currentCapacity;
                                }

                                // 정원 업데이트
                                $oldCapacity = $currentCapacity;
                                $reservation->update(['capacity' => $calculatedCapacity]);
                                $newCapacity = $calculatedCapacity;
                                $capacityUpdated = true;

                                // reservation 객체 새로고침
                                $reservation->refresh();
                            }

                            // 다시 시도
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
                            } catch (\Exception $retryException) {
                                $errorMessages[] = "{$member->name}: " . $retryException->getMessage();
                            }
                        } else {
                            $errorMessages[] = "{$member->name}: " . $e->getMessage();
                        }
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
            if ($capacityUpdated && $oldCapacity !== null && $newCapacity !== null) {
                $message .= "\n정원이 {$oldCapacity}명에서 {$newCapacity}명으로 자동 증가되었습니다.";
            }
            if (!empty($errorMessages)) {
                $message .= "\n오류: " . implode(', ', $errorMessages);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'capacity_updated' => $capacityUpdated,
                    'old_capacity' => $oldCapacity,
                    'new_capacity' => $newCapacity,
                ]);
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
