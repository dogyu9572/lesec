<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\IndividualApplications\StoreIndividualApplicationRequest;
use App\Http\Requests\Backoffice\IndividualApplications\UpdateIndividualApplicationRequest;
use App\Http\Requests\Backoffice\IndividualApplications\BulkUploadRequest;
use App\Services\Backoffice\IndividualApplicationService;
use App\Models\IndividualApplication;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class IndividualApplicationController extends BaseController
{
    protected $individualApplicationService;

    public function __construct(IndividualApplicationService $individualApplicationService)
    {
        $this->individualApplicationService = $individualApplicationService;
    }

    /**
     * 개인 신청 내역 목록
     */
    public function index(Request $request)
    {
        $applications = $this->individualApplicationService->getFilteredApplications($request);
        $options = $this->getSharedOptions();

        return $this->view('backoffice.individual-applications.index', [
            'applications' => $applications,
            'receptionTypes' => $options['receptionTypes'],
            'educationTypes' => $options['educationTypes'],
            'drawResults' => $options['drawResults'],
            'paymentStatuses' => $options['paymentStatuses'],
            'paymentMethods' => $options['paymentMethods'],
        ]);
    }

    /**
     * 개인 신청 신규 등록 화면
     */
    public function create()
    {
        $options = $this->getSharedOptions();

        return $this->view('backoffice.individual-applications.create', [
            'receptionTypes' => $options['receptionTypes'],
            'educationTypes' => $options['educationTypes'],
            'drawResults' => $options['drawResults'],
            'paymentStatuses' => $options['paymentStatuses'],
            'paymentMethods' => $options['paymentMethods'],
        ]);
    }

    /**
     * 개인 신청 수정 화면
     */
    public function edit(IndividualApplication $application)
    {
        $application->load(['reservation', 'member']);
        $options = $this->getSharedOptions();

        return $this->view('backoffice.individual-applications.edit', [
            'application' => $application,
            'receptionTypes' => $options['receptionTypes'],
            'educationTypes' => $options['educationTypes'],
            'drawResults' => $options['drawResults'],
            'paymentStatuses' => $options['paymentStatuses'],
            'paymentMethods' => $options['paymentMethods'],
        ]);
    }

    /**
     * 개인 신청 신규 등록 처리
     */
    public function store(StoreIndividualApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['reception_type'] !== 'lottery') {
            $validated['draw_result'] = IndividualApplication::DRAW_RESULT_PENDING;
        }

        try {
            $this->individualApplicationService->createApplication($validated);
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withErrors(['application' => $exception->getMessage()])
                ->withInput();
        } catch (\Throwable $throwable) {
            report($throwable);

            return back()
                ->withErrors(['application' => '신청 등록 중 오류가 발생했습니다. 다시 시도해 주세요.'])
                ->withInput();
        }

        return redirect()
            ->route('backoffice.individual-applications.index')
            ->with('success', '신청이 등록되었습니다.');
    }

    /**
     * 개인 신청 정보 업데이트
     */
    public function update(UpdateIndividualApplicationRequest $request, IndividualApplication $application): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['reception_type'] !== 'lottery') {
            $validated['draw_result'] = IndividualApplication::DRAW_RESULT_PENDING;
        }

        $this->individualApplicationService->updateApplication($application, $validated);

        return redirect()
            ->route('backoffice.individual-applications.index')
            ->with('success', '신청 정보가 수정되었습니다.');
    }

    /**
     * 개인 신청 삭제
     */
    public function destroy(IndividualApplication $application): RedirectResponse
    {
        $this->individualApplicationService->deleteApplication($application);

        return redirect()
            ->route('backoffice.individual-applications.index')
            ->with('success', '신청이 삭제되었습니다.');
    }

    /**
     * 회원 검색 (모달용)
     */
    public function searchMembers(Request $request)
    {
        try {
            $members = $this->individualApplicationService->searchMembers($request);
            
            return response()->json([
                'members' => $members->items(),
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '회원 검색 중 오류가 발생했습니다.',
                'members' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ]
            ], 500);
        }
    }

    /**
     * 프로그램 검색 (모달용)
     */
    public function searchPrograms(Request $request)
    {
        try {
            $programs = $this->individualApplicationService->searchPrograms($request);
            
            return response()->json([
                'programs' => $programs->items(),
                'pagination' => [
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage(),
                    'per_page' => $programs->perPage(),
                    'total' => $programs->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로그램 검색 중 오류가 발생했습니다.',
                'programs' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ]
            ], 500);
        }
    }

    /**
     * 샘플 파일 다운로드
     */
    public function downloadSample()
    {
        return $this->individualApplicationService->downloadSample();
    }

    /**
     * 일괄 업로드 처리
     */
    public function bulkUpload(BulkUploadRequest $request): JsonResponse
    {
        try {
            $result = $this->individualApplicationService->bulkUploadApplications($request->file('file'));

            $message = "{$result['success_count']}건의 신청이 등록되었습니다.";
            if ($result['error_count'] > 0) {
                $message .= " ({$result['error_count']}건 실패)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $result['success_count'],
                'error_count' => $result['error_count'],
                'errors' => $result['errors'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => '일괄 업로드 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getSharedOptions(): array
    {
        return [
            'receptionTypes' => $this->individualApplicationService->getReceptionTypes(),
            'educationTypes' => $this->individualApplicationService->getEducationTypes(),
            'drawResults' => $this->individualApplicationService->getDrawResults(),
            'paymentStatuses' => $this->individualApplicationService->getPaymentStatuses(),
            'paymentMethods' => $this->individualApplicationService->getPaymentMethods(),
        ];
    }
}

