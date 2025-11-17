<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\ReservationCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationCalendarController extends BaseController
{
    protected ReservationCalendarService $reservationCalendarService;

    public function __construct(ReservationCalendarService $reservationCalendarService)
    {
        $this->reservationCalendarService = $reservationCalendarService;
    }

    /**
     * 예약 캘린더 메인 페이지
     */
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        // 월별 프로그램 조회
        $programs = $this->reservationCalendarService->getProgramsByMonth($year, $month);

        // 날짜별 그룹화
        $programsByDate = $this->reservationCalendarService->groupProgramsByDate($programs);

        // 캘린더 생성
        $calendar = $this->reservationCalendarService->generateCalendar($year, $month, $programsByDate);

        return $this->view('backoffice.reservations.calendar', [
            'calendar' => $calendar,
            'programsByDate' => $programsByDate,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * 특정 날짜의 예약 내역 조회 (AJAX)
     */
    public function getReservationsByDate(Request $request): JsonResponse
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => '날짜가 필요합니다.',
            ], 400);
        }

        try {
            $reservations = $this->reservationCalendarService->getReservationsByDate($date);

            return response()->json([
                'success' => true,
                'data' => $reservations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '예약 내역을 조회하는 중 오류가 발생했습니다.',
            ], 500);
        }
    }

}

