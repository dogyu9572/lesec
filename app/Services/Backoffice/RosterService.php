<?php

namespace App\Services\Backoffice;

use App\Models\IndividualApplication;
use App\Models\GroupApplication;
use App\Models\GroupApplicationParticipant;
use App\Models\ProgramReservation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RosterService
{
    /**
     * 필터링된 명단 목록 조회 (프로그램 기준)
     */
    public function getFilteredRosters(Request $request): LengthAwarePaginator
    {
        // 개인 프로그램 조회
        $individualProgramQuery = ProgramReservation::query()
            ->byApplicationType('individual')
            ->active();

        // 단체 프로그램 조회
        $groupProgramQuery = ProgramReservation::query()
            ->byApplicationType('group')
            ->active();

        // 신청구분 필터
        $applicationType = $request->input('application_type');
        if ($applicationType === 'individual') {
            $groupProgramQuery->whereRaw('1 = 0'); // 단체 제외
        } elseif ($applicationType === 'group') {
            $individualProgramQuery->whereRaw('1 = 0'); // 개인 제외
        }

        // 신청유형 필터 (개인만 해당)
        if ($request->filled('reception_type')) {
            $individualProgramQuery->where('reception_type', $request->input('reception_type'));
        }

        // 교육유형 필터
        if ($request->filled('education_type')) {
            $individualProgramQuery->where('education_type', $request->input('education_type'));
            $groupProgramQuery->where('education_type', $request->input('education_type'));
        }

        // 참가일 필터 (교육 시작일 기준)
        if ($request->filled('participation_date')) {
            $date = $request->input('participation_date');
            $individualProgramQuery->whereDate('education_start_date', $date);
            $groupProgramQuery->whereDate('education_start_date', $date);
        }

        // 검색어 필터
        if ($request->filled('search_keyword')) {
            $keyword = $request->input('search_keyword');
            $searchType = $request->input('search_type', 'program_name');

            if ($searchType === 'program_name') {
                $individualProgramQuery->where('program_name', 'like', "%{$keyword}%");
                $groupProgramQuery->where('program_name', 'like', "%{$keyword}%");
            } elseif ($searchType === 'author') {
                $individualProgramQuery->where('author', 'like', "%{$keyword}%");
                $groupProgramQuery->where('author', 'like', "%{$keyword}%");
            }
        }

        // 데이터 조회
        $individualPrograms = $individualProgramQuery->get();
        $groupPrograms = $groupProgramQuery->get();

        // 프로그램 ID 목록
        $individualProgramIds = $individualPrograms->pluck('id')->toArray();
        $groupProgramIds = $groupPrograms->pluck('id')->toArray();

        // 각 프로그램별 신청인원 계산
        $individualApplicationsCount = IndividualApplication::query()
            ->whereIn('program_reservation_id', $individualProgramIds)
            ->selectRaw('program_reservation_id, COUNT(*) as count')
            ->groupBy('program_reservation_id')
            ->pluck('count', 'program_reservation_id')
            ->toArray();

        $groupApplicationsCount = GroupApplication::query()
            ->whereIn('program_reservation_id', $groupProgramIds)
            ->selectRaw('program_reservation_id, SUM(applicant_count) as total')
            ->groupBy('program_reservation_id')
            ->pluck('total', 'program_reservation_id')
            ->toArray();

        // 통합 데이터 변환
        $mergedData = $this->mergePrograms($individualPrograms, $groupPrograms, $individualApplicationsCount, $groupApplicationsCount);

        // 정렬 (참가일 기준 내림차순)
        $mergedData = $mergedData->sortByDesc(function ($item) {
            return $item['participation_date'] ?? '';
        })->values();

        // 페이지네이션 처리
        $perPage = (int) $request->input('per_page', 20);
        $currentPage = (int) $request->input('page', 1);
        $total = $mergedData->count();
        $items = $mergedData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $paginator;
    }

    /**
     * 개인/단체 프로그램 데이터 통합
     */
    private function mergePrograms(
        Collection $individualPrograms,
        Collection $groupPrograms,
        array $individualApplicationsCount,
        array $groupApplicationsCount
    ): Collection {
        $merged = collect();

        // 개인 프로그램 변환
        /** @var ProgramReservation $program */
        foreach ($individualPrograms as $program) {
            $applicantCount = $individualApplicationsCount[$program->id] ?? 0;
            
            $merged->push([
                'id' => $program->id,
                'application_type' => 'individual',
                'reception_type' => $program->reception_type,
                'education_type' => $program->education_type,
                'program_name' => $program->program_name,
                'participation_date' => $program->education_start_date instanceof Carbon ? $program->education_start_date->format('Y-m-d') : null,
                'participation_date_formatted' => $this->formatParticipationDate($program->education_start_date, $program->education_end_date),
                'capacity' => $program->capacity,
                'is_unlimited_capacity' => $program->is_unlimited_capacity ?? false,
                'applicant_count' => $applicantCount,
                'edit_url' => route('backoffice.individual-programs.edit', $program->id),
                'raw' => $program, // 원본 데이터 보관
            ]);
        }

        // 단체 프로그램 변환
        /** @var ProgramReservation $program */
        foreach ($groupPrograms as $program) {
            $applicantCount = (int) ($groupApplicationsCount[$program->id] ?? 0);
            
            $merged->push([
                'id' => $program->id,
                'application_type' => 'group',
                'reception_type' => null, // 단체는 신청유형 없음
                'education_type' => $program->education_type,
                'program_name' => $program->program_name,
                'participation_date' => $program->education_start_date instanceof Carbon ? $program->education_start_date->format('Y-m-d') : null,
                'participation_date_formatted' => $this->formatParticipationDate($program->education_start_date, $program->education_end_date),
                'capacity' => $program->capacity,
                'is_unlimited_capacity' => $program->is_unlimited_capacity ?? false,
                'applicant_count' => $applicantCount,
                'edit_url' => route('backoffice.group-programs.edit', $program->id),
                'raw' => $program, // 원본 데이터 보관
            ]);
        }

        return $merged;
    }

    /**
     * 참가일 포맷팅 (요일 포함, 시작일~종료일)
     */
    private function formatParticipationDate($startDate, $endDate = null): ?string
    {
        if (!$startDate) {
            return null;
        }

        if ($startDate instanceof Carbon) {
            $startCarbon = $startDate;
        } else {
            $startCarbon = Carbon::parse($startDate);
        }

        $days = ['일', '월', '화', '수', '목', '금', '토'];
        $startDayOfWeek = $days[$startCarbon->dayOfWeek] ?? '';
        $startFormatted = $startCarbon->format('Y.m.d') . '(' . $startDayOfWeek . ')';

        // 종료일이 있고 시작일과 다르면 범위로 표시
        if ($endDate) {
            if ($endDate instanceof Carbon) {
                $endCarbon = $endDate;
            } else {
                $endCarbon = Carbon::parse($endDate);
            }

            if ($startCarbon->format('Y-m-d') !== $endCarbon->format('Y-m-d')) {
                $endDayOfWeek = $days[$endCarbon->dayOfWeek] ?? '';
                $endFormatted = $endCarbon->format('Y.m.d') . '(' . $endDayOfWeek . ')';
                return $startFormatted . ' ~ ' . $endFormatted;
            }
        }

        return $startFormatted;
    }

    /**
     * 필터 옵션 반환
     */
    public function getFilterOptions(): array
    {
        return [
            'application_types' => [
                'individual' => '개인',
                'group' => '단체',
            ],
            'reception_types' => IndividualApplication::RECEPTION_TYPE_LABELS,
            'education_types' => IndividualApplication::EDUCATION_TYPE_LABELS,
            'search_types' => [
                'program_name' => '프로그램명',
                'author' => '작성자',
            ],
        ];
    }

    /**
     * 명단 상세 데이터 조회
     */
    public function getRosterDetail(int $reservationId): array
    {
        $reservation = ProgramReservation::findOrFail($reservationId);

        $programInfo = [
            'id' => $reservation->id,
            'application_type' => $reservation->application_type,
            'reception_type' => $reservation->reception_type,
            'education_type' => $reservation->education_type,
            'program_name' => $reservation->program_name,
            'participation_date' => $reservation->education_start_date,
            'participation_date_formatted' => $this->formatParticipationDate($reservation->education_start_date, $reservation->education_end_date),
            'capacity' => $reservation->capacity,
            'is_unlimited_capacity' => $reservation->is_unlimited_capacity ?? false,
            'applied_count' => 0,
            'raw' => $reservation,
        ];

        $rosterList = collect();

        if ($reservation->application_type === 'individual') {
            $applications = IndividualApplication::where('program_reservation_id', $reservationId)
                ->with(['member'])
                ->orderBy('applied_at', 'asc')
                ->get();

            $programInfo['applied_count'] = $applications->count();

            foreach ($applications as $application) {
                $rosterList->push([
                    'id' => $application->id,
                    'application_number' => $application->application_number,
                    'applicant_name' => $application->applicant_name,
                    'school_name' => $application->applicant_school_name,
                    'grade' => $application->applicant_grade,
                    'class' => $application->applicant_class,
                    'birthday' => $application->member?->birth_date,
                    'birthday_formatted' => $application->member?->birth_date?->format('Ymd'),
                    'gender' => $application->member?->gender === 'male' ? '남' : ($application->member?->gender === 'female' ? '여' : '-'),
                    'payment_status' => $application->payment_status,
                    'payment_status_label' => $application->payment_status_label,
                    'applied_at' => $application->applied_at,
                    'applied_at_formatted' => $application->applied_at?->format('Y.m.d H:i'),
                    'draw_result' => $application->draw_result,
                    'draw_result_label' => $application->draw_result_label,
                    'raw' => $application,
                ]);
            }
        } else {
            $groupApplications = GroupApplication::where('program_reservation_id', $reservationId)
                ->with(['participants'])
                ->get();

            $totalCount = 0;
            foreach ($groupApplications as $groupApplication) {
                $totalCount += $groupApplication->participants->count();
                foreach ($groupApplication->participants as $participant) {
                    $rosterList->push([
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'school_name' => $groupApplication->school_name,
                        'grade' => $participant->grade,
                        'class' => $participant->class,
                        'birthday' => $participant->birthday,
                        'birthday_formatted' => $participant->birthday?->format('Ymd'),
                        'raw' => $participant,
                    ]);
                }
            }

            $programInfo['applied_count'] = $totalCount;
        }

        $lotteryStatus = null;
        if ($reservation->application_type === 'individual' && $reservation->reception_type === 'lottery') {
            // 추첨 대기 상태인 신청 수 확인 (null 또는 'pending')
            $pendingCount = IndividualApplication::where('program_reservation_id', $reservationId)
                ->where('reception_type', 'lottery')
                ->where(function($query) {
                    $query->whereNull('draw_result')
                          ->orWhere('draw_result', 'pending');
                })
                ->count();

            // 전체 신청 수 확인
            $totalCount = IndividualApplication::where('program_reservation_id', $reservationId)
                ->where('reception_type', 'lottery')
                ->count();

            $lotteryStatus = [
                'is_pending' => $pendingCount > 0,
                'pending_count' => $pendingCount,
                'total_count' => $totalCount,
                'is_completed' => $totalCount > 0 && $pendingCount === 0,
            ];
        }

        return [
            'program' => $programInfo,
            'roster_list' => $rosterList,
            'lottery_status' => $lotteryStatus,
        ];
    }

    /**
     * 추첨 실행
     */
    public function runLottery(int $reservationId, ?int $capacity = null): array
    {
        try {
            DB::beginTransaction();

            // 프로그램 정보 조회
            $reservation = ProgramReservation::findOrFail($reservationId);

            // 추첨 프로그램인지 확인
            if ($reservation->application_type !== 'individual' || $reservation->reception_type !== 'lottery') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '추첨 프로그램이 아닙니다.',
                ];
            }

            // 정원 확인
            if ($reservation->is_unlimited_capacity || !$reservation->capacity) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '정원이 설정되지 않은 프로그램입니다.',
                ];
            }

            $capacity = $capacity ?? $reservation->capacity;

            // 추첨 대상 조회 (draw_result가 null 또는 'pending'인 것만)
            $applications = IndividualApplication::where('program_reservation_id', $reservationId)
                ->where('reception_type', 'lottery')
                ->where(function($query) {
                    $query->whereNull('draw_result')
                          ->orWhere('draw_result', 'pending');
                })
                ->get();

            if ($applications->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '추첨 대상이 없습니다.',
                ];
            }

            // 이미 추첨이 완료된 경우 체크 (모든 신청이 win 또는 fail인지 확인)
            $totalApplications = IndividualApplication::where('program_reservation_id', $reservationId)
                ->where('reception_type', 'lottery')
                ->count();

            if ($totalApplications > 0 && $applications->count() === 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '이미 추첨이 완료된 프로그램입니다.',
                ];
            }

            // 랜덤 추첨 (shuffle 후 정원만큼 선택)
            $shuffled = $applications->shuffle();
            $winners = $shuffled->take($capacity);
            $losers = $shuffled->skip($capacity);

            // 당첨 처리
            $winnerIds = $winners->pluck('id')->toArray();
            if (!empty($winnerIds)) {
                IndividualApplication::whereIn('id', $winnerIds)
                    ->update(['draw_result' => 'win']);
            }

            // 미당첨 처리
            $loserIds = $losers->pluck('id')->toArray();
            if (!empty($loserIds)) {
                IndividualApplication::whereIn('id', $loserIds)
                    ->update(['draw_result' => 'fail']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => '추첨이 완료되었습니다. (당첨: ' . count($winnerIds) . '명, 미당첨: ' . count($loserIds) . '명)',
                'winners_count' => count($winnerIds),
                'losers_count' => count($loserIds),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => '추첨 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 명단 다운로드
     */
    public function downloadRoster(int $reservationId, array $selectedIds = [], array $selectedColumns = []): StreamedResponse
    {
        $reservation = ProgramReservation::findOrFail($reservationId);

        // 컬럼 매핑 정의
        $columnMapping = $this->getColumnMapping($reservation->application_type, $reservation->reception_type);

        // 선택된 컬럼 검증 및 필터링
        $selectedColumns = array_intersect($selectedColumns, array_keys($columnMapping));
        if (empty($selectedColumns)) {
            throw new \InvalidArgumentException('유효한 컬럼이 선택되지 않았습니다.');
        }

        // 데이터 조회
        $data = $this->prepareDownloadData($reservationId, $selectedIds, $selectedColumns);

        // 파일명 생성
        $programName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $reservation->program_name);
        $filename = '명단_' . ($reservation->application_type === 'individual' ? '개인' : '단체') . '_' . $programName . '_' . date('YmdHis');

        // CSV로 다운로드
        return $this->exportToCsv($data, $selectedColumns, $columnMapping, $filename);
    }

    /**
     * 컬럼 매핑 반환
     */
    private function getColumnMapping(string $applicationType, ?string $receptionType = null): array
    {
        if ($applicationType === 'individual') {
            $mapping = [
                'application_number' => '신청번호',
                'applicant_name' => '신청자명',
                'school_name' => '학교명',
                'grade' => '학년',
                'class' => '반',
                'birthday' => '생년월일',
                'gender' => '성별',
                'payment_status' => '결제상태',
                'applied_at' => '신청일시',
            ];

            if ($receptionType === 'lottery') {
                $mapping['draw_result'] = '추첨결과';
            }

            return $mapping;
        } else {
            return [
                'name' => '이름',
                'school_name' => '학교',
                'grade' => '학년',
                'class' => '반',
                'birthday' => '생년월일',
            ];
        }
    }

    /**
     * 다운로드 데이터 준비
     */
    private function prepareDownloadData(int $reservationId, array $selectedIds, array $selectedColumns): Collection
    {
        $reservation = ProgramReservation::findOrFail($reservationId);
        $data = collect();

        if ($reservation->application_type === 'individual') {
            $query = IndividualApplication::where('program_reservation_id', $reservationId)
                ->with(['member'])
                ->orderBy('applied_at', 'asc');

            if (!empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            }

            $applications = $query->get();

            foreach ($applications as $application) {
                $row = [];
                foreach ($selectedColumns as $column) {
                    switch ($column) {
                        case 'application_number':
                            $row[$column] = $application->application_number ?? '-';
                            break;
                        case 'applicant_name':
                            $row[$column] = $application->applicant_name ?? '-';
                            break;
                        case 'school_name':
                            $row[$column] = $application->applicant_school_name ?? '-';
                            break;
                        case 'grade':
                            $row[$column] = $application->applicant_grade ?? '-';
                            break;
                        case 'class':
                            $row[$column] = $application->applicant_class ?? '-';
                            break;
                        case 'birthday':
                            $row[$column] = $application->member?->birth_date?->format('Ymd') ?? '-';
                            break;
                        case 'gender':
                            $row[$column] = $application->member?->gender === 'male' ? '남' : ($application->member?->gender === 'female' ? '여' : '-');
                            break;
                        case 'payment_status':
                            $row[$column] = $application->payment_status_label ?? '-';
                            break;
                        case 'draw_result':
                            $row[$column] = $application->draw_result_label ?? '-';
                            break;
                        case 'applied_at':
                            $row[$column] = $application->applied_at?->format('Y.m.d H:i') ?? '-';
                            break;
                        default:
                            $row[$column] = '-';
                    }
                }
                $data->push($row);
            }
        } else {
            $query = GroupApplication::where('program_reservation_id', $reservationId)
                ->with(['participants'])
                ->get();

            $participantIds = [];
            if (!empty($selectedIds)) {
                $participantIds = $selectedIds;
            }

            foreach ($query as $groupApplication) {
                $participants = $groupApplication->participants;
                if (!empty($participantIds)) {
                    $participants = $participants->whereIn('id', $participantIds);
                }

                foreach ($participants as $participant) {
                    $row = [];
                    foreach ($selectedColumns as $column) {
                        switch ($column) {
                            case 'name':
                                $row[$column] = $participant->name ?? '-';
                                break;
                            case 'school_name':
                                $row[$column] = $groupApplication->school_name ?? '-';
                                break;
                            case 'grade':
                                $row[$column] = $participant->grade ?? '-';
                                break;
                            case 'class':
                                $row[$column] = $participant->class ?? '-';
                                break;
                            case 'birthday':
                                $row[$column] = $participant->birthday?->format('Ymd') ?? '-';
                                break;
                            default:
                                $row[$column] = '-';
                        }
                    }
                    $data->push($row);
                }
            }
        }

        return $data;
    }

    /**
     * CSV 형식으로 다운로드
     */
    private function exportToCsv(Collection $data, array $selectedColumns, array $columnMapping, string $filename): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($data, $selectedColumns, $columnMapping) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM 추가 (한글 깨짐 방지)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 헤더 작성
            $csvHeaders = [];
            foreach ($selectedColumns as $column) {
                $csvHeaders[] = $columnMapping[$column] ?? $column;
            }
            fputcsv($file, $csvHeaders);
            
            // 데이터 작성
            foreach ($data as $item) {
                $row = [];
                foreach ($selectedColumns as $column) {
                    $row[] = $item[$column] ?? '-';
                }
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }

    /**
     * 명단 리스트 다운로드 (리스트 페이지 항목 그대로)
     */
    public function downloadRosterList(array $selectedIds = []): StreamedResponse
    {
        // 개인 프로그램 조회
        $individualProgramQuery = ProgramReservation::query()
            ->byApplicationType('individual')
            ->active();

        // 단체 프로그램 조회
        $groupProgramQuery = ProgramReservation::query()
            ->byApplicationType('group')
            ->active();

        // 선택된 ID 필터
        if (!empty($selectedIds)) {
            $individualProgramQuery->whereIn('id', $selectedIds);
            $groupProgramQuery->whereIn('id', $selectedIds);
        }

        // 데이터 조회
        $individualPrograms = $individualProgramQuery->get();
        $groupPrograms = $groupProgramQuery->get();

        // 각 프로그램별 신청인원 계산
        $individualProgramIds = $individualPrograms->pluck('id')->toArray();
        $groupProgramIds = $groupPrograms->pluck('id')->toArray();

        $individualApplicationsCount = IndividualApplication::query()
            ->whereIn('program_reservation_id', $individualProgramIds)
            ->selectRaw('program_reservation_id, COUNT(*) as count')
            ->groupBy('program_reservation_id')
            ->pluck('count', 'program_reservation_id')
            ->toArray();

        $groupApplicationsCount = GroupApplication::query()
            ->whereIn('program_reservation_id', $groupProgramIds)
            ->selectRaw('program_reservation_id, SUM(applicant_count) as total')
            ->groupBy('program_reservation_id')
            ->pluck('total', 'program_reservation_id')
            ->toArray();

        // 통합 데이터 변환
        $mergedData = $this->mergePrograms($individualPrograms, $groupPrograms, $individualApplicationsCount, $groupApplicationsCount);

        // 정렬 (참가일 기준 내림차순)
        $mergedData = $mergedData->sortByDesc(function ($item) {
            return $item['participation_date'] ?? '';
        })->values();

        // 필터 옵션 가져오기
        $filters = $this->getFilterOptions();

        // 다운로드 데이터 변환
        $downloadData = $mergedData->map(function ($item) use ($filters) {
            return [
                'application_type' => $item['application_type'] === 'individual' ? '개인' : '단체',
                'reception_type' => $item['application_type'] === 'individual' 
                    ? ($filters['reception_types'][$item['reception_type']] ?? '-')
                    : '-',
                'education_type' => $filters['education_types'][$item['education_type']] ?? '-',
                'program_name' => $item['program_name'],
                'participation_date' => $item['participation_date_formatted'] ?? '-',
                'capacity' => $item['is_unlimited_capacity'] ? '무제한' : ($item['capacity'] ?? '-'),
                'applicant_count' => $item['applicant_count'],
            ];
        });

        // 컬럼 매핑
        $columnMapping = [
            'application_type' => '신청구분',
            'reception_type' => '신청유형',
            'education_type' => '교육유형',
            'program_name' => '프로그램명',
            'participation_date' => '참가일',
            'capacity' => '정원',
            'applicant_count' => '신청인원',
        ];

        // 모든 컬럼 선택
        $selectedColumns = array_keys($columnMapping);

        // 파일명 생성
        $filename = '명단_리스트_' . date('Ymd_His');

        // CSV로 다운로드
        return $this->exportToCsv($downloadData, $selectedColumns, $columnMapping, $filename);
    }
}

