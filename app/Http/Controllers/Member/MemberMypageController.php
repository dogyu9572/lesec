<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberUpdateRequest;
use App\Models\GroupApplication;
use App\Models\GroupApplicationParticipant;
use App\Models\IndividualApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class MemberMypageController extends Controller
{
    /**
     * 회원 정보 페이지 표시
     */
    public function show()
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $gNum = "03";
        $sNum = "01";
        $gName = "마이페이지";
        $sName = "회원정보";
        
        $emailParts = $this->parseEmail($member->email);
        
        return view('mypage.member', compact('gNum', 'sNum', 'gName', 'sName', 'member', 'emailParts'));
    }

    /**
     * 회원 정보 업데이트
     */
    public function update(MemberUpdateRequest $request)
    {
        $member = Auth::guard('member')->user();
        
        $data = $request->validated();
        
        if (!empty($data['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        $notificationAgree = $data['notification_agree'] ?? false;
        unset($data['notification_agree']);
        
        $data['email_consent'] = $notificationAgree;
        $data['sms_consent'] = $notificationAgree;
        
        unset($data['current_password'], $data['password_confirmation']);
        
        $member->fill($data);
        $member->save();
        
        return redirect()->route('mypage.member')
            ->with('success', '회원 정보가 수정되었습니다.');
    }

    /**
     * 단체 신청내역 목록
     */
    public function groupApplicationList()
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $gNum = "03";
        $sNum = "02";
        $gName = "마이페이지";
        $sName = "나의 신청내역(단체)";

        $applications = GroupApplication::where('member_id', $member->id)
            ->with(['reservation'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('mypage.application_list', compact('gNum', 'sNum', 'gName', 'sName', 'applications'));
    }

    /**
     * 개인 신청내역 목록
     */
    public function individualApplicationList()
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $gNum = "03";
        $sNum = "02";
        $gName = "마이페이지";
        $sName = "나의 신청내역";

        $applications = IndividualApplication::where('member_id', $member->id)
            ->with(['reservation', 'member'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('mypage.application_indi_list', compact('gNum', 'sNum', 'gName', 'sName', 'applications'));
    }

    /**
     * 개인 신청내역 상세
     */
    public function individualApplicationShow($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = IndividualApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->with(['reservation', 'member'])
            ->firstOrFail();

        $gNum = "03";
        $sNum = "02";
        $gName = "마이페이지";
        $sName = "나의 신청내역";

        return view('mypage.application_indi_view', compact('gNum', 'sNum', 'gName', 'sName', 'application'));
    }

    /**
     * 단체 신청내역 상세
     */
    public function groupApplicationShow($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->with(['reservation', 'participants'])
            ->firstOrFail();

        $gNum = "03";
        $sNum = "02";
        $gName = "마이페이지";
        $sName = "나의 신청내역(단체)";

        return view('mypage.application_view', compact('gNum', 'sNum', 'gName', 'sName', 'application'));
    }

    /**
     * 단체 신청내역 수정
     */
    public function groupApplicationWrite($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->with(['reservation', 'participants'])
            ->firstOrFail();

        $gNum = "03";
        $sNum = "02";
        $gName = "마이페이지";
        $sName = "나의 신청내역(단체)";

        return view('mypage.application_write', compact('gNum', 'sNum', 'gName', 'sName', 'application'));
    }

    /**
     * 단체 신청내역 명단 저장
     */
    public function groupApplicationWriteUpdate(Request $request, $id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $request->validate([
            'payment_method' => 'nullable|in:bank_transfer,on_site_card,online_card',
            'participants' => 'required|array',
            'participants.*.name' => 'required|string|max:50',
            'participants.*.grade' => 'required|integer|min:1|max:3',
            'participants.*.class' => 'required|string|max:20',
            'participants.*.birthday' => 'nullable|string|max:8',
            'privacy_agree' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // 결제방법 업데이트
            if ($request->has('payment_method')) {
                $application->payment_method = $request->input('payment_method');
                $application->save();
            }

            // 기존 명단 ID 수집
            $existingIds = collect($request->input('participants', []))
                ->pluck('id')
                ->filter()
                ->toArray();

            // 기존 명단 중 삭제된 항목 제거
            GroupApplicationParticipant::where('group_application_id', $application->id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            // 명단 저장/수정
            foreach ($request->input('participants', []) as $participantData) {
                if (empty($participantData['name']) || empty($participantData['grade']) || empty($participantData['class'])) {
                    continue;
                }

                $birthday = null;
                if (!empty($participantData['birthday'])) {
                    $birthdayStr = $participantData['birthday'];
                    if (strlen($birthdayStr) === 8 && is_numeric($birthdayStr)) {
                        $birthday = \Carbon\Carbon::createFromFormat('Ymd', $birthdayStr)->format('Y-m-d');
                    }
                }

                if (!empty($participantData['id'])) {
                    // 수정
                    $participant = GroupApplicationParticipant::where('id', $participantData['id'])
                        ->where('group_application_id', $application->id)
                        ->first();
                    
                    if ($participant) {
                        $participant->update([
                            'name' => $participantData['name'],
                            'grade' => $participantData['grade'],
                            'class' => $participantData['class'],
                            'birthday' => $birthday,
                        ]);
                    }
                } else {
                    // 신규 추가
                    GroupApplicationParticipant::create([
                        'group_application_id' => $application->id,
                        'name' => $participantData['name'],
                        'grade' => $participantData['grade'],
                        'class' => $participantData['class'],
                        'birthday' => $birthday,
                    ]);
                }
            }

            // 신청 인원 업데이트
            $participantCount = GroupApplicationParticipant::where('group_application_id', $application->id)->count();
            $application->applicant_count = $participantCount;
            $application->save();

            DB::commit();

            return redirect()->route('mypage.application_write', $application->id)
                ->with('success', '명단이 저장되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => '명단 저장 중 오류가 발생했습니다.'])
                ->withInput();
        }
    }

    /**
     * CSV 샘플 파일 다운로드
     */
    public function groupApplicationWriteSample($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $csvData = [];
        $csvData[] = ['이름', '학년', '반', '생년월일'];
        $csvData[] = ['홍길동', '1', '1', '20010101'];
        $csvData[] = ['김철수', '2', '3', '20020202'];

        $filename = '명단_샘플_' . date('Ymd') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            // BOM 추가 (한글 깨짐 방지)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * CSV 일괄 업로드
     */
    public function groupApplicationWriteUpload(Request $request, $id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return response()->json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // 첫 번째 줄(헤더) 건너뛰기
            $header = fgetcsv($handle);
            
            $participants = [];
            $lineNumber = 1;
            
            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                if (count($data) < 3) {
                    continue;
                }

                $name = trim($data[0] ?? '');
                $grade = trim($data[1] ?? '');
                $class = trim($data[2] ?? '');
                $birthday = trim($data[3] ?? '');

                if (empty($name) || empty($grade) || empty($class)) {
                    continue;
                }

                $birthdayDate = null;
                if (!empty($birthday)) {
                    $birthdayStr = preg_replace('/[^0-9]/', '', $birthday);
                    if (strlen($birthdayStr) === 8 && is_numeric($birthdayStr)) {
                        try {
                            $birthdayDate = \Carbon\Carbon::createFromFormat('Ymd', $birthdayStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $birthdayDate = null;
                        }
                    }
                }

                $participants[] = [
                    'group_application_id' => $application->id,
                    'name' => $name,
                    'grade' => (int)$grade,
                    'class' => $class,
                    'birthday' => $birthdayDate,
                ];
            }
            
            fclose($handle);

            if (empty($participants)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => '업로드할 명단이 없습니다.'], 400);
            }

            // 기존 명단은 유지하고 새 명단을 추가로 삽입
            foreach ($participants as $participant) {
                GroupApplicationParticipant::create($participant);
            }

            // 신청 인원 업데이트: 전체 인원 기준으로 반영
            $totalCount = GroupApplicationParticipant::where('group_application_id', $application->id)->count();
            $application->applicant_count = $totalCount;
            $application->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($participants) . '명의 명단이 추가로 업로드되었습니다.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'CSV 파일 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 개인 신청 취소
     */
    public function cancelIndividualApplication($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = IndividualApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        // 이미 취소된 신청인지 체크
        if ($application->payment_status === 'cancelled') {
            return redirect()->route('mypage.application_indi_list')
                ->withErrors(['cancel' => '이미 취소된 신청입니다.']);
        }

        // 취소 불가능한 상태 체크
        if ($application->draw_result === 'fail' || $application->payment_status === 'refunded') {
            return redirect()->route('mypage.application_indi_list')
                ->withErrors(['cancel' => '취소할 수 없는 신청입니다.']);
        }

        // 신청 취소 처리 (상태 변경)
        $application->payment_status = 'cancelled';
        $application->save();

        // 신청 취소 시 프로그램 신청 인원 감소
        if ($application->reservation && !$application->reservation->is_unlimited_capacity) {
            $application->reservation->decrement('applied_count', 1);
        }

        return redirect()->route('mypage.application_indi_list')
            ->with('success', '신청이 취소되었습니다.');
    }

    /**
     * 단체 신청 취소
     */
    public function cancelGroupApplication($id)
    {
        $member = Auth::guard('member')->user();
        
        if (!$member) {
            return redirect()->route('member.login')
                ->withErrors(['auth' => '로그인이 필요합니다.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        // 이미 취소된 신청인지 체크
        if ($application->payment_status === 'cancelled') {
            return redirect()->route('mypage.application_list')
                ->withErrors(['cancel' => '이미 취소된 신청입니다.']);
        }

        // 신청 취소 처리 (상태 변경)
        $application->payment_status = 'cancelled';
        $application->save();

        // 신청 취소 시 프로그램 신청 인원 감소
        if ($application->reservation && !$application->reservation->is_unlimited_capacity) {
            $application->reservation->decrement('applied_count', $application->applicant_count);
        }

        return redirect()->route('mypage.application_list')
            ->with('success', '신청이 취소되었습니다.');
    }

    /**
     * 이메일 파싱 (ID와 도메인 분리)
     */
    private function parseEmail(?string $email): array
    {
        if (empty($email)) {
            return ['id' => '', 'domain' => '', 'is_custom' => false];
        }

        $parts = explode('@', $email, 2);
        
        if (count($parts) !== 2) {
            return ['id' => $email, 'domain' => '', 'is_custom' => true];
        }

        $emailId = $parts[0];
        $emailDomain = $parts[1];
        
        $commonDomains = ['naver.com', 'gmail.com', 'daum.net', 'nate.com'];
        $isCustom = !in_array($emailDomain, $commonDomains, true);

        return [
            'id' => $emailId,
            'domain' => $isCustom ? 'custom' : $emailDomain,
            'custom_domain' => $isCustom ? $emailDomain : '',
            'is_custom' => $isCustom,
        ];
    }
}

