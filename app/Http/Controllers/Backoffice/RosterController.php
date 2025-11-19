<?php

namespace App\Http\Controllers\Backoffice;

use App\Services\Backoffice\RosterService;
use App\Models\ProgramReservation;
use Illuminate\Http\Request;

class RosterController extends BaseController
{
    protected RosterService $rosterService;

    public function __construct(RosterService $rosterService)
    {
        $this->rosterService = $rosterService;
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
}