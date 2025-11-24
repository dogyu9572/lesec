<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\MailSms\StoreMailSmsMessageRequest;
use App\Http\Requests\Backoffice\MailSms\UpdateMailSmsMessageRequest;
use App\Models\MailSmsMessage;
use App\Models\Member;
use App\Models\MemberGroup;
use App\Services\Backoffice\MailSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MailSmsController extends BaseController
{
    public function __construct(
        private readonly MailSmsService $mailSmsService
    ) {
    }

    /**
     * 발송 목록
     */
    public function index(Request $request)
    {
        $messages = $this->mailSmsService->getMessages($request);

        return $this->view('backoffice.mail-sms.index', [
            'messages' => $messages,
            'messageTypes' => $this->messageTypes(),
            'statuses' => $this->statusTypes(),
        ]);
    }

    /**
     * 작성 화면
     */
    public function create()
    {
        $selectedMembers = $this->selectedMembersFromOld();

        return $this->view('backoffice.mail-sms.create', [
            'messageTypes' => $this->messageTypes(false),
            'memberGroups' => $this->memberGroups(),
            'selectedMembers' => $selectedMembers,
        ]);
    }

    /**
     * 저장
     */
    public function store(StoreMailSmsMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['writer_id'] = auth()->id();

        $this->mailSmsService->createMessage($data);

        return redirect()->route('backoffice.mail-sms.index')
            ->with('success', '발송 정보가 등록되었습니다.');
    }

    /**
     * 상세 + 로그
     */
    public function show(Request $request, MailSmsMessage $mailSmsMessage)
    {
        $mailSmsMessage->load(['writer', 'memberGroup', 'recipients']);
        $logs = $this->mailSmsService->getLogs($mailSmsMessage, $request);

        return $this->view('backoffice.mail-sms.show', [
            'message' => $mailSmsMessage,
            'logs' => $logs,
            'statuses' => $this->statusTypes(),
        ]);
    }

    /**
     * 수정 화면
     */
    public function edit(MailSmsMessage $mailSmsMessage)
    {
        $mailSmsMessage->load('recipients');
        $selectedMembers = $this->selectedMembersFromOld();
        if ($selectedMembers->isEmpty()) {
            $selectedMembers = $mailSmsMessage->recipients;
        }

        return $this->view('backoffice.mail-sms.edit', [
            'message' => $mailSmsMessage,
            'messageTypes' => $this->messageTypes(false),
            'memberGroups' => $this->memberGroups(),
            'selectedMembers' => $selectedMembers,
        ]);
    }

    /**
     * 업데이트
     */
    public function update(UpdateMailSmsMessageRequest $request, MailSmsMessage $mailSmsMessage): RedirectResponse
    {
        $this->mailSmsService->updateMessage($mailSmsMessage, $request->validated());

        return redirect()->route('backoffice.mail-sms.index')
            ->with('success', '발송 정보가 수정되었습니다.');
    }

    /**
     * 삭제
     */
    public function destroy(MailSmsMessage $mailSmsMessage): RedirectResponse
    {
        $this->mailSmsService->deleteMessage($mailSmsMessage);

        return redirect()->route('backoffice.mail-sms.index')
            ->with('success', '발송 정보가 삭제되었습니다.');
    }

    /**
     * 발송 요청 (더미)
     */
    public function send(MailSmsMessage $mailSmsMessage): RedirectResponse
    {
        $this->mailSmsService->requestSend($mailSmsMessage);

        return back()->with('success', '발송이 완료되었습니다. (테스트용 더미 데이터)');
    }

    /**
     * 회원 그룹별 회원 조회
     */
    public function membersByGroup(MemberGroup $memberGroup): JsonResponse
    {
        $members = $this->mailSmsService->getMembersByGroup($memberGroup->id);

        return response()->json([
            'members' => $members,
            'meta' => [
                'group_id' => $memberGroup->id,
                'group_name' => $memberGroup->name,
                'count' => count($members),
            ],
        ]);
    }

    /**
     * 회원 검색 (모달)
     */
    public function searchMembers(Request $request): JsonResponse
    {
        $members = $this->mailSmsService->searchMembers($request);

        return response()->json([
            'members' => $members->items(),
            'pagination' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * 발송 로그 목록
     */
    public function logs(Request $request)
    {
        $messages = $this->mailSmsService->getSentMessages($request);

        return $this->view('backoffice.mail-sms-logs.index', [
            'messages' => $messages,
            'messageTypes' => $this->messageTypes(),
        ]);
    }

    /**
     * 발송 로그 상세
     */
    public function logShow(MailSmsMessage $mailSmsMessage)
    {
        $mailSmsMessage->load(['writer', 'memberGroup', 'recipients']);
        $logs = $this->mailSmsService->getLogs($mailSmsMessage, new Request());

        return $this->view('backoffice.mail-sms-logs.show', [
            'message' => $mailSmsMessage,
            'logs' => $logs,
        ]);
    }

    /**
     * 발송 구분 목록
     */
    private function messageTypes(bool $includeAll = true): array
    {
        $types = [
            MailSmsMessage::TYPE_EMAIL => 'EMAIL',
            MailSmsMessage::TYPE_SMS => 'SMS',
            MailSmsMessage::TYPE_KAKAO => '카카오 알림톡',
        ];

        if ($includeAll) {
            return ['all' => '전체'] + $types;
        }

        return $types;
    }

    /**
     * 상태 목록
     */
    private function statusTypes(): array
    {
        return [
            'all' => '전체',
            MailSmsMessage::STATUS_PREPARED => '대기',
            MailSmsMessage::STATUS_SENDING => '발송 중',
            MailSmsMessage::STATUS_COMPLETED => '완료',
        ];
    }

    /**
     * 회원 그룹 목록
     */
    private function memberGroups()
    {
        return MemberGroup::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * 이전 입력값 기반 회원 리스트
     */
    private function selectedMembersFromOld()
    {
        $oldIds = collect(session()->getOldInput('member_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($oldIds->isEmpty()) {
            return collect();
        }

        return Member::query()
            ->whereIn('id', $oldIds)
            ->get()
            ->map(function (Member $member) {
                return (object) [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'member_email' => $member->email,
                    'member_contact' => $member->contact,
                ];
            });
    }
}


