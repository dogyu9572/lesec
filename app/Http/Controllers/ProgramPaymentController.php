<?php

namespace App\Http\Controllers;

use App\Http\Requests\Program\IndividualProgramApplyRequest;
use App\Models\GroupApplication;
use App\Models\IndividualApplication;
use App\Models\Member;
use App\Services\ProgramReservationService;
use App\Services\TossPaymentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramPaymentController extends Controller
{
    public function __construct(
        protected ProgramReservationService $programReservationService,
        protected TossPaymentsService $tossPaymentsService
    ) {}

    /**
     * 결제 준비 (orderId 발급, 세션 저장). 온라인 카드 결제 시에만 사용.
     */
    public function prepare(IndividualProgramApplyRequest $request, string $type): JsonResponse
    {
        $reservation = $this->programReservationService->getIndividualProgramById(
            (int) $request->input('program_reservation_id')
        );

        if (!$reservation || $reservation->education_type !== $type) {
            return response()->json(['message' => '유효하지 않은 프로그램입니다.'], 400);
        }

        $member = Auth::guard('member')->user();
        if (!$member instanceof Member) {
            return response()->json(['message' => '로그인 후 신청해 주세요.'], 401);
        }

        if ($member->member_type === 'teacher') {
            return response()->json(['message' => '개인 신청은 학생만 가능합니다.'], 400);
        }

        if ($member->member_type === 'student') {
            $schoolLevel = $member->school?->school_level;
            $programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
            if ($schoolLevel && $programLevel && $schoolLevel !== $programLevel) {
                $levelName = $schoolLevel === 'middle' ? '중등' : '고등';

                return response()->json(
                    ['message' => "회원님은 {$levelName} 프로그램만 신청 가능합니다."],
                    400
                );
            }
        }

        $memberContact = $member->contact;
        if (empty($memberContact)) {
            return response()->json(
                ['message' => '회원 정보에 연락처가 등록되어 있지 않습니다. 마이페이지에서 연락처를 먼저 등록해주세요.'],
                400
            );
        }

        $paymentMethods = is_array($reservation->payment_methods) ? $reservation->payment_methods : [];
        if (!in_array('online_card', $paymentMethods, true)) {
            return response()->json(
                ['message' => '이 프로그램은 온라인 카드 결제를 지원하지 않습니다.'],
                400
            );
        }

        $amount = (int) ($reservation->education_fee ?? 0);
        if ($amount <= 0) {
            return response()->json(
                ['message' => '참가비가 0원인 프로그램은 결제 없이 신청하기에서 바로 신청해 주세요.'],
                400
            );
        }

        $participationDate = $request->input('participation_date')
            ?? optional($reservation->education_start_date)->format('Y-m-d');
        if (empty($participationDate)) {
            return response()->json(
                ['message' => '참가일 정보를 확인할 수 없습니다. 관리자에게 문의해 주세요.'],
                400
            );
        }

        $orderId = $this->tossPaymentsService->generateOrderId();
        $request->session()->put("toss_order_{$orderId}", [
            'reservation_id' => $reservation->id,
            'participation_date' => $participationDate,
            'type' => $type,
            'amount' => $amount,
            'member_id' => $member->id,
        ]);

        $clientKey = $this->tossPaymentsService->getClientKey();
        if (empty($clientKey)) {
            return response()->json(['message' => '결제 설정을 확인할 수 없습니다.'], 500);
        }

        $base = rtrim(config('app.url'), '/');
        $successUrl = "{$base}/program/{$type}/payment/success";
        $failUrl = "{$base}/program/{$type}/payment/fail";

        return response()->json([
            'orderId' => $orderId,
            'amount' => $amount,
            'client_key' => $clientKey,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
        ]);
    }

    /**
     * 결제 성공 콜백 (토스 리다이렉트)
     */
    public function success(Request $request, string $type): RedirectResponse
    {
        $orderId = $request->query('orderId');
        $paymentKey = $request->query('paymentKey');
        $amount = $request->query('amount');

        if (empty($orderId) || empty($paymentKey) || $amount === null || $amount === '') {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '결제 정보가 올바르지 않습니다. 다시 신청해 주세요.']);
        }

        $amount = (int) $amount;
        $sessionKey = "toss_order_{$orderId}";
        $data = $request->session()->get($sessionKey);

        if (!$data || (int) ($data['amount'] ?? 0) !== $amount) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '결제 정보를 확인할 수 없습니다. 다시 신청해 주세요.']);
        }

        $reservation = $this->programReservationService->getIndividualProgramById((int) ($data['reservation_id'] ?? 0));
        $member = \App\Models\Member::find((int) ($data['member_id'] ?? 0));

        if (!$reservation || !$member || $reservation->education_type !== $type) {
            $request->session()->forget($sessionKey);

            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '신청 정보를 확인할 수 없습니다. 다시 신청해 주세요.']);
        }

        try {
            $this->tossPaymentsService->confirmPayment($paymentKey, $orderId, $amount);
        } catch (\Throwable $e) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => $e->getMessage() ?: '결제 승인에 실패했습니다.']);
        }

        $request->session()->forget($sessionKey);

        $memberContact = $member->contact;
        if (empty($memberContact)) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => '회원 연락처 정보를 확인할 수 없습니다. 마이페이지에서 등록 후 다시 시도해 주세요.']);
        }

        try {
            $application = $this->programReservationService->createIndividualApplication(
                $reservation,
                [
                    'member_id' => $member->id,
                    'participation_date' => $data['participation_date'] ?? $reservation->education_start_date?->format('Y-m-d'),
                    'program_name' => $reservation->program_name,
                    'participation_fee' => $reservation->education_fee,
                    'applicant_name' => $member->name,
                    'applicant_contact' => $memberContact,
                    'guardian_contact' => $member->parent_contact,
                    'applicant_school_name' => $member->school_name,
                    'applicant_grade' => $member->grade,
                    'applicant_class' => $member->class_number,
                    'payment_method' => 'online_card',
                    'payment_status' => IndividualApplication::PAYMENT_STATUS_PAID,
                    'draw_result' => IndividualApplication::DRAW_RESULT_PENDING,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('program.select.individual', [$type])
                ->withErrors(['application' => $e->getMessage()]);
        }

        $request->session()->put('individual_application.completed', [
            'application_id' => $application->id,
        ]);

        return redirect()->route('program.complete.individual', [$type]);
    }

    /**
     * 결제 실패 콜백 (토스 리다이렉트)
     */
    public function fail(Request $request, string $type): RedirectResponse
    {
        $message = $request->query('message');

        return redirect()
            ->route('program.select.individual', [$type])
            ->withErrors(['application' => $message ?: '결제가 취소되었거나 실패했습니다. 다시 시도해 주세요.']);
    }

    /**
     * 단체 신청 결제 준비 (orderId 발급, 세션 저장). 온라인 카드 결제 시에만 사용.
     */
    public function prepareGroup(Request $request, int $id): JsonResponse
    {
        $member = Auth::guard('member')->user();
        if (!$member instanceof Member) {
            return response()->json(['message' => '로그인 후 신청해 주세요.'], 401);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $member->id)
            ->with('reservation')
            ->first();

        if (!$application) {
            return response()->json(['message' => '유효하지 않은 신청입니다.'], 400);
        }

        $reservation = $application->reservation;
        if (!$reservation) {
            return response()->json(['message' => '프로그램 정보를 찾을 수 없습니다.'], 400);
        }

        $paymentMethods = is_array($reservation->payment_methods) ? $reservation->payment_methods : [];
        if (!in_array('online_card', $paymentMethods, true)) {
            return response()->json(
                ['message' => '이 프로그램은 온라인 카드 결제를 지원하지 않습니다.'],
                400
            );
        }

        $feePerPerson = (int) ($application->participation_fee ?? $reservation->education_fee ?? 0);
        if ($feePerPerson <= 0) {
            return response()->json(
                ['message' => '참가비가 0원인 프로그램은 결제 없이 저장해 주세요.'],
                400
            );
        }

        $participantCount = (int) $request->input('participant_count', 0);
        if ($participantCount <= 0) {
            return response()->json(
                ['message' => '명단을 1명 이상 입력한 뒤 결제해 주세요.'],
                400
            );
        }

        $amount = $feePerPerson * $participantCount;

        $orderId = $this->tossPaymentsService->generateOrderId();
        $request->session()->put("toss_group_order_{$orderId}", [
            'application_id' => $application->id,
            'amount' => $amount,
            'member_id' => $member->id,
        ]);

        $clientKey = $this->tossPaymentsService->getClientKey();
        if (empty($clientKey)) {
            return response()->json(['message' => '결제 설정을 확인할 수 없습니다.'], 500);
        }

        $base = rtrim(config('app.url'), '/');
        $successUrl = "{$base}/mypage/application_write/{$id}/payment/success";
        $failUrl = "{$base}/mypage/application_write/{$id}/payment/fail";

        return response()->json([
            'orderId' => $orderId,
            'amount' => $amount,
            'client_key' => $clientKey,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
        ]);
    }

    /**
     * 단체 신청 결제 성공 콜백 (토스 리다이렉트)
     */
    public function successGroup(Request $request, int $id): RedirectResponse
    {
        $orderId = $request->query('orderId');
        $paymentKey = $request->query('paymentKey');
        $amount = $request->query('amount');

        if (empty($orderId) || empty($paymentKey) || $amount === null || $amount === '') {
            return redirect()
                ->route('mypage.application_write', $id)
                ->withErrors(['error' => '결제 정보가 올바르지 않습니다. 다시 시도해 주세요.']);
        }

        $amount = (int) $amount;
        $sessionKey = "toss_group_order_{$orderId}";
        $data = $request->session()->get($sessionKey);

        if (!$data || (int) ($data['amount'] ?? 0) !== $amount || (int) ($data['application_id'] ?? 0) !== $id) {
            return redirect()
                ->route('mypage.application_write', $id)
                ->withErrors(['error' => '결제 정보를 확인할 수 없습니다. 다시 시도해 주세요.']);
        }

        $application = GroupApplication::where('id', $id)
            ->where('member_id', $data['member_id'] ?? 0)
            ->first();

        if (!$application) {
            $request->session()->forget($sessionKey);
            return redirect()
                ->route('mypage.application_write', $id)
                ->withErrors(['error' => '신청 정보를 확인할 수 없습니다. 다시 시도해 주세요.']);
        }

        try {
            $this->tossPaymentsService->confirmPayment($paymentKey, $orderId, $amount);
        } catch (\Throwable $e) {
            return redirect()
                ->route('mypage.application_write', $id)
                ->withErrors(['error' => $e->getMessage() ?: '결제 승인에 실패했습니다.']);
        }

        $request->session()->forget($sessionKey);

        $application->payment_method = 'online_card';
        $application->payment_status = 'paid';
        $application->save();

        return redirect()
            ->route('mypage.application_write', $id)
            ->with('success', '결제가 완료되었습니다.');
    }

    /**
     * 단체 신청 결제 실패 콜백 (토스 리다이렉트)
     */
    public function failGroup(Request $request, int $id): RedirectResponse
    {
        $message = $request->query('message');

        return redirect()
            ->route('mypage.application_write', $id)
            ->withErrors(['error' => $message ?: '결제가 취소되었거나 실패했습니다. 다시 시도해 주세요.']);
    }
}
