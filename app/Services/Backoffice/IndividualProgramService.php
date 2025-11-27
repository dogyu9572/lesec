<?php

namespace App\Services\Backoffice;

use App\Models\ProgramReservation;
use App\Services\Backoffice\Concerns\ValidatesScheduleAvailability;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class IndividualProgramService
{
    use ValidatesScheduleAvailability;
    /**
     * 필터링된 개인 프로그램 목록 조회
     */
    public function getFilteredPrograms($request): LengthAwarePaginator
    {
        $query = ProgramReservation::query()
            ->byApplicationType('individual')
            ->orderBy('created_at', 'desc');

        // 교육유형 필터
        if ($request->filled('education_type')) {
            $query->byEducationType($request->education_type);
        }

        // 접수유형 필터
        if ($request->filled('reception_type')) {
            $query->where('reception_type', $request->reception_type);
        }

        // 교육일정 기간 검색
        if ($request->filled('education_start_date')) {
            $query->where('education_start_date', '>=', $request->education_start_date);
        }
        if ($request->filled('education_end_date')) {
            $query->where('education_end_date', '<=', $request->education_end_date);
        }

        // 신청기간 기간 검색
        if ($request->filled('application_start_date')) {
            $query->where('application_start_date', '>=', $request->application_start_date);
        }
        if ($request->filled('application_end_date')) {
            $query->where('application_end_date', '<=', $request->application_end_date);
        }

        // 검색어 (프로그램명/작성자)
        if ($request->filled('search_keyword') && $request->filled('search_type')) {
            $keyword = $request->search_keyword;
            if ($request->search_type === 'program_name') {
                $query->where('program_name', 'like', "%{$keyword}%");
            } elseif ($request->search_type === 'author') {
                $query->where('author', 'like', "%{$keyword}%");
            } else {
                // 전체 검색
                $query->where(function ($q) use ($keyword) {
                    $q->where('program_name', 'like', "%{$keyword}%")
                      ->orWhere('author', 'like', "%{$keyword}%");
                });
            }
        }

        return $query->paginate(20)->withQueryString();
    }

    /**
     * 교육유형 목록 반환
     */
    public function getEducationTypes(): array
    {
        return [
            'middle_semester' => '중등학기',
            'middle_vacation' => '중등방학',
            'high_semester' => '고등학기',
            'high_vacation' => '고등방학',
            'special' => '특별프로그램',
        ];
    }

    /**
     * 접수유형 목록 반환 (개인 프로그램용)
     */
    public function getReceptionTypes(): array
    {
        return [
            'first_come' => '선착순',
            'lottery' => '추첨',
            'naver_form' => '네이버폼',
        ];
    }

    /**
     * 결제수단 목록 반환
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
     * 프로그램 생성
     */
    public function createProgram(array $data): ProgramReservation
    {
        $isSingleDay = !empty($data['is_single_day']);
        unset($data['is_single_day']);

        // application_type은 항상 'individual'
        $data['application_type'] = 'individual';
        
        // 결제수단 처리
        if (isset($data['payment_methods']) && is_array($data['payment_methods'])) {
            $data['payment_methods'] = array_values($data['payment_methods']);
        }

        if ($isSingleDay && !empty($data['education_start_date'])) {
            $data['education_end_date'] = $data['education_start_date'];
        }

        $this->ensureEducationScheduleIsAvailable(
            $data['education_start_date'] ?? null,
            $data['education_end_date'] ?? null
        );

        // 제한없음 체크 시 capacity는 null (추첨일 경우 제한없음 사용 불가)
        if (isset($data['is_unlimited_capacity']) && $data['is_unlimited_capacity'] && $data['reception_type'] !== 'lottery') {
            $data['capacity'] = null;
        }

        // 무료 체크 시 education_fee는 null
        if (isset($data['is_free']) && $data['is_free']) {
            $data['education_fee'] = null;
        } elseif (array_key_exists('education_fee', $data) && $data['education_fee'] !== null && $data['education_fee'] !== '') {
            $data['education_fee'] = (int) $data['education_fee'];
        }

        // 작성자 필드는 현재 로그인한 관리자 이름으로 자동 설정
        $data['author'] = Auth::user()?->name ?? null;
        
        // 신청 인원은 0으로 초기화
        $data['applied_count'] = 0;
        
        return ProgramReservation::create($data);
    }

    /**
     * 프로그램 수정
     */
    public function updateProgram(ProgramReservation $programReservation, array $data): bool
    {
        $isSingleDay = !empty($data['is_single_day']);
        unset($data['is_single_day']);

        $startDate = $data['education_start_date'] ?? $programReservation->education_start_date?->format('Y-m-d');
        $endDate = $data['education_end_date'] ?? $programReservation->education_end_date?->format('Y-m-d');

        if ($isSingleDay && $startDate) {
            $data['education_end_date'] = $startDate;
            $endDate = $startDate;
        }

        $this->ensureEducationScheduleIsAvailable($startDate, $endDate);

        // application_type은 변경 불가
        unset($data['application_type']);

        // 결제수단 처리
        if (isset($data['payment_methods']) && is_array($data['payment_methods'])) {
            $data['payment_methods'] = array_values($data['payment_methods']);
        }

        // 제한없음 체크 시 capacity는 null (추첨일 경우 제한없음 사용 불가)
        if (isset($data['is_unlimited_capacity']) && $data['is_unlimited_capacity'] && $data['reception_type'] !== 'lottery') {
            $data['capacity'] = null;
        } elseif (isset($data['is_unlimited_capacity']) && !$data['is_unlimited_capacity'] && !isset($data['capacity'])) {
            // 제한없음 해제 시 capacity는 필수
            $data['capacity'] = null;
        }

        // 무료 체크 시 education_fee는 null
        if (isset($data['is_free']) && $data['is_free']) {
            $data['education_fee'] = null;
        } elseif (isset($data['is_free']) && !$data['is_free']) {
            $data['education_fee'] = array_key_exists('education_fee', $data) && $data['education_fee'] !== null && $data['education_fee'] !== ''
                ? (int) $data['education_fee']
                : $programReservation->education_fee;
        }

        // 작성자 필드는 현재 로그인한 관리자 이름으로 자동 업데이트
        $data['author'] = Auth::user()?->name ?? $programReservation->author;
        
        return $programReservation->update($data);
    }

    /**
     * 프로그램 삭제
     */
    public function deleteProgram(ProgramReservation $programReservation): bool
    {
        return $programReservation->delete();
    }

    /**
     * 프로그램명 검색 (모달용)
     */
    public function searchPrograms(Request $request): LengthAwarePaginator
    {
        $query = ProgramReservation::query()
            ->select('program_name')
            ->groupBy('program_name')
            ->orderBy('program_name', 'asc');

        // 검색어 필터
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where('program_name', 'like', "%{$keyword}%");
        }

        // 교육유형 필터
        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        $perPage = $request->get('per_page', 10);
        
        return $query->paginate($perPage)->withQueryString();
    }
}

