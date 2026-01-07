<?php

namespace App\Http\Controllers;

use App\Http\Requests\Program\IndividualProgramApplyRequest;
use App\Http\Requests\Program\GroupProgramApplyRequest;
use App\Models\Program;
use App\Models\IndividualApplication;
use App\Models\ProgramReservation;
use App\Models\GroupApplication;
use App\Models\Member;
use App\Services\ProgramService;
use App\Services\ProgramReservationService;
use App\Services\Backoffice\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramController extends Controller
{
    protected $programService;
    protected $programReservationService;
    protected ScheduleService $scheduleService;
    
    public function __construct(
        ProgramService $programService,
        ProgramReservationService $programReservationService,
        ScheduleService $scheduleService
    ) {
        $this->programService = $programService;
        $this->programReservationService = $programReservationService;
        $this->scheduleService = $scheduleService;
    }
    
    /**
     * 유형 선택 페이지
     */
    public function show($type)
    {
        $member = Auth::guard('member')->user();
        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        
        return view('program.show', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type', 'member'));
    }
    
    /**
     * 단체 신청 페이지
     */
    public function applyGroup($type)
    {
        // 학생은 단체 신청 불가
        $member = Auth::guard('member')->user();
        if ($member && $member instanceof Member && $member->member_type === 'student') {
            return redirect()
                ->route('program.show', $type)
                ->withErrors(['access' => '단체 신청은 교사만 가능합니다. 개인 신청을 이용해주세요.']);
        }

        // 교사의 경우 학교급과 프로그램 타입 일치 여부 체크
        if ($member && $member instanceof Member && $member->member_type === 'teacher') {
            $schoolLevel = $member->school?->school_level;
            $programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
            
            if ($schoolLevel && $programLevel && $schoolLevel !== $programLevel) {
                $levelName = $schoolLevel === 'middle' ? '중등' : '고등';
                return redirect()
                    ->route('program.show', $type)
                    ->withErrors(['access' => "회원님은 {$levelName} 프로그램만 신청 가능합니다."]);
            }
        }

        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        $programService = $this->programService;
        $periodSummary = $this->formatProgramPeriod($program);
        
        return view('program.apply-group', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type', 'programService', 'periodSummary', 'member'));
    }
    
    /**
     * 개인 신청 페이지
     */
    public function applyIndividual($type)
    {
        $member = Auth::guard('member')->user();
        $program = $this->programService->getProgramByType($type);
        $gNum = "01"; 
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램"; 
        $sName = $this->programService->getTypeName($type);
        $programService = $this->programService;
        $periodSummary = $this->formatProgramPeriod($program);
        
        return view('program.apply-individual', compact('program', 'gNum', 'sNum', 'gName', 'sName', 'type', 'programService', 'periodSummary', 'member'));
    }

    /**
     * 단체 교육 선택 페이지 (캘린더)
     */
    public function selectGroup($type, Request $request)
    {
        // 학생은 단체 신청 불가
        $member = Auth::guard('member')->user();
        if ($member && $member instanceof Member && $member->member_type === 'student') {
            return redirect()
                ->route('program.show', $type)
                ->withErrors(['access' => '단체 신청은 교사만 가능합니다. 개인 신청을 이용해주세요.']);
        }

        // 교사의 경우 학교급과 프로그램 타입 일치 여부 체크
        if ($member && $member instanceof Member && $member->member_type === 'teacher') {
            $schoolLevel = $member->school?->school_level;
            $programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
            
            if ($schoolLevel && $programLevel && $schoolLevel !== $programLevel) {
                $levelName = $schoolLevel === 'middle' ? '중등' : '고등';
                return redirect()
                    ->route('program.show', $type)
                    ->withErrors(['access' => "회원님은 {$levelName} 프로그램만 신청 가능합니다."]);
            }
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        // 프로그램 조회
        $programs = $this->programReservationService->getProgramsByMonth($type, 'group', $year, $month);
        
        // 날짜별 그룹화
        $programsByDate = $this->programReservationService->groupProgramsByDate($programs);
        
        $disabledDates = $this->scheduleService->getDisabledDates($year, $month);
        // 캘린더 생성
        $calendar = $this->programReservationService->generateCalendar($year, $month, $programsByDate, $disabledDates);
        
        // 페이지 정보
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);
        
        return view('program.select-group', compact('gNum', 'sNum', 'gName', 'sName', 'programs', 'programsByDate', 'calendar', 'year', 'month', 'type'));
    }

    /**
     * 개인 교육 선택 페이지 (캘린더)
     */
    public function selectIndividual($type, Request $request)
    {
        // 교사는 개인 신청 불가
        $member = Auth::guard('member')->user();
        if ($member && $member instanceof Member && $member->member_type === 'teacher') {
            return redirect()
                ->route('program.show', $type)
                ->withErrors(['access' => '개인 신청은 학생만 가능합니다. 단체 신청을 이용해주세요.']);
        }

        // 학생의 경우 학교급과 프로그램 타입 일치 여부 체크
        if ($member && $member instanceof Member && $member->member_type === 'student') {
            $schoolLevel = $member->school?->school_level;
            $programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
            
            if ($schoolLevel && $programLevel && $schoolLevel !== $programLevel) {
                $levelName = $schoolLevel === 'middle' ? '중등' : '고등';
                return redirect()
                    ->route('program.show', $type)
                    ->withErrors(['access' => "회원님은 {$levelName} 프로그램만 신청 가능합니다."]);
            }
        }

        $filterMonthInput = $request->input('month');
        $filterMonth = is_numeric($filterMonthInput) ? (int) $filterMonthInput : null;
        $filterProgram = $request->input('program');
        $filterStatusInput = $request->input('status', '');
        $allowedStatuses = ['', 'apply', 'waitlist', 'scheduled'];
        $filterStatus = in_array($filterStatusInput, $allowedStatuses, true) ? $filterStatusInput : '';
        $filterSortInput = $request->input('sort', 'education_date');
        $allowedSorts = ['education_date', 'application_start_date'];
        $filterSort = in_array($filterSortInput, $allowedSorts, true) ? $filterSortInput : 'education_date';

        $allPrograms = $this->programReservationService->getIndividualPrograms($type);
        $programs = $this->programReservationService->filterIndividualPrograms(
            $allPrograms,
            $filterMonth,
            ($filterProgram !== null && $filterProgram !== '') ? $filterProgram : null,
            $filterStatus !== '' ? $filterStatus : null
        );

        $availableMonths = $this->programReservationService->getIndividualProgramMonths($allPrograms);
        $availableProgramNames = $this->programReservationService->getIndividualProgramNames($allPrograms);

        /** @var Member|null $member */
        $member = Auth::guard('member')->user();
        if ($member && $member instanceof Member && $member->member_type === 'student') {
            $applications = IndividualApplication::query()
                ->where('member_id', $member->id)
                ->get(['program_reservation_id', 'program_name']);

            $appliedPrefixes = $applications
                ->map(function (IndividualApplication $application) {
                    return Str::upper(Str::substr($application->program_name, 0, 2));
                })
                ->filter()
                ->unique();

            $appliedReservationIds = $applications
                ->pluck('program_reservation_id')
                ->filter()
                ->unique();

            $programs = $programs->map(function (ProgramReservation $program) use ($appliedPrefixes, $appliedReservationIds) {
                $prefix = Str::upper(Str::substr($program->program_name, 0, 2));

                if ($appliedReservationIds->contains($program->id)) {
                    $program->application_status = 'applied';
                } elseif ($appliedPrefixes->contains($prefix)) {
                    $program->application_status = 'blocked';
                } else {
                    $program->application_status = 'available';
                }

                return $program;
            });
        }

        // 정렬 적용 (내림차순: 최신 날짜가 먼저)
        if ($filterSort === 'education_date') {
            $programs = $programs->sortByDesc(function ($program) {
                return $program->education_start_date ? $program->education_start_date->timestamp : 0;
            })->values();
        } elseif ($filterSort === 'application_start_date') {
            $programs = $programs->sortByDesc(function ($program) {
                return $program->application_start_date ? $program->application_start_date->timestamp : 0;
            })->values();
        }

        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);

        $totalCount = $programs->count();
        
        // 페이지네이션 적용
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $items = $programs->forPage($currentPage, $perPage);
        $programsPaginated = new LengthAwarePaginator(
            $items,
            $totalCount,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('program.select-individual', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'type' => $type,
            'programs' => $programsPaginated,
            'totalPrograms' => $totalCount,
            'availableMonths' => $availableMonths,
            'availableProgramNames' => $availableProgramNames,
            'filterMonth' => $filterMonth,
            'filterProgram' => $filterProgram,
            'filterStatus' => $filterStatus,
            'filterSort' => $filterSort,
            'member' => $member,
        ]);
    }

    /**
     * 개인 신청 저장
     */
    public function submitIndividualApplication(IndividualProgramApplyRequest $request, $type): RedirectResponse
    {
        $reservation = $this->programReservationService->getIndividualProgramById((int) $request->input('program_reservation_id'));

        if (!$reservation || $reservation->education_type !== $type) {
            return back()
                ->withErrors(['application' => '유효하지 않은 프로그램입니다.'])
                ->withInput();
        }

        $validated = $request->validated();
        $member = Auth::guard('member')->user();

        if (!$member instanceof Member) {
            return redirect()
                ->route('member.login')
                ->withErrors(['login' => '로그인 후 신청해 주세요.']);
        }

        // 교사는 개인 신청 불가
        if ($member->member_type === 'teacher') {
            return back()
                ->withErrors(['application' => '개인 신청은 학생만 가능합니다.'])
                ->withInput();
        }

        // 학생의 경우 학교급과 프로그램 타입 일치 여부 체크
        if ($member->member_type === 'student') {
            $schoolLevel = $member->school?->school_level;
            $programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
            
            if ($schoolLevel && $programLevel && $schoolLevel !== $programLevel) {
                $levelName = $schoolLevel === 'middle' ? '중등' : '고등';
                return back()
                    ->withErrors(['application' => "회원님은 {$levelName} 프로그램만 신청 가능합니다."])
                    ->withInput();
            }
        }

        $memberForCreation = $member;

        $memberContact = $member->contact;
        if (empty($memberContact)) {
            return back()
                ->withErrors(['application' => '회원 정보에 연락처가 등록되어 있지 않습니다. 마이페이지에서 연락처를 먼저 등록해주세요.']);
        }

        $participationDate = $validated['participation_date'] ?? optional($reservation->education_start_date)->toDateString();

        if ($participationDate === null) {
            return back()
                ->withErrors(['application' => '참가일 정보를 확인할 수 없습니다. 관리자에게 문의해 주세요.'])
                ->withInput();
        }

        $paymentMethod = null;
        if (is_array($reservation->payment_methods) && count($reservation->payment_methods) > 0) {
            $paymentMethod = $reservation->payment_methods[0];
        }

        try {
            $application = $this->programReservationService->createIndividualApplication(
                $reservation,
                [
                    'member_id' => $member->id,
                    'participation_date' => $participationDate,
                    'program_name' => $reservation->program_name,
                    'participation_fee' => $reservation->education_fee,
                    'applicant_name' => $member->name,
                    'applicant_contact' => $memberContact,
                    'guardian_contact' => $member->parent_contact,
                    'applicant_school_name' => $member->school_name,
                    'applicant_grade' => $member->grade,
                    'applicant_class' => $member->class_number,
                    'payment_method' => $paymentMethod,
                    'payment_status' => IndividualApplication::PAYMENT_STATUS_UNPAID,
                    'draw_result' => IndividualApplication::DRAW_RESULT_PENDING,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['application' => $e->getMessage()])
                ->withInput();
        }

        $request->session()->put('individual_application.completed', [
            'application_id' => $application->id,
        ]);

        return redirect()->route('program.complete.individual', [$type]);
    }

    /**
     * 단체 신청 저장
     */
    public function submitGroupApplication(GroupProgramApplyRequest $request, $type): JsonResponse
    {
        $reservationId = (int) $request->input('program_reservation_id');
        $reservation = $this->programReservationService->getGroupProgramById($reservationId);

        if (!$reservation || $reservation->education_type !== $type) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 프로그램입니다.',
            ], 400);
        }

        $member = Auth::guard('member')->user();

        if (!$member instanceof Member) {
            return response()->json([
                'success' => false,
                'message' => '로그인 후 신청해 주세요.',
            ], 401);
        }

        // 학생은 단체 신청 불가
        if ($member->member_type === 'student') {
            return response()->json([
                'success' => false,
                'message' => '단체 신청은 교사만 가능합니다.',
            ], 403);
        }

        $memberContact = $member->contact;
        if (empty($memberContact)) {
            return response()->json([
                'success' => false,
                'message' => '회원 정보에 연락처가 등록되어 있지 않습니다. 마이페이지에서 연락처를 먼저 등록해주세요.',
            ], 400);
        }

        $participationDate = optional($reservation->education_start_date)->toDateString();

        if ($participationDate === null) {
            return response()->json([
                'success' => false,
                'message' => '참가일 정보를 확인할 수 없습니다. 관리자에게 문의해 주세요.',
            ], 400);
        }

        $paymentMethod = null;
        if (is_array($reservation->payment_methods) && count($reservation->payment_methods) > 0) {
            $paymentMethod = $reservation->payment_methods[0];
        }

        try {
            $application = $this->programReservationService->createGroupApplication(
                $reservation,
                [
                    'member_id' => $member->id,
                    'participation_date' => $participationDate,
                    'applicant_count' => (int) $request->input('applicant_count'),
                    'participation_fee' => $reservation->education_fee,
                    'applicant_name' => $member->name,
                    'applicant_contact' => $memberContact,
                    'school_name' => $member->school_name,
                    'school_level' => $member->school?->school_level ?? null,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'unpaid',
                    'application_status' => 'pending',
                    'reception_status' => 'application',
                ]
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        $request->session()->put('group_application.completed', [
            'application_id' => $application->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => '신청이 완료되었습니다.',
            'redirect_url' => route('program.complete.group', [$type]),
        ]);
    }

    /**
     * 단체 신청 완료 페이지
     */
    public function completeGroup(Request $request, $type)
    {
        $program = $this->programService->getProgramByType($type);
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);

        $sessionData = $request->session()->pull('group_application.completed');

        if (!$sessionData || empty($sessionData['application_id'])) {
            return redirect()
                ->route('program.select.group', [$type])
                ->withErrors(['application' => '정상적인 신청 완료 단계가 아닙니다. 다시 신청을 진행해주세요.']);
        }

        $application = GroupApplication::query()
            ->with('reservation')
            ->find($sessionData['application_id']);

        if (!$application) {
            return redirect()
                ->route('program.select.group', [$type])
                ->withErrors(['application' => '신청 정보를 찾을 수 없습니다. 다시 신청을 진행해주세요.']);
        }

        if ($application->reservation && $application->reservation->education_type !== $type) {
            return redirect()
                ->route('program.select.group', [$type])
                ->withErrors(['application' => '선택하신 교육유형과 신청 정보가 일치하지 않습니다.']);
        }

        $completionContext = $this->buildGroupCompletionContext($application);

        return view('program.complete-group', compact('gNum', 'sNum', 'gName', 'sName', 'type', 'program', 'completionContext'));
    }

    /**
     * 개인 신청 완료 페이지
     */
    public function completeIndividual(Request $request, $type)
    {
        $program = $this->programService->getProgramByType($type);
        $gNum = "01";
        $sNum = $this->programService->getSubMenuNumber($type);
        $gName = "프로그램";
        $sName = $this->programService->getTypeName($type);

        $sessionData = $request->session()->pull('individual_application.completed');

        if (!$sessionData || empty($sessionData['application_id'])) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '정상적인 신청 완료 단계가 아닙니다. 다시 신청을 진행해주세요.']);
        }

        $application = IndividualApplication::query()
            ->with('reservation')
            ->find($sessionData['application_id']);

        if (!$application) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '신청 정보를 찾을 수 없습니다. 다시 신청을 진행해주세요.']);
        }

        if ($application->reservation && $application->reservation->education_type !== $type) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '선택하신 교육유형과 신청 정보가 일치하지 않습니다.']);
        }

        $completionContext = $this->buildIndividualCompletionContext($application);

        return view('program.complete-individual', compact('gNum', 'sNum', 'gName', 'sName', 'type', 'program', 'completionContext'));
    }

    private function formatProgramPeriod(?Program $program): ?string
    {
        if (!$program || !$program->period_start || !$program->period_end) {
            return null;
        }

        $startTimestamp = strtotime((string) $program->period_start);
        $endTimestamp = strtotime((string) $program->period_end);

        if ($startTimestamp === false || $endTimestamp === false) {
            return null;
        }

        $days = ['일', '월', '화', '수', '목', '금', '토'];
        $startDay = $days[(int) date('w', $startTimestamp)] ?? '';
        $endDay = $days[(int) date('w', $endTimestamp)] ?? '';

        return sprintf(
            '%s(%s) ~ %s(%s)',
            date('Y년 n월 j일', $startTimestamp),
            $startDay,
            date('n월 j일', $endTimestamp),
            $endDay
        );
    }

    private function buildGroupCompletionContext(GroupApplication $application): array
    {
        $participationTimestamp = $application->participation_date
            ? strtotime((string) $application->participation_date)
            : false;

        $appliedTimestamp = $application->applied_at
            ? strtotime((string) $application->applied_at)
            : false;

        $programName = '';
        if ($application->reservation) {
            $programName = $application->reservation->program_name ?? '';
        }

        return [
            'program_name' => $programName,
            'education_date' => $participationTimestamp ? date('Y.m.d', $participationTimestamp) : '교육일이 지정되지 않았습니다.',
            'application_number' => $application->application_number,
            'applicant_name' => $application->applicant_name,
            'applicant_count' => $application->applicant_count . '명',
            'submitted_at' => $appliedTimestamp ? date('Y.m.d H:i', $appliedTimestamp) : now()->format('Y.m.d H:i'),
        ];
    }

    private function buildIndividualCompletionContext(IndividualApplication $application): array
    {
        $appliedTimestamp = $application->applied_at
            ? strtotime((string) $application->applied_at)
            : false;

        // 참가일: reservation 관계에서 시작일과 종료일 가져오기
        $educationDate = '교육일이 지정되지 않았습니다.';
        if ($application->reservation) {
            $startDate = $application->reservation->education_start_date;
            $endDate = $application->reservation->education_end_date;
            
            if ($startDate) {
                $days = ['일', '월', '화', '수', '목', '금', '토'];
                $startTimestamp = strtotime((string) $startDate);
                $startDayName = $days[(int) date('w', $startTimestamp)] ?? '';
                $startFormatted = date('Y.m.d', $startTimestamp) . '(' . $startDayName . ')';
                
                if ($endDate && $startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                    $endTimestamp = strtotime((string) $endDate);
                    $endDayName = $days[(int) date('w', $endTimestamp)] ?? '';
                    $endFormatted = date('Y.m.d', $endTimestamp) . '(' . $endDayName . ')';
                    $educationDate = $startFormatted . ' ~ ' . $endFormatted;
                } else {
                    $educationDate = $startFormatted;
                }
            }
        } elseif ($application->participation_date) {
            $participationTimestamp = strtotime((string) $application->participation_date);
            $days = ['일', '월', '화', '수', '목', '금', '토'];
            $dayName = $days[(int) date('w', $participationTimestamp)] ?? '';
            $educationDate = date('Y.m.d', $participationTimestamp) . '(' . $dayName . ')';
        }
 
        return [
            'program_name' => $application->program_name ?? '',
            'education_date' => $educationDate,
            'application_number' => $application->application_number,
            'applicant_name' => $application->applicant_name,
            'applicant_phone' => $this->formatContactNumber($application->applicant_contact),
            'submitted_at' => $appliedTimestamp ? date('Y.m.d H:i', $appliedTimestamp) : now()->format('Y.m.d H:i'),
        ];
    }

    private function formatContactNumber(?string $digits): string
    {
        if (empty($digits)) {
            return '-';
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $digits);
        }

        return $digits;
    }
}
