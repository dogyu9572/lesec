<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\Schedules\ScheduleStoreRequest;
use App\Http\Requests\Backoffice\Schedules\ScheduleUpdateRequest;
use App\Services\Backoffice\ScheduleService;
use App\Services\ProgramReservationService;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ScheduleController extends BaseController
{
    protected ScheduleService $scheduleService;
    protected ProgramReservationService $programReservationService;

    public function __construct(
        ScheduleService $scheduleService,
        ProgramReservationService $programReservationService
    ) {
        $this->scheduleService = $scheduleService;
        $this->programReservationService = $programReservationService;
    }

    /**
     * 일정관리 메인 페이지
     */
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        // 일정 목록 조회
        $schedules = $this->scheduleService->getList();

        // 일정 데이터를 날짜별로 그룹화
        $schedulesByDate = [];
        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->start_date);
            $end = Carbon::parse($schedule->end_date);
            $current = (clone $start);
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($schedulesByDate[$dateKey])) {
                    $schedulesByDate[$dateKey] = [];
                }
                $schedulesByDate[$dateKey][] = $schedule;
                $current->addDay();
            }
        }

        // 현재 월에 해당하는 일정 목록
        $monthlySchedules = $schedules->filter(function (Schedule $schedule) use ($startOfMonth, $endOfMonth) {
            $start = Carbon::parse($schedule->start_date);
            $end = Carbon::parse($schedule->end_date);
            return $start->lte($endOfMonth) && $end->gte($startOfMonth);
        })->sortBy(function (Schedule $schedule) {
            return Carbon::parse($schedule->start_date)->timestamp;
        })->values();

        // 해당 월의 모든 프로그램 조회 (개인/단체 모두)
        $programs = \App\Models\ProgramReservation::query()
            ->active()
            ->whereDate('education_start_date', '<=', $endOfMonth)
            ->whereDate('education_end_date', '>=', $startOfMonth)
            ->orderBy('education_start_date', 'asc')
            ->get();
        
        // 날짜별 그룹화
        $programsByDate = $this->programReservationService->groupProgramsByDate($programs);
        
        // 교육불가 일정 조회
        $disabledDates = $this->scheduleService->getDisabledDates($year, $month);

        // 캘린더 생성
        $calendar = $this->programReservationService->generateCalendar($year, $month, $programsByDate, $disabledDates);

        return $this->view('backoffice.schedules.index', [
            'calendar' => $calendar,
            'schedules' => $schedules,
            'schedulesByDate' => $schedulesByDate,
            'monthlySchedules' => $monthlySchedules,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * 일정 등록 폼 (AJAX로 모달 처리)
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('backoffice.schedules.partials.form', [
                'schedule' => null,
            ])->render(),
        ]);
    }

    /**
     * 일정 저장
     */
    public function store(ScheduleStoreRequest $request)
    {
        try {
            $schedule = $this->scheduleService->create($request->validated());

            return redirect()->route('backoffice.schedules.index')
                ->with('success', '일정이 등록되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '일정 등록 중 오류가 발생했습니다.');
        }
    }

    /**
     * 일정 수정 폼 (AJAX로 모달 처리)
     */
    public function edit(Schedule $schedule)
    {
        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'start_date' => Carbon::parse($schedule->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($schedule->end_date)->format('Y-m-d'),
                'content' => $schedule->content,
                'disable_application' => $schedule->disable_application,
            ],
        ]);
    }

    /**
     * 일정 수정
     */
    public function update(ScheduleUpdateRequest $request, Schedule $schedule)
    {
        try {
            $this->scheduleService->update($schedule, $request->validated());

            return redirect()->route('backoffice.schedules.index')
                ->with('success', '일정이 수정되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '일정 수정 중 오류가 발생했습니다.');
        }
    }

    /**
     * 일정 삭제
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $this->scheduleService->delete($schedule);

            return redirect()->route('backoffice.schedules.index')
                ->with('success', '일정이 삭제되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '일정 삭제 중 오류가 발생했습니다.');
        }
    }

    /**
     * 특정 날짜의 일정 조회 (AJAX)
     */
    public function getSchedulesByDate(Request $request): JsonResponse
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => '날짜가 필요합니다.',
            ], 400);
        }

        try {
            $schedules = $this->scheduleService->getSchedulesByDate($date);
            
            // 해당 날짜의 프로그램 조회
            $dateCarbon = Carbon::parse($date);
            $programs = \App\Models\ProgramReservation::query()
                ->active()
                ->whereDate('education_start_date', '<=', $dateCarbon)
                ->whereDate('education_end_date', '>=', $dateCarbon)
                ->orderBy('education_start_date', 'asc')
                ->get();

            $schedulesData = $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'start_date' => Carbon::parse($schedule->start_date)->format('Y.m.d'),
                    'end_date' => Carbon::parse($schedule->end_date)->format('Y.m.d'),
                    'content' => $schedule->content ?? '',
                    'disable_application' => $schedule->disable_application,
                    'type' => 'schedule',
                ];
            })->toArray();

            $programsData = $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'title' => $program->program_name,
                    'start_date' => Carbon::parse($program->education_start_date)->format('Y.m.d'),
                    'end_date' => Carbon::parse($program->education_end_date)->format('Y.m.d'),
                    'content' => $program->education_type_name ?? '',
                    'type' => 'program',
                    'applied_count' => $program->applied_count ?? 0,
                    'capacity' => $program->capacity ?? null,
                    'is_unlimited_capacity' => $program->is_unlimited_capacity ?? false,
                ];
            })->toArray();

            // 일정과 프로그램을 합쳐서 정렬
            $allData = array_merge($schedulesData, $programsData);
            usort($allData, function($a, $b) {
                return strcmp($a['start_date'], $b['start_date']);
            });

            return response()->json([
                'success' => true,
                'data' => $allData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일정을 조회하는 중 오류가 발생했습니다.',
            ], 500);
        }
    }
}
