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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        
        // validation 실패 시 자동으로 리다이렉트되므로, 여기까지 오면 validation 통과
        \Log::info('회원 정보 수정 시작', ['member_id' => $member->id]);
        
        try {
            $data = $request->validated();
            
            \Log::info('회원 정보 수정 시작', ['member_id' => $member->id, 'data_keys' => array_keys($data)]);
            
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
            
            \Log::info('fill 전 데이터', ['original_city' => $member->city, 'original_district' => $member->district, 'new_city' => $data['city'] ?? null, 'new_district' => $data['district'] ?? null]);
            
            // fill() 대신 직접 할당하여 확실히 저장
            foreach ($data as $key => $value) {
                if (in_array($key, $member->getFillable())) {
                    $member->$key = $value;
                }
            }
            
            \Log::info('할당 후 변경사항', ['isDirty' => $member->isDirty(), 'dirty' => $member->getDirty()]);
            
            // 변경사항이 있으면 저장
            if ($member->isDirty()) {
                $saved = $member->save();
                \Log::info('회원 정보 저장 완료', ['saved' => $saved]);
            } else {
                \Log::warning('변경된 데이터가 없어 저장하지 않음', ['data' => $data]);
            }
            
            return redirect()->route('mypage.member')
                ->with('success', '회원 정보가 수정되었습니다.');
        } catch (\Exception $e) {
            \Log::error('회원 정보 수정 오류', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('mypage.member')
                ->withErrors(['error' => '회원 정보 수정 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
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
     * 엑셀 샘플 파일 다운로드
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

        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \InvalidArgumentException('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다.');
        }

        $filename = '명단_샘플_' . date('Ymd') . '.xlsx';
        $headers = ['이름', '학년', '반', '생년월일(YYYYMMDD)'];
        $sampleRows = [
            ['홍길동', '1', '1', '20010101'],
            ['김철수', '2', '3', '20020202'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 헤더 작성
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '1', $header);
            $columnIndex++;
        }

        // 헤더 스타일 설정
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // 샘플 데이터 작성
        $rowIndex = 2;
        foreach ($sampleRows as $row) {
            $columnIndex = 1;
            foreach ($row as $cellValue) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $cellAddress = $columnLetter . $rowIndex;
                $sheet->setCellValue($cellAddress, $cellValue);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // 컬럼 너비 자동 조정
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * 엑셀/CSV 일괄 업로드
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
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('csv_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = $file->getRealPath();
            
            $participants = [];
            
            if ($extension === 'csv') {
                // CSV 파일 처리
                $handle = fopen($filePath, 'r');
                if (!$handle) {
                    throw new \Exception('파일을 읽을 수 없습니다.');
                }

                // UTF-8 BOM 제거
                $bom = fread($handle, 3);
                if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                    rewind($handle);
                }
                
                // 첫 번째 줄(헤더) 건너뛰기
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) < 3) {
                        continue;
                    }

                    $participant = $this->processParticipantRow($application->id, $data);
                    if ($participant) {
                        $participants[] = $participant;
                    }
                }
                
                fclose($handle);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // 엑셀 파일 처리
                if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                    throw new \Exception('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다.');
                }

                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $excelRows = $worksheet->toArray();

                // 헤더 제거
                array_shift($excelRows);

                foreach ($excelRows as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    $participant = $this->processParticipantRow($application->id, $row);
                    if ($participant) {
                        $participants[] = $participant;
                    }
                }
            } else {
                throw new \Exception('지원하지 않는 파일 형식입니다.');
            }

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
                'message' => '파일 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 명단 행 데이터 처리
     */
    private function processParticipantRow(int $applicationId, array $data): ?array
    {
        $name = trim($data[0] ?? '');
        $grade = trim($data[1] ?? '');
        $class = trim($data[2] ?? '');
        $birthday = trim($data[3] ?? '');

        if (empty($name) || empty($grade) || empty($class)) {
            return null;
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

        return [
            'group_application_id' => $applicationId,
            'name' => $name,
            'grade' => (int)$grade,
            'class' => $class,
            'birthday' => $birthdayDate,
        ];
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
     * 인쇄 - 견적서
     * 로그인 없이도 접근 가능
     * 여러 개 선택 시 하나의 페이지에서 순차적으로 출력
     */
    public function printEstimate(Request $request)
    {
        $gNum = "print"; $sNum = ""; $gName = "견적서"; $sName = "";

        $estimates = [];

        // 여러 ID 처리 (ids 파라미터)
        $idsParam = $request->query('ids', '');
        if (!empty($idsParam)) {
            $ids = array_filter(array_map('intval', explode(',', $idsParam)));
            if (!empty($ids)) {
                $applications = GroupApplication::query()
                    ->with(['reservation', 'member'])
                    ->whereIn('id', $ids)
                    ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                    ->get();

                foreach ($applications as $application) {
                    $estimates[] = $this->buildEstimateData($application);
                }
            }
        } else {
            // 단일 ID 처리 (id 파라미터 - 기존 호환성 유지)
            $id = (int) $request->query('id', 0);
            if ($id > 0) {
                $application = GroupApplication::query()
                    ->with(['reservation', 'member'])
                    ->where('id', $id)
                    ->first();

                if ($application) {
                    $estimates[] = $this->buildEstimateData($application);
                }
            }
        }

        return view('print.estimate', compact('gNum', 'sNum', 'gName', 'sName', 'estimates'));
    }

    /**
     * 견적서 데이터 생성
     */
    private function buildEstimateData(GroupApplication $application): array
    {
        $educationDate = $application->participation_date
            ? \Carbon\Carbon::parse($application->participation_date)->format('Y.m.d')
            : 'YYYY.MM.DD';

        $programName = optional($application->reservation)->program_name ?? '';
        $applicantCount = (int) ($application->applicant_count ?? 0);
        $unitPrice = (int) ($application->participation_fee ?? 0);
        $amount = $applicantCount * $unitPrice;
        $vat = (int) floor($amount * 0.1);
        $total = $amount + $vat;

        $recipientName = $application->applicant_name ?? '';
        $schoolName = $application->school_name ?? '';
        $phone = $this->formatPhoneNumberSimple($application->applicant_contact ?? '');
        $email = optional($application->member)->email ?? '';

        return [
            'number' => $application->application_number ?? '',
            'date' => now()->format('Y.m.d'),
            'recipient_name' => $recipientName,
            'school_name' => $schoolName,
            'phone' => $phone,
            'email' => $email,
            'items' => [[
                'no' => 1,
                'education_date' => $educationDate,
                'program_name' => $programName,
                'count' => $applicantCount,
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ]],
            'subtotal' => $amount,
            'vat' => $vat,
            'total' => $total,
            'print_date' => now()->format('Y년 m월 d일'),
            'note' => '',
        ];
    }

    private function formatPhoneNumberSimple(?string $digits): string
    {
        if (empty($digits)) return '';
        $only = preg_replace('/[^0-9]/', '', $digits);
        if (strlen($only) === 11) {
            return preg_replace('/(\\d{3})(\\d{4})(\\d{4})/', '$1-$2-$3', $only);
        }
        if (strlen($only) === 10) {
            return preg_replace('/(\\d{3})(\\d{3})(\\d{4})/', '$1-$2-$3', $only);
        }
        return $digits;
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

