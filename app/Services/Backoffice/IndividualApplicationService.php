<?php

namespace App\Services\Backoffice;

use App\Models\IndividualApplication;
use App\Models\ProgramReservation;
use App\Models\Member;
use App\Services\Concerns\DownloadsCsvSample;
use App\Services\ProgramReservationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class IndividualApplicationService
{
    use DownloadsCsvSample;
    protected ProgramReservationService $programReservationService;

    public function __construct(ProgramReservationService $programReservationService)
    {
        $this->programReservationService = $programReservationService;
    }

    /**
     * 필터링된 개인 신청 목록 조회
     */
    public function getFilteredApplications(Request $request): LengthAwarePaginator
    {
        $query = IndividualApplication::query()
            ->with(['reservation', 'member'])
            ->orderBy('created_at', 'desc');

        // 신청유형 필터
        if ($request->filled('reception_type')) {
            $query->where('reception_type', $request->reception_type);
        }

        // 교육유형 필터
        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        // 참가일정 기간 검색
        if ($request->filled('participation_start_date')) {
            $query->where('participation_date', '>=', $request->participation_start_date);
        }
        if ($request->filled('participation_end_date')) {
            $query->where('participation_date', '<=', $request->participation_end_date);
        }

        // 신청기간 검색
        if ($request->filled('applied_start_date')) {
            $query->whereDate('applied_at', '>=', $request->applied_start_date);
        }
        if ($request->filled('applied_end_date')) {
            $query->whereDate('applied_at', '<=', $request->applied_end_date);
        }

        // 추첨결과 필터
        if ($request->filled('draw_result')) {
            $query->where('draw_result', $request->draw_result);
        }

        // 결제상태 필터
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 검색어 (신청자명/학교명/프로그램명/신청번호)
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $searchType = $request->input('search_type', '');
            
            if ($searchType === 'applicant_name') {
                $query->where('applicant_name', 'like', "%{$keyword}%");
            } elseif ($searchType === 'school_name') {
                $query->where('applicant_school_name', 'like', "%{$keyword}%");
            } elseif ($searchType === 'program_name') {
                $query->whereHas('reservation', function ($q) use ($keyword) {
                    $q->where('program_name', 'like', "%{$keyword}%");
                });
            } elseif ($searchType === 'application_number') {
                $query->where('application_number', 'like', "%{$keyword}%");
            } else {
                // 전체 검색 (search_type이 비어있거나 'all'인 경우)
                $query->where(function ($q) use ($keyword) {
                    $q->where('applicant_name', 'like', "%{$keyword}%")
                      ->orWhere('applicant_school_name', 'like', "%{$keyword}%")
                      ->orWhere('application_number', 'like', "%{$keyword}%")
                      ->orWhereHas('reservation', function ($subQ) use ($keyword) {
                          $subQ->where('program_name', 'like', "%{$keyword}%");
                      });
                });
            }
        }

        $perPage = $request->input('per_page', 20);
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 신청유형 목록 반환
     */
    public function getReceptionTypes(): array
    {
        return IndividualApplication::RECEPTION_TYPE_LABELS;
    }

    /**
     * 교육유형 목록 반환
     */
    public function getEducationTypes(): array
    {
        return IndividualApplication::EDUCATION_TYPE_LABELS;
    }

    /**
     * 추첨결과 목록 반환
     */
    public function getDrawResults(): array
    {
        return [
            IndividualApplication::DRAW_RESULT_PENDING => IndividualApplication::DRAW_RESULT_LABELS[IndividualApplication::DRAW_RESULT_PENDING],
            IndividualApplication::DRAW_RESULT_WIN => IndividualApplication::DRAW_RESULT_LABELS[IndividualApplication::DRAW_RESULT_WIN],
            IndividualApplication::DRAW_RESULT_FAIL => IndividualApplication::DRAW_RESULT_LABELS[IndividualApplication::DRAW_RESULT_FAIL],
        ];
    }

    /**
     * 결제상태 목록 반환
     */
    public function getPaymentStatuses(): array
    {
        return [
            IndividualApplication::PAYMENT_STATUS_UNPAID => IndividualApplication::PAYMENT_STATUS_LABELS[IndividualApplication::PAYMENT_STATUS_UNPAID],
            IndividualApplication::PAYMENT_STATUS_PAID => IndividualApplication::PAYMENT_STATUS_LABELS[IndividualApplication::PAYMENT_STATUS_PAID],
        ];
    }

    /**
     * 결제방법 목록 반환
     */
    public function getPaymentMethods(): array
    {
        return [
            'bank_transfer' => '무통장 입금',
            'on_site_card' => '방문 카드결제',
            'online_card' => '온라인 카드결제',
        ];
    }

    /**
     * 개인 신청 신규 생성
     */
    public function createApplication(array $data): IndividualApplication
    {
        $reservationId = $data['program_reservation_id'] ?? null;

        if (!$reservationId) {
            throw new InvalidArgumentException('프로그램을 선택해 주세요.');
        }

        $reservation = ProgramReservation::query()
            ->byApplicationType('individual')
            ->find($reservationId);

        if (!$reservation) {
            throw new InvalidArgumentException('프로그램 정보를 찾을 수 없습니다.');
        }

        $member = null;
        if (!empty($data['member_id'])) {
            $member = Member::find($data['member_id']);
            if (!$member) {
                throw new InvalidArgumentException('회원 정보를 찾을 수 없습니다.');
            }
        }

        $application = $this->programReservationService->createIndividualApplication(
            $reservation,
            [
                'member_id' => $data['member_id'] ?? null,
                'applicant_name' => $data['applicant_name'],
                'applicant_school_name' => $data['applicant_school_name'] ?? null,
                'applicant_grade' => $data['applicant_grade'] ?? null,
                'applicant_class' => $data['applicant_class'] ?? null,
                'applicant_contact' => $data['applicant_contact'],
                'guardian_contact' => $data['guardian_contact'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? IndividualApplication::PAYMENT_STATUS_UNPAID,
                'draw_result' => $data['draw_result'] ?? null,
            ],
            $member,
            true
        );

        $this->updateApplication($application, $data);

        return $application->refresh();
    }

    /**
     * 신청 정보 업데이트
     */
    public function updateApplication(IndividualApplication $application, array $data): bool
    {
        $updateData = [];

        $fields = [
            'reception_type',
            'education_type',
            'draw_result',
            'payment_status',
            'payment_method',
            'program_reservation_id',
            'member_id',
            'applicant_name',
            'applicant_school_name',
            'applicant_grade',
            'applicant_class',
            'applicant_contact',
            'guardian_contact',
        ];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'program_reservation_id':
                case 'member_id':
                    $updateData[$field] = $value !== null && $value !== '' ? (int) $value : null;
                    break;
                case 'applicant_grade':
                case 'applicant_class':
                    $updateData[$field] = $value !== null && $value !== '' ? (int) $value : null;
                    break;
                default:
                    $updateData[$field] = $value === '' ? null : $value;
                    break;
            }
        }

        if (empty($updateData)) {
            return true;
        }

        return $application->update($updateData);
    }

    private function extractProgramPrefix(?string $programName): string
    {
        if (empty($programName)) {
            return '';
        }

        $trimmed = trim($programName);

        if ($trimmed === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($trimmed, 0, 2));
    }

    /**
     * 신청 삭제
     */
    public function deleteApplication(IndividualApplication $application): bool
    {
        // 신청 삭제 시 프로그램 신청 인원 감소
        if ($application->reservation && !$application->reservation->is_unlimited_capacity) {
            $application->reservation->decrement('applied_count', 1);
        }

        return $application->delete();
    }

    /**
     * 회원 검색
     */
    public function searchMembers(Request $request)
    {
        $query = \App\Models\Member::query()->select('id', 'name', 'login_id', 'email', 'school_name', 'contact');

        if ($request->filled('member_type') && $request->member_type !== 'all') {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('search_term')) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('login_id', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('contact', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * 프로그램 검색
     */
    public function searchPrograms(Request $request)
    {
        $query = \App\Models\ProgramReservation::query()
            ->byApplicationType('individual')
            ->active()
            ->orderBy('created_at', 'desc');

        // 네이버 폼 프로그램만 조회 (reception_type이 전달된 경우)
        if ($request->filled('reception_type')) {
            $query->where('reception_type', $request->reception_type);
        }

        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where('program_name', 'like', "%{$keyword}%");
        }

        $perPage = (int) $request->get('per_page', 10);

        return $query->paginate($perPage)->through(function ($program) {
            $days = ['일', '월', '화', '수', '목', '금', '토'];
            $startDate = null;
            $endDate = null;
            
            if ($program->education_start_date instanceof Carbon) {
                $startDay = $days[$program->education_start_date->dayOfWeek] ?? '';
                $startDate = $program->education_start_date->format('Y.m.d') . '(' . $startDay . ')';
            }
            
            if ($program->education_end_date instanceof Carbon) {
                $endDay = $days[$program->education_end_date->dayOfWeek] ?? '';
                $endDate = $program->education_end_date->format('Y.m.d') . '(' . $endDay . ')';
            }
            
            $participationSchedule = '';
            if ($startDate && $endDate) {
                if ($program->education_start_date->equalTo($program->education_end_date)) {
                    $participationSchedule = $startDate;
                } else {
                    $participationSchedule = $startDate . '~' . $endDate;
                }
            } elseif ($startDate) {
                $participationSchedule = $startDate;
            } else {
                $participationSchedule = '-';
            }
            
            return [
                'id' => $program->id,
                'program_name' => $program->program_name,
                'education_type' => $program->education_type,
                'education_start_date' => optional($program->education_start_date)->format('Y-m-d'),
                'education_end_date' => optional($program->education_end_date)->format('Y-m-d'),
                'participation_schedule' => $participationSchedule,
                'education_fee' => $program->education_fee,
            ];
        });
    }

    /**
     * 샘플 엑셀 파일 다운로드
     * 네이버 폼 일괄 업로드용 (신청유형/교육유형/프로그램명/신청일시 제외)
     */
    public function downloadSample(): StreamedResponse
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new InvalidArgumentException('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다.');
        }

        $filename = 'individual_application_sample.xlsx';
        $headers = [
            '참가일',
            '참가비',
            '결제방법',
            '신청자명',
            '학교명',
            '학년',
            '반',
            '연락처1',
            '연락처2',
            '결제상태',
            '추첨결과',
            '회원ID'
        ];
        $sampleRows = [
            [
                '2025-01-15',
                '50000',
                '무통장 입금',
                '홍길동',
                '서울중학교',
                '1',
                '1',
                '01012345678',
                '01098765432',
                '미입금',
                '대기중',
                ''
            ],
            [
                '2025-01-20',
                '50000',
                '방문 카드결제',
                '김철수',
                '경기중학교교',
                '2',
                '3',
                '01011112222',
                '01033334444',
                '입금완료',
                '대기중',
                ''
            ],
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
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        // 샘플 데이터 작성
        $rowIndex = 2;
        foreach ($sampleRows as $row) {
            $columnIndex = 1;
            foreach ($row as $cellValue) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $cellAddress = $columnLetter . $rowIndex;
                
                // 연락처1, 연락처2, 회원ID 컬럼은 텍스트 형식으로 설정 (숫자 앞 0 유지)
                if ($columnIndex === 8 || $columnIndex === 9 || $columnIndex === 12) {
                    $sheet->setCellValueExplicit($cellAddress, $cellValue, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($cellAddress, $cellValue);
                }
                
                $columnIndex++;
            }
            $rowIndex++;
        }

        // 컬럼 너비 자동 조정
        foreach (range('A', 'L') as $column) {
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
     * CSV/엑셀 파일 일괄 업로드
     */
    public function bulkUploadApplications(UploadedFile $file, int $programReservationId): array
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        DB::beginTransaction();
        try {
            if ($extension === 'csv') {
                $this->processCsvFile($filePath, $programReservationId, $successCount, $errorCount, $errors);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $this->processExcelFile($filePath, $programReservationId, $successCount, $errorCount, $errors);
            } else {
                throw new InvalidArgumentException('지원하지 않는 파일 형식입니다.');
            }

            DB::commit();

            return [
                'success' => true,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * CSV 파일 처리
     */
    private function processCsvFile(string $filePath, int $programReservationId, int &$successCount, int &$errorCount, array &$errors): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new InvalidArgumentException('파일을 읽을 수 없습니다.');
        }

        // UTF-8 BOM 제거
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        // 헤더 건너뛰기
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new InvalidArgumentException('파일 형식이 올바르지 않습니다.');
        }

        $lineNumber = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;
            try {
                $this->createApplicationFromRow($data, $lineNumber, $programReservationId);
                $successCount++;
            } catch (\Throwable $e) {
                $errorCount++;
                $errors[] = "{$lineNumber}번째 줄: " . $e->getMessage();
            }
        }

        fclose($handle);
    }

    /**
     * 엑셀 파일 처리
     */
    private function processExcelFile(string $filePath, int $programReservationId, int &$successCount, int &$errorCount, array &$errors): void
    {
        // PhpSpreadsheet 사용 (설치되어 있다고 가정)
        // 없으면 CSV만 지원하도록 처리
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new InvalidArgumentException('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다. CSV 파일을 사용해주세요.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // 헤더 제거
        array_shift($rows);

        $lineNumber = 1;
        foreach ($rows as $row) {
            $lineNumber++;
            if (empty(array_filter($row))) {
                continue;
            }
            try {
                $this->createApplicationFromRow($row, $lineNumber, $programReservationId);
                $successCount++;
            } catch (\Throwable $e) {
                $errorCount++;
                $errors[] = "{$lineNumber}번째 줄: " . $e->getMessage();
            }
        }
    }

    /**
     * CSV/엑셀 행 데이터로 신청 생성
     * 컬럼 순서: 참가일, 참가비, 결제방법, 신청자명, 학교명, 학년, 반, 연락처1, 연락처2, 결제상태, 추첨결과, 회원ID(선택)
     * (신청유형/교육유형/프로그램명은 선택한 프로그램 정보 사용, 신청일시는 저장 시 현재 날짜로 자동 설정)
     */
    private function createApplicationFromRow(array $row, int $lineNumber, int $programReservationId): void
    {
        $participationDate = trim($row[0] ?? '');
        $participationFee = trim($row[1] ?? '');
        $paymentMethodText = trim($row[2] ?? '');
        $applicantName = trim($row[3] ?? '');
        $schoolName = trim($row[4] ?? '');
        $grade = trim($row[5] ?? '');
        $class = trim($row[6] ?? '');
        $contact = ltrim(trim($row[7] ?? ''), "'"); // 작은따옴표 제거
        $guardianContact = ltrim(trim($row[8] ?? ''), "'"); // 작은따옴표 제거
        $paymentStatusText = trim($row[9] ?? '');
        $drawResultText = trim($row[10] ?? '');
        $memberIdText = ltrim(trim($row[11] ?? ''), "'"); // 작은따옴표 제거

        // 필수 필드 검증
        if (empty($applicantName)) {
            throw new InvalidArgumentException('신청자명은 필수입니다.');
        }
        if (empty($contact)) {
            throw new InvalidArgumentException('연락처1은 필수입니다.');
        }

        // 선택한 프로그램 정보 조회
        $reservation = ProgramReservation::findOrFail($programReservationId);

        // 한글 텍스트를 코드값으로 변환
        $paymentMethod = $this->convertPaymentMethodToCode($paymentMethodText);
        $paymentStatus = $this->convertPaymentStatusToCode($paymentStatusText);
        $drawResult = $this->convertDrawResultToCode($drawResultText);

        // 회원 ID 처리: CSV에 회원 ID가 있으면 사용, 없으면 연락처로 자동 매칭
        $memberId = null;
        if (!empty($memberIdText)) {
            $memberIdText = trim($memberIdText);
            
            // 숫자면 member_id로 검색
            if (is_numeric($memberIdText)) {
                $memberId = (int) $memberIdText;
                $member = Member::find($memberId);
                if (!$member) {
                    $memberId = null; // 잘못된 회원 ID는 무시
                }
            } else {
                // 숫자가 아니면 login_id로 검색
                $member = Member::where('login_id', $memberIdText)->first();
                if ($member) {
                    $memberId = $member->id;
                }
            }
        }

        // 회원 ID가 없으면 연락처로 자동 매칭
        if (!$memberId) {
            $memberId = $this->findMemberByContact($contact);
        }

        // 데이터 정리 (선택한 프로그램 정보 사용)
        $data = [
            'program_reservation_id' => $reservation->id,
            'member_id' => $memberId,
            'reception_type' => 'naver_form', // 네이버 폼으로 고정
            'education_type' => $reservation->education_type, // 선택한 프로그램의 교육유형 사용
            'program_name' => $reservation->program_name, // 선택한 프로그램의 프로그램명 사용
            'participation_date' => $participationDate 
                ? Carbon::parse($participationDate)->format('Y-m-d') 
                : ($reservation->education_start_date ? $reservation->education_start_date->format('Y-m-d') : null),
            'participation_fee' => $participationFee 
                ? (int) $participationFee 
                : ($reservation->education_fee ?? 0),
            'payment_method' => $paymentMethod,
            'applicant_name' => $applicantName,
            'applicant_school_name' => $schoolName ?: null,
            'applicant_grade' => $grade ? (int) $grade : null,
            'applicant_class' => $class ? (int) $class : null,
            'applicant_contact' => $contact,
            'guardian_contact' => $guardianContact ?: null,
            'payment_status' => $paymentStatus ?: IndividualApplication::PAYMENT_STATUS_UNPAID,
            'draw_result' => IndividualApplication::DRAW_RESULT_PENDING, // 네이버 폼은 추첨 없음
        ];

        // 일괄 업로드는 직접 생성 (ProgramReservationService 거치지 않음)
        $application = $this->createApplicationDirectly($data, $reservation);

        // 신청일시는 현재 날짜로 자동 설정
        $application->applied_at = Carbon::now();
        $application->save();
    }

    /**
     * 일괄 업로드용 직접 생성 (ProgramReservationService 거치지 않음)
     */
    private function createApplicationDirectly(array $data, ProgramReservation $reservation): IndividualApplication
    {
        $year = $this->getAcademicYear();
        $prefix = str_starts_with($data['education_type'], 'high') ? 'H' : 'M';

        $latestNumber = IndividualApplication::query()
            ->where('application_number', 'like', $prefix . $year . '%')
            ->orderBy('application_number', 'desc')
            ->lockForUpdate()
            ->value('application_number');

        $nextSequence = 1;
        if ($latestNumber && preg_match('/^' . preg_quote($prefix . $year, '/') . '(\d{4})$/', $latestNumber, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        $applicationNumber = sprintf('%s%s%04d', $prefix, $year, $nextSequence);

        return IndividualApplication::create([
            'program_reservation_id' => $reservation->id,
            'member_id' => $data['member_id'] ?? null,
            'application_number' => $applicationNumber,
            'education_type' => $data['education_type'],
            'reception_type' => $data['reception_type'],
            'program_name' => $data['program_name'],
            'participation_date' => $data['participation_date'] ? Carbon::parse($data['participation_date']) : Carbon::today(),
            'participation_fee' => $data['participation_fee'],
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'],
            'draw_result' => $data['draw_result'],
            'applicant_name' => $data['applicant_name'],
            'applicant_school_name' => $data['applicant_school_name'],
            'applicant_grade' => $data['applicant_grade'],
            'applicant_class' => $data['applicant_class'],
            'applicant_contact' => $data['applicant_contact'],
            'guardian_contact' => $data['guardian_contact'],
            'applied_at' => now(),
        ]);
    }

    /**
     * 3월 기준 학사연도 계산
     */
    private function getAcademicYear(): int
    {
        $now = Carbon::now();
        $year = $now->year;

        if ($now->month < 3) {
            $year--;
        }

        return $year;
    }

    /**
     * 신청유형 한글 텍스트를 코드값으로 변환
     */
    private function convertReceptionTypeToCode(string $text): ?string
    {
        $map = array_flip(IndividualApplication::RECEPTION_TYPE_LABELS);
        return $map[$text] ?? null;
    }

    /**
     * 교육유형 한글 텍스트를 코드값으로 변환
     */
    private function convertEducationTypeToCode(string $text): ?string
    {
        $map = array_flip(IndividualApplication::EDUCATION_TYPE_LABELS);
        return $map[$text] ?? null;
    }

    /**
     * 결제방법 한글 텍스트를 코드값으로 변환
     */
    private function convertPaymentMethodToCode(string $text): ?string
    {
        $map = [
            '무통장 입금' => 'bank_transfer',
            '방문 카드결제' => 'on_site_card',
            '온라인 카드결제' => 'online_card',
        ];
        return $map[$text] ?? null;
    }

    /**
     * 결제상태 한글 텍스트를 코드값으로 변환
     */
    private function convertPaymentStatusToCode(string $text): ?string
    {
        $map = array_flip(IndividualApplication::PAYMENT_STATUS_LABELS);
        return $map[$text] ?? null;
    }

    /**
     * 추첨결과 한글 텍스트를 코드값으로 변환
     */
    private function convertDrawResultToCode(string $text): ?string
    {
        $map = array_flip(IndividualApplication::DRAW_RESULT_LABELS);
        return $map[$text] ?? null;
    }

    /**
     * 연락처로 회원 찾기
     */
    private function findMemberByContact(string $contact): ?int
    {
        if (empty($contact)) {
            return null;
        }

        // 연락처에서 숫자만 추출
        $digits = preg_replace('/[^0-9]/', '', $contact);
        if (empty($digits)) {
            return null;
        }

        // 연락처로 회원 검색 (contact 필드)
        $member = Member::where('contact', $digits)
            ->orWhere('contact', 'like', '%' . $digits . '%')
            ->first();

        return $member?->id;
    }
}

