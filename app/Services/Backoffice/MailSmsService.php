<?php

namespace App\Services\Backoffice;

use App\Models\MailSmsLog;
use App\Models\MailSmsMessage;
use App\Models\MailSmsMessageMember;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MailSmsService
{
    /**
     * 메일/SMS 목록 조회
     */
    public function getMessages(Request $request): LengthAwarePaginator
    {
        $query = MailSmsMessage::query()
            ->with(['writer', 'memberGroup'])
            ->orderByDesc('created_at');

        if ($request->filled('message_type') && $request->message_type !== 'all') {
            $query->where('message_type', $request->message_type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('send_start_date')) {
            $query->whereDate('send_requested_at', '>=', $request->send_start_date);
        }

        if ($request->filled('send_end_date')) {
            $query->whereDate('send_requested_at', '<=', $request->send_end_date);
        }

        if ($request->filled('title_keyword')) {
            $query->where('title', 'like', '%' . $request->title_keyword . '%');
        }

        $perPage = (int) $request->input('per_page', 20);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 발송 로그 목록 조회 (발송 완료된 메시지만)
     */
    public function getSentMessages(Request $request): LengthAwarePaginator
    {
        $query = MailSmsMessage::query()
            ->with(['writer'])
            ->where('status', MailSmsMessage::STATUS_COMPLETED)
            ->whereNotNull('send_completed_at')
            ->orderByDesc('send_completed_at');

        if ($request->filled('message_type') && $request->message_type !== 'all') {
            $query->where('message_type', $request->message_type);
        }

        if ($request->filled('send_start_date')) {
            $query->whereDate('send_completed_at', '>=', $request->send_start_date);
        }

        if ($request->filled('send_end_date')) {
            $query->whereDate('send_completed_at', '<=', $request->send_end_date);
        }

        if ($request->filled('title_keyword')) {
            $query->where('title', 'like', '%' . $request->title_keyword . '%');
        }

        $perPage = (int) $request->input('per_page', 20);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 메일/SMS 생성
     */
    public function createMessage(array $data): MailSmsMessage
    {
        return DB::transaction(function () use ($data) {
            $recipientIds = $data['member_ids'] ?? [];
            if (empty($recipientIds)) {
                throw new InvalidArgumentException('수신 대상을 선택해 주세요.');
            }

            $writerId = $data['writer_id'] ?? null;
            if (!$writerId) {
                throw new InvalidArgumentException('작성자 정보가 필요합니다.');
            }

            $messageData = [
                'message_type' => $data['message_type'],
                'title' => $data['title'],
                'content' => $data['content'],
                'writer_id' => $writerId,
                'member_group_id' => $data['member_group_id'] ?? null,
                'status' => MailSmsMessage::STATUS_PREPARED,
            ];

            $message = MailSmsMessage::create($messageData);
            $this->syncRecipients($message, $recipientIds);

            return $message->fresh(['writer', 'memberGroup', 'recipients']);
        });
    }

    /**
     * 메일/SMS 수정
     */
    public function updateMessage(MailSmsMessage $message, array $data): MailSmsMessage
    {
        if ($message->status !== MailSmsMessage::STATUS_PREPARED) {
            throw new InvalidArgumentException('발송 대기 상태에서만 수정할 수 있습니다.');
        }

        return DB::transaction(function () use ($message, $data) {
            $message->update([
                'message_type' => $data['message_type'],
                'title' => $data['title'],
                'content' => $data['content'],
                'member_group_id' => $data['member_group_id'] ?? null,
            ]);

            if (!empty($data['member_ids'])) {
                $this->syncRecipients($message, $data['member_ids']);
            }

            return $message->fresh(['writer', 'memberGroup', 'recipients']);
        });
    }

    /**
     * 메일/SMS 삭제
     */
    public function deleteMessage(MailSmsMessage $message): bool
    {
        return DB::transaction(function () use ($message) {
            $message->recipients()->delete();
            $message->logs()->delete();
            return $message->delete();
        });
    }

    /**
     * 발송 요청 처리 (더미 발송)
     */
    public function requestSend(MailSmsMessage $message): MailSmsMessage
    {
        if ($message->recipients()->count() === 0) {
            throw new InvalidArgumentException('수신 대상이 없습니다.');
        }

        if ($message->status === MailSmsMessage::STATUS_COMPLETED) {
            return $message;
        }

        return DB::transaction(function () use ($message) {
            $now = Carbon::now();

            $message->update([
                'status' => MailSmsMessage::STATUS_SENDING,
                'send_requested_at' => $now,
                'send_started_at' => $now,
            ]);

            $logs = $message->recipients()
                ->get()
                ->map(function (MailSmsMessageMember $recipient) use ($message, $now) {
                    return [
                        'mail_sms_message_id' => $message->id,
                        'mail_sms_message_member_id' => $recipient->id,
                        'member_id' => $recipient->member_id,
                        'member_name' => $recipient->member_name,
                        'member_email' => $recipient->member_email,
                        'member_contact' => $recipient->member_contact,
                        'result_status' => 'success',
                        'sent_at' => $now,
                        'response_code' => 'DUMMY',
                        'response_message' => '실제 발송 API 연동 전 테스트 데이터입니다.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })
                ->toArray();

            MailSmsLog::insert($logs);

            $message->update([
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => count($logs),
                'failure_count' => 0,
                'send_completed_at' => $now,
            ]);

            return $message->fresh(['writer', 'memberGroup']);
        });
    }

    /**
     * 발송 로그 조회
     */
    public function getLogs(MailSmsMessage $message, Request $request): LengthAwarePaginator
    {
        $query = $message->logs()->orderByDesc('created_at');

        if ($request->filled('result_status') && $request->result_status !== 'all') {
            $query->where('result_status', $request->result_status);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('member_name', 'like', "%{$keyword}%")
                  ->orWhere('member_email', 'like', "%{$keyword}%")
                  ->orWhere('member_contact', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 회원 검색
     */
    public function searchMembers(Request $request): LengthAwarePaginator
    {
        $query = Member::query()->orderByDesc('created_at');

        if ($request->filled('member_type') && $request->member_type !== 'all') {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('member_group_id')) {
            $query->where('member_group_id', $request->member_group_id);
        }

        if ($request->filled('search_term')) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('login_id', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * 회원 그룹별 전체 회원 조회
     */
    public function getMembersByGroup(int $groupId): array
    {
        return Member::query()
            ->where('member_group_id', $groupId)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'contact'])
            ->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'contact' => $member->contact,
            ])
            ->toArray();
    }

    /**
     * 대상 회원 동기화
     */
    private function syncRecipients(MailSmsMessage $message, array $memberIds): void
    {
        $uniqueIds = collect($memberIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($uniqueIds->isEmpty()) {
            throw new InvalidArgumentException('수신 대상을 선택해 주세요.');
        }

        $members = Member::query()
            ->whereIn('id', $uniqueIds)
            ->get(['id', 'name', 'email', 'contact']);

        if ($members->count() === 0) {
            throw new InvalidArgumentException('선택한 회원을 찾을 수 없습니다.');
        }

        $now = Carbon::now();
        $message->recipients()->delete();

        $insertRows = $members->map(fn (Member $member) => [
            'mail_sms_message_id' => $message->id,
            'member_id' => $member->id,
            'member_name' => $member->name,
            'member_email' => $member->email,
            'member_contact' => $member->contact,
            'is_selected' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        MailSmsMessageMember::insert($insertRows);
    }
}


