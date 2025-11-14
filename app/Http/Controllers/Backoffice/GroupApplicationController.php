<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\GroupApplications\StoreGroupApplicationRequest;
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
    public function update(Request $request, int $applicationId): RedirectResponse
    {
        $validated = $this->groupApplicationService->validateUpdateRequest($request);
        $this->groupApplicationService->updateApplication($applicationId, $validated);

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
}



