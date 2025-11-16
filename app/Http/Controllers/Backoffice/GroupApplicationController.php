<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\GroupApplications\StoreGroupApplicationRequest;
use App\Http\Requests\Backoffice\GroupApplications\GroupApplicationUpdateRequest;
use App\Http\Requests\Backoffice\GroupApplications\ParticipantRequest;
use App\Services\Backoffice\GroupApplicationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class GroupApplicationController extends BaseController
{
    protected GroupApplicationService $groupApplicationService;

    public function __construct(GroupApplicationService $groupApplicationService)
    {
        $this->groupApplicationService = $groupApplicationService;
    }

    /**
     * 단체 신청 내역 목록
     */
    public function index(Request $request)
    {
        $applications = $this->groupApplicationService->getFilteredApplications($request);
        $filters = $this->groupApplicationService->getFilterOptions();

        return $this->view('backoffice.group-applications.index', [
            'applications' => $applications,
            'filters' => $filters,
        ]);
    }

    /**
     * 단체 신청 신규 등록 화면
     */
    public function create()
    {
        $formOptions = $this->groupApplicationService->getFormOptions();

        return $this->view('backoffice.group-applications.create', [
            'formOptions' => $formOptions,
        ]);
    }

    /**
     * 단체 신청 신규 등록 처리
     */
    public function store(StoreGroupApplicationRequest $request): RedirectResponse
    {
        try {
            $this->groupApplicationService->createApplication($request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withErrors(['application' => $exception->getMessage()])
                ->withInput();
        } catch (\Throwable $throwable) {
            report($throwable);

            return back()
                ->withErrors(['application' => '단체 신청 등록 중 오류가 발생했습니다. 다시 시도해주세요.'])
                ->withInput();
        }

        return redirect()
            ->route('backoffice.group-applications.index')
            ->with('success', '단체 신청이 등록되었습니다.');
    }

    /**
     * 단체 신청 상세/수정 화면
     */
    public function edit(int $applicationId)
    {
        $application = $this->groupApplicationService->getApplicationDetail($applicationId);
        $formOptions = $this->groupApplicationService->getFormOptions();

        return $this->view('backoffice.group-applications.edit', [
            'application' => $application,
            'formOptions' => $formOptions,
        ]);
    }

    /**
     * 단체 신청 정보 업데이트
     */
    public function update(GroupApplicationUpdateRequest $request, int $applicationId): RedirectResponse
    {
        $this->groupApplicationService->updateApplication($applicationId, $request->validated());

        return redirect()
            ->route('backoffice.group-applications.index')
            ->with('success', '단체 신청 정보가 수정되었습니다.');
    }

    /**
     * 단체 신청 삭제
     */
    public function destroy(int $applicationId): RedirectResponse
    {
        $this->groupApplicationService->deleteApplication($applicationId);

        return redirect()
            ->route('backoffice.group-applications.index')
            ->with('success', '단체 신청이 삭제되었습니다.');
    }

    /**
     * 학교 검색
     */
    public function searchSchools(Request $request)
    {
        return response()->json(
            $this->groupApplicationService->searchSchools($request)
        );
    }

    /**
     * 회원 검색
     */
    public function searchMembers(Request $request)
    {
        return response()->json(
            $this->groupApplicationService->searchMembers($request)
        );
    }

    /**
     * 프로그램 검색
     */
    public function searchPrograms(Request $request)
    {
        return response()->json(
            $this->groupApplicationService->searchPrograms($request)
        );
    }

    /**
     * 신청 명단 다운로드
     */
    public function downloadRoster(int $applicationId)
    {
        return $this->groupApplicationService->downloadRoster($applicationId);
    }

    /**
     * 견적서 출력
     */
    public function downloadQuotation(int $applicationId)
    {
        return $this->groupApplicationService->downloadQuotation($applicationId);
    }

    /**
     * 명단 샘플 다운로드 (CSV)
     */
    public function downloadRosterSample(int $applicationId)
    {
        return $this->groupApplicationService->downloadRosterSample();
    }

    /**
     * CSV 일괄 업로드 (누적 추가)
     */
    public function uploadRoster(ParticipantRequest $request, int $applicationId)
    {
        $rows = $this->groupApplicationService->uploadRoster($applicationId, $request->file('csv_file'));

        return response()->json([
            'success' => true,
            'message' => "{$rows}명의 명단을 추가했습니다.",
        ]);
    }

    /**
     * 단일 참가자 추가 (행 추가 저장)
     */
    public function storeParticipant(ParticipantRequest $request, int $applicationId)
    {
        $this->groupApplicationService->storeParticipant($applicationId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => '참가자가 추가되었습니다.',
        ]);
    }

    /**
     * 참가자 수정
     */
    public function updateParticipant(ParticipantRequest $request, int $applicationId, int $participantId)
    {
        $this->groupApplicationService->updateParticipant($applicationId, $participantId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => '참가자 정보가 수정되었습니다.',
        ]);
    }

    /**
     * 참가자 삭제
     */
    public function destroyParticipant(int $applicationId, int $participantId)
    {
        $this->groupApplicationService->destroyParticipant($applicationId, $participantId);

        return response()->json([
            'success' => true,
            'message' => '참가자가 삭제되었습니다.',
        ]);
    }
}



