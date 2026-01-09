<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\RosterService;
use App\Services\ProgramReservationService;
use App\Models\ProgramReservation;
use App\Models\Member;
use App\Models\IndividualApplication;
use App\Models\GroupApplication;
use App\Models\GroupApplicationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RosterController extends BaseController
{
    protected RosterService $rosterService;
    protected ProgramReservationService $programReservationService;

    public function __construct(RosterService $rosterService, ProgramReservationService $programReservationService)
    {
        $this->rosterService = $rosterService;
        $this->programReservationService = $programReservationService;
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