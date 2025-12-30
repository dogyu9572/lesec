<?php

namespace App\Services\Backoffice;

use App\Models\GroupApplication;
use App\Models\GroupApplicationParticipant;
use App\Models\Member;
use App\Models\ProgramReservation;
use App\Models\School;
use App\Services\Concerns\DownloadsCsvSample;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GroupApplicationService
{
    use DownloadsCsvSample;
    public function getFilteredApplications(Request $request): LengthAwarePaginator
    {
        $query = GroupApplication::query()
            ->with(['reservation'])
            ->orderByDesc('created_at');

        if ($request->filled('application_status')) {
            $query->where('application_status', $request->input('application_status'));
        }

        if ($request->filled('education_type')) {
            $query->where('education_type', $request->input('education_type'));
        }

        if ($request->filled('participation_start_date')) {
            $query->whereDate('participation_date', '>=', $request->input('participation_start_date'));
        }

        if ($request->filled('participation_end_date')) {
            $query->whereDate('participation_date', '<=', $request->input('participation_end_date'));
        }

        if ($request->filled('applied_start_date')) {
            $query->whereDate('applied_at', '>=', $request->input('applied_start_date'));
        }

        if ($request->filled('applied_end_date')) {
            $query->whereDate('applied_at', '<=', $request->input('applied_end_date'));
        }

        if ($request->filled('search_keyword')) {
            $keyword = $request->input('search_keyword');
            $searchType = $request->input('search_type');

            $query->where(function ($innerQuery) use ($keyword, $searchType) {
                if ($searchType === 'application_number') {
                    $innerQuery->where('application_number', 'like', "%{$keyword}%");
                } elseif ($searchType === 'applicant_name') {
                    $innerQuery->where('applicant_name', 'like', "%{$keyword}%");
                } elseif ($searchType === 'school_name') {
                    $innerQuery->where('school_name', 'like', "%{$keyword}%");
                } elseif ($searchType === 'program_name') {
                    $innerQuery->where('program_name', 'like', "%{$keyword}%");
                } else {
                    $innerQuery
                        ->where('application_number', 'like', "%{$keyword}%")
                        ->orWhere('applicant_name', 'like', "%{$keyword}%")
                        ->orWhere('school_name', 'like', "%{$keyword}%")
                        ->orWhere('program_name', 'like', "%{$keyword}%");
                }
            });
        }

        $perPage = (int) $request->input('per_page', 20);

        return $query->paginate($perPage)->withQueryString();
    }

    public function getFilterOptions(): array
    {
        return [
            'application_statuses' => GroupApplication::APPLICATION_STATUS_LABELS,
            'education_types' => GroupApplication::EDUCATION_TYPE_LABELS,
            'search_types' => [
                'application_number' => '신청번호',
                'applicant_name' => '신청자명',
                'school_name' => '학교명',
                'program_name' => '프로그램명',
            ],
        ];
    }

    public function getApplicationDetail(int $applicationId): GroupApplication
    {
        return GroupApplication::query()
            ->with(['participants' => fn ($query) => $query->orderBy('id', 'asc')])
            ->findOrFail($applicationId);
    }

    public function getFormOptions(): array
    {
        return [
            'application_statuses' => GroupApplication::APPLICATION_STATUS_LABELS,
            'payment_methods' => GroupApplication::PAYMENT_METHOD_LABELS,
            'payment_statuses' => GroupApplication::PAYMENT_STATUS_LABELS,
            'education_types' => GroupApplication::EDUCATION_TYPE_LABELS,
            'reception_statuses' => GroupApplication::RECEPTION_STATUS_LABELS,
        ];
    }

    public function createApplication(array $data): GroupApplication
    {
        $reservation = ProgramReservation::find($data['program_reservation_id'] ?? 0);

        if (!$reservation) {
            throw new InvalidArgumentException('선택한 프로그램을 찾을 수 없습니다.');
        }

        if ($reservation->application_type !== 'group') {
            throw new InvalidArgumentException('단체 신청 프로그램만 선택할 수 있습니다.');
        }

        if (!$reservation->is_active) {
            throw new InvalidArgumentException('비활성화된 프로그램입니다.');
        }

        $applicantCount = max(1, (int) ($data['applicant_count'] ?? 0));
        if (!$reservation->is_unlimited_capacity) {
            $capacity = (int) ($reservation->capacity ?? 0);
            $applied = (int) ($reservation->applied_count ?? 0);
            $remaining = max(0, $capacity - $applied);

            if ($applicantCount > $remaining) {
                throw new InvalidArgumentException('잔여 정원이 부족합니다.');
            }
        }

        $normalizedContact = $this->normalizeContactNumber($data['applicant_contact'] ?? '');
        if ($normalizedContact === null) {
            throw new InvalidArgumentException('신청자 연락처를 정확히 입력해주세요.');
        }

        $participationDateValue = $data['participation_date'] ?? null;
        if (empty($participationDateValue) && $reservation->education_start_date instanceof Carbon) {
            $participationDateValue = $reservation->education_start_date->toDateString();
        }
        $participationDate = $participationDateValue ? Carbon::parse($participationDateValue) : null;

        $paymentMethods = array_values(array_filter(Arr::wrap($data['payment_methods'] ?? [])));
        $paymentMethods = count($paymentMethods) > 0 ? $paymentMethods : null;

        $participationFee = $data['participation_fee'] ?? $reservation->education_fee;
        if ($participationFee === '' || $participationFee === null) {
            $participationFee = null;
        } else {
            $participationFee = (int) $participationFee;
        }

        return DB::transaction(function () use ($reservation, $data, $applicantCount, $normalizedContact, $participationDate, $paymentMethods, $participationFee) {
            $application = GroupApplication::create([
                'program_reservation_id' => $reservation->id,
                'application_number' => $this->generateApplicationNumber($reservation),
                'education_type' => $reservation->education_type,
                'payment_methods' => $paymentMethods,
                'payment_method' => $data['payment_method'] ?? null,
                'application_status' => $data['application_status'] ?? 'pending',
                'reception_status' => $data['reception_status'] ?? 'application',
                'applicant_name' => $data['applicant_name'],
                'member_id' => $data['member_id'] ?? null,
                'applicant_contact' => $normalizedContact,
                'school_level' => $data['school_level'] ?? null,
                'school_name' => $data['school_name'] ?? null,
                'applicant_count' => $applicantCount,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'participation_fee' => $participationFee,
                'participation_date' => $participationDate?->toDateString(),
                'applied_at' => Carbon::now(),
            ]);

            if (!$reservation->is_unlimited_capacity) {
                $reservation->increment('applied_count', $applicantCount);
            }

            return $application;
        });
    }

    public function validateUpdateRequest(Request $request): array
    {
        $allowedEducationTypes = array_keys(GroupApplication::EDUCATION_TYPE_LABELS);
        $allowedApplicationStatuses = array_keys(GroupApplication::APPLICATION_STATUS_LABELS);
        $allowedPaymentStatuses = array_keys(GroupApplication::PAYMENT_STATUS_LABELS);
        $allowedPaymentMethods = array_keys(GroupApplication::PAYMENT_METHOD_LABELS);
        $allowedReceptionStatuses = array_keys(GroupApplication::RECEPTION_STATUS_LABELS);

        $rules = [
            'education_type' => ['required', Rule::in($allowedEducationTypes)],
            'program_reservation_id' => ['nullable', 'integer', 'exists:program_reservations,id'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['in:' . implode(',', $allowedPaymentMethods)],
            'payment_method' => ['nullable', Rule::in($allowedPaymentMethods)],
            'application_status' => ['required', Rule::in($allowedApplicationStatuses)],
            'reception_status' => ['nullable', Rule::in($allowedReceptionStatuses)],
            'applicant_name' => ['required', 'string', 'max:50'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'applicant_contact' => ['nullable', 'string', 'max:30'],
            'school_level' => ['nullable', 'string', 'max:50'],
            'school_name' => ['nullable', 'string', 'max:100'],
            'applicant_count' => ['nullable', 'integer', 'min:0'],
            'payment_status' => ['required', Rule::in($allowedPaymentStatuses)],
            'participation_fee' => ['nullable', 'integer', 'min:0'],
            'participation_date' => ['nullable', 'date'],
        ];

        $validated = $request->validate($rules);
        $validated['payment_methods'] = array_values(array_filter(Arr::wrap($validated['payment_methods'] ?? [])));

        return $validated;
    }

    public function updateApplication(int $applicationId, array $data): void
    {
        $application = GroupApplication::findOrFail($applicationId);

        $paymentMethods = array_values(array_filter(Arr::wrap($data['payment_methods'] ?? [])));
        $paymentMethods = count($paymentMethods) > 0 ? $paymentMethods : null;

        $payload = [
            'education_type' => $data['education_type'],
            'program_reservation_id' => $data['program_reservation_id'] ?? $application->program_reservation_id,
            'application_status' => $data['application_status'],
            'reception_status' => $data['reception_status'] ?? $application->reception_status,
            'applicant_name' => $data['applicant_name'],
            'member_id' => $data['member_id'] ?? null,
            'applicant_contact' => $data['applicant_contact'] ?? null,
            'school_level' => $data['school_level'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'applicant_count' => $data['applicant_count'] ?? 0,
            'payment_status' => $data['payment_status'],
            'payment_method' => $data['payment_method'] ?? null,
            'participation_fee' => isset($data['participation_fee']) ? (int) $data['participation_fee'] : null,
            'payment_methods' => $paymentMethods,
        ];

        if (!empty($data['participation_date'])) {
            $payload['participation_date'] = Carbon::parse($data['participation_date'])->toDateString();
        }

        $application->fill($payload);
        $application->save();
    }

    public function deleteApplication(int $applicationId): void
    {
        $application = GroupApplication::findOrFail($applicationId);
        $application->delete();
    }

    public function searchSchools(Request $request): array
    {
        $query = School::query()->select('id', 'school_name', 'school_level', 'city', 'district');

        if ($request->filled('search_keyword')) {
            $keyword = $request->input('search_keyword');
            $query->where('school_name', 'like', "%{$keyword}%");
        }

        if ($request->filled('school_level')) {
            $query->where('school_level', $request->input('school_level'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->orderBy('school_name')->paginate($perPage);

        return [
            'schools' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function searchMembers(Request $request): array
    {
        $query = Member::query()->select('id', 'name', 'login_id', 'email', 'school_name', 'member_type', 'contact');

        if ($request->filled('member_type') && $request->member_type !== 'all') {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('search_term')) {
            $term = $request->input('search_term');
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('login_id', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $paginator = $query->orderByDesc('created_at')->paginate(10);

        return [
            'members' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function searchPrograms(Request $request): array
    {
        $query = ProgramReservation::query()
            ->byApplicationType('group')
            ->active()
            ->orderByDesc('created_at');

        if ($request->filled('education_type')) {
            $query->where('education_type', $request->input('education_type'));
        }

        if ($request->filled('search_keyword')) {
            $keyword = $request->input('search_keyword');
            $query->where('program_name', 'like', "%{$keyword}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->paginate($perPage);

        $programs = $paginator->map(function (ProgramReservation $reservation) {
            return [
                'id' => $reservation->id,
                'program_name' => $reservation->program_name,
                'education_type' => $reservation->education_type,
                'education_type_label' => $reservation->education_type_name,
                'education_start_date' => optional($reservation->education_start_date)->format('Y-m-d'),
                'education_end_date' => optional($reservation->education_end_date)->format('Y-m-d'),
                'education_fee' => $reservation->education_fee,
                'payment_methods' => $reservation->payment_methods ?? [],
            ];
        });

        return [
            'programs' => $programs->toArray(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function downloadRoster(int $applicationId)
    {
        return response()->noContent();
    }

    public function downloadQuotation(int $applicationId)
    {
        return response()->noContent();
    }

    public function downloadRosterSample(): StreamedResponse
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new InvalidArgumentException('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다.');
        }

        $filename = 'group_roster_sample.xlsx';
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

    public function uploadRoster(int $applicationId, UploadedFile $file): int
    {
        $application = GroupApplication::query()->findOrFail($applicationId);
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        $rows = 0;

        if ($extension === 'csv') {
            // CSV 파일 처리
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new InvalidArgumentException('파일을 읽을 수 없습니다.');
            }

            // UTF-8 BOM 제거
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // header skip
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                $rows += $this->processRosterRow($application->id, $data);
            }
            fclose($handle);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            // 엑셀 파일 처리
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                throw new InvalidArgumentException('엑셀 파일 처리를 위해 PhpSpreadsheet가 필요합니다.');
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
                $rows += $this->processRosterRow($application->id, $row);
            }
        } else {
            throw new InvalidArgumentException('지원하지 않는 파일 형식입니다. CSV 또는 엑셀 파일을 사용해주세요.');
        }

        $count = GroupApplicationParticipant::where('group_application_id', $application->id)->count();
        $application->applicant_count = $count;
        $application->save();

        return $rows;
    }

    /**
     * 명단 행 데이터 처리
     */
    private function processRosterRow(int $applicationId, array $data): int
    {
        $name = trim($data[0] ?? '');
        $grade = trim($data[1] ?? '');
        $class = trim($data[2] ?? '');
        $birthdayRaw = trim($data[3] ?? '');

        if ($name === '' || $grade === '' || $class === '') {
            return 0;
        }

        $birthday = null;
        $digits = preg_replace('/[^0-9]/', '', $birthdayRaw);
        if (strlen($digits) === 8 && ctype_digit($digits)) {
            try {
                $birthday = Carbon::createFromFormat('Ymd', $digits)->format('Y-m-d');
            } catch (\Throwable $e) {
                $birthday = null;
            }
        }

        GroupApplicationParticipant::create([
            'group_application_id' => $applicationId,
            'name' => $name,
            'grade' => (int) $grade,
            'class' => $class,
            'birthday' => $birthday,
        ]);

        return 1;
    }

    public function storeParticipant(int $applicationId, array $data): void
    {
        $application = GroupApplication::query()->findOrFail($applicationId);
        $birthday = null;
        $digits = preg_replace('/[^0-9]/', '', $data['birthday'] ?? '');
        if (strlen($digits) === 8 && ctype_digit($digits)) {
            try {
                $birthday = Carbon::createFromFormat('Ymd', $digits)->format('Y-m-d');
            } catch (\Throwable $e) {
                $birthday = null;
            }
        }
        GroupApplicationParticipant::create([
            'group_application_id' => $application->id,
            'name' => $data['name'],
            'grade' => (int) $data['grade'],
            'class' => $data['class'],
            'birthday' => $birthday,
        ]);
        $count = GroupApplicationParticipant::where('group_application_id', $application->id)->count();
        $application->applicant_count = $count;
        $application->save();
    }

    public function updateParticipant(int $applicationId, int $participantId, array $data): void
    {
        $participant = GroupApplicationParticipant::query()
            ->where('id', $participantId)
            ->where('group_application_id', $applicationId)
            ->firstOrFail();
        $birthday = null;
        $digits = preg_replace('/[^0-9]/', '', $data['birthday'] ?? '');
        if (strlen($digits) === 8 && ctype_digit($digits)) {
            try {
                $birthday = Carbon::createFromFormat('Ymd', $digits)->format('Y-m-d');
            } catch (\Throwable $e) {
                $birthday = null;
            }
        }
        $participant->update([
            'name' => $data['name'],
            'grade' => (int) $data['grade'],
            'class' => $data['class'],
            'birthday' => $birthday,
        ]);
    }

    public function destroyParticipant(int $applicationId, int $participantId): void
    {
        $participant = GroupApplicationParticipant::query()
            ->where('id', $participantId)
            ->where('group_application_id', $applicationId)
            ->firstOrFail();
        $participant->delete();
        $application = GroupApplication::query()->findOrFail($applicationId);
        $count = GroupApplicationParticipant::where('group_application_id', $application->id)->count();
        $application->applicant_count = $count;
        $application->save();
    }
    /**
     * 3월 기준 학사연도 계산
     */
    private function getAcademicYear(): int
    {
        $now = Carbon::now();
        $year = $now->year;
        
        // 3월 1일 이전이면 전년도 사용
        if ($now->month < 3) {
            $year--;
        }
        
        return $year;
    }

    private function generateApplicationNumber(ProgramReservation $reservation): string
    {
        $year = $this->getAcademicYear();
        $prefix = 'T'; // 교사만 단체 신청 가능

        $latestNumber = GroupApplication::query()
            ->where('application_number', 'like', $prefix . $year . '%')
            ->orderByDesc('application_number')
            ->lockForUpdate()
            ->value('application_number');

        $nextSequence = 1;
        if ($latestNumber && preg_match('/^' . preg_quote($prefix . $year, '/') . '(\d{4})$/', $latestNumber, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%s%04d', $prefix, $year, $nextSequence);
    }

    private function normalizeContactNumber(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $number);

        return $digits !== '' ? $digits : null;
    }
}


