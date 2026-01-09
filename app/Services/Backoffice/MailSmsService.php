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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class MailSmsService
{
    public function __construct(
        private readonly SmsKakaoApiService $smsKakaoApiService
    ) {
    }

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
     * 발송 회차 목록 조회
     */
    public function getLogBatches(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->input('per_page', 20);

        $query = MailSmsLog::query()
            ->select([
                DB::raw('MAX(mail_sms_logs.id) as id'),
                'mail_sms_logs.mail_sms_message_id',
                'mail_sms_logs.send_sequence',
                DB::raw('MIN(mail_sms_logs.created_at) as requested_at'),
                DB::raw('MAX(mail_sms_logs.sent_at) as completed_at'),
                DB::raw("SUM(CASE WHEN mail_sms_logs.result_status = 'success' THEN 1 ELSE 0 END) as success_count"),
                DB::raw("SUM(CASE WHEN mail_sms_logs.result_status = 'failure' THEN 1 ELSE 0 END) as failure_count"),
            ])
            ->with(['message.writer'])
            ->join('mail_sms_messages', 'mail_sms_messages.id', '=', 'mail_sms_logs.mail_sms_message_id')
            ->groupBy('mail_sms_logs.mail_sms_message_id', 'mail_sms_logs.send_sequence')
            ->orderByDesc('requested_at');

        if ($request->filled('message_type') && $request->message_type !== 'all') {
            $query->where('mail_sms_messages.message_type', $request->message_type);
        }

        if ($request->filled('send_start_date')) {
            $query->whereDate('mail_sms_logs.created_at', '>=', $request->send_start_date);
        }

        if ($request->filled('send_end_date')) {
            $query->whereDate('mail_sms_logs.created_at', '<=', $request->send_end_date);
        }

        if ($request->filled('title_keyword')) {
            $query->where('mail_sms_messages.title', 'like', '%' . $request->title_keyword . '%');
        }

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
    /**
     * 일괄 삭제
     */
    public function bulkDelete(array $messageIds): int
    {
        $deletedCount = 0;
        
        foreach ($messageIds as $messageId) {
            $message = MailSmsMessage::find($messageId);
            if ($message && $this->deleteMessage($message)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    public function deleteMessage(MailSmsMessage $message): bool
    {
        return DB::transaction(function () use ($message) {
            $message->recipients()->delete();
            $message->logs()->delete();
            return $message->delete();
        });
    }

    /**
     * 발송 요청 처리
     */
    public function requestSend(MailSmsMessage $message): MailSmsMessage
    {
        if ($message->recipients()->count() === 0) {
            throw new InvalidArgumentException('수신 대상이 없습니다.');
        }

        return DB::transaction(function () use ($message) {
            $now = Carbon::now();

            $message->update([
                'status' => MailSmsMessage::STATUS_SENDING,
                'send_requested_at' => $now,
                'send_started_at' => $now,
            ]);

            $recipients = $message->recipients()->get();
            $sendSequence = (int) $message->logs()->max('send_sequence') + 1;
            $logs = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    if ($message->message_type === MailSmsMessage::TYPE_EMAIL) {
                        // 이메일 발송
                        $email = $recipient->member_email;

                        if (empty($email)) {
                            $logs[] = [
                                'send_sequence' => $sendSequence,
                                'mail_sms_message_id' => $message->id,
                                'mail_sms_message_member_id' => $recipient->id,
                                'member_id' => $recipient->member_id,
                                'member_name' => $recipient->member_name,
                                'member_email' => $email,
                                'member_contact' => $recipient->member_contact,
                                'result_status' => 'failure',
                                'sent_at' => null,
                                'response_code' => 'NO_EMAIL',
                                'response_message' => '이메일 주소가 없습니다.',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $failureCount++;
                            continue;
                        }

                        Mail::raw($content, function ($mail) use ($email, $message, $recipient) {
                            $mail->to($email, $recipient->member_name)
                                ->subject($message->title);
                        });

                        $logs[] = [
                            'send_sequence' => $sendSequence,
                            'mail_sms_message_id' => $message->id,
                            'mail_sms_message_member_id' => $recipient->id,
                            'member_id' => $recipient->member_id,
                            'member_name' => $recipient->member_name,
                            'member_email' => $email,
                            'member_contact' => $recipient->member_contact,
                            'result_status' => 'success',
                            'sent_at' => $now,
                            'response_code' => '200',
                            'response_message' => '이메일 발송 성공',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $successCount++;

                    } else {
                        // SMS 또는 카카오 알림톡 발송
                        $phone = $recipient->member_contact;
                        $content = $message->content;

                        if (empty($phone)) {
                            $logs[] = [
                                'send_sequence' => $sendSequence,
                                'mail_sms_message_id' => $message->id,
                                'mail_sms_message_member_id' => $recipient->id,
                                'member_id' => $recipient->member_id,
                                'member_name' => $recipient->member_name,
                                'member_email' => $recipient->member_email,
                                'member_contact' => $phone,
                                'result_status' => 'failure',
                                'sent_at' => $now,
                                'response_code' => 'NO_PHONE',
                                'response_message' => '전화번호가 없습니다.',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $failureCount++;
                            continue;
                        }

                        if ($message->message_type === MailSmsMessage::TYPE_SMS) {
                            $result = $this->smsKakaoApiService->sendSms($phone, $content);
                        } elseif ($message->message_type === MailSmsMessage::TYPE_KAKAO) {
                            $result = $this->smsKakaoApiService->sendKakao($phone, $content);
                        } else {
                            throw new InvalidArgumentException('지원하지 않는 메시지 타입입니다.');
                        }

                        $logs[] = [
                            'send_sequence' => $sendSequence,
                            'mail_sms_message_id' => $message->id,
                            'mail_sms_message_member_id' => $recipient->id,
                            'member_id' => $recipient->member_id,
                            'member_name' => $recipient->member_name,
                            'member_email' => $recipient->member_email,
                            'member_contact' => $phone,
                            'result_status' => $result['success'] ? 'success' : 'failure',
                            'sent_at' => $result['success'] ? $now : null,
                            'response_code' => $result['response_code'],
                            'response_message' => $result['response_message'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $failureCount++;
                        }
                    }

                } catch (\Exception $e) {
                    Log::error('메시지 발송 중 오류 발생', [
                        'message_id' => $message->id,
                        'recipient_id' => $recipient->id,
                        'message_type' => $message->message_type,
                        'error' => $e->getMessage(),
                    ]);

                    $logs[] = [
                        'send_sequence' => $sendSequence,
                        'mail_sms_message_id' => $message->id,
                        'mail_sms_message_member_id' => $recipient->id,
                        'member_id' => $recipient->member_id,
                        'member_name' => $recipient->member_name,
                        'member_email' => $recipient->member_email,
                        'member_contact' => $recipient->member_contact,
                        'result_status' => 'failure',
                        'sent_at' => null,
                        'response_code' => 'EXCEPTION',
                        'response_message' => '발송 중 오류가 발생했습니다: ' . $e->getMessage(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $failureCount++;
                }
            }

            MailSmsLog::insert($logs);

            $message->update([
                'status' => MailSmsMessage::STATUS_COMPLETED,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'send_completed_at' => $now,
                'last_error_message' => $failureCount > 0 ? "{$failureCount}건 발송 실패" : null,
            ]);

            return $message->fresh(['writer', 'memberGroup']);
        });
    }

    /**
     * 수신자 목록 페이징 조회
     */
    public function getRecipientsPaginated(MailSmsMessage $message, Request $request): LengthAwarePaginator
    {
        // 먼저 등록 순서(id 순서)로 전체 목록을 가져와서 순서 번호를 매김
        $allRecipients = $message->recipients()->orderBy('id')->get();
        $sequenceMap = [];
        foreach ($allRecipients as $index => $recipient) {
            $sequenceMap[$recipient->id] = $index + 1;
        }

        // 이름 순으로 정렬된 쿼리
        $query = $message->recipients()->orderBy('member_name');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('member_name', 'like', "%{$keyword}%")
                  ->orWhere('member_email', 'like', "%{$keyword}%")
                  ->orWhere('member_contact', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $paginator = $query->paginate($perPage)->withQueryString();

        // 각 항목에 순서 번호 추가
        $paginator->getCollection()->transform(function ($item) use ($sequenceMap) {
            $item->sequence = $sequenceMap[$item->id] ?? null;
            return $item;
        });

        return $paginator;
    }

    /**
     * 발송 로그 조회
     */
    public function getLogs(MailSmsMessage $message, Request $request, ?int $sendSequence = null): LengthAwarePaginator
    {
        $query = $message->logs()->orderByDesc('created_at');

        $activeSequence = $sendSequence ?? $request->input('send_sequence');

        if ($activeSequence) {
            $query->where('send_sequence', (int) $activeSequence);
        }

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
     * 회차 요약 정보
     */
    public function getSequenceSummary(MailSmsMessage $message, int $sendSequence): ?object
    {
        $summary = $message->logs()
            ->where('send_sequence', $sendSequence)
            ->selectRaw("SUM(CASE WHEN result_status = 'success' THEN 1 ELSE 0 END) as success_count")
            ->selectRaw("SUM(CASE WHEN result_status = 'failure' THEN 1 ELSE 0 END) as failure_count")
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('MIN(created_at) as requested_at')
            ->selectRaw('MAX(sent_at) as completed_at')
            ->first();

        if ($summary) {
            $summary->send_sequence = $sendSequence;
        }

        return $summary;
    }

    /**
     * 회원 검색
     */
    public function searchMembers(Request $request): LengthAwarePaginator
    {
        $query = Member::query()->select('id', 'name', 'login_id', 'email', 'school_name', 'contact')->orderByDesc('created_at');

        // 회원구분 필터
        $memberType = $request->input('member_type');
        if (!empty($memberType) && in_array($memberType, ['teacher', 'student'])) {
            $query->where('member_type', $memberType);
        }

        if ($request->filled('member_group_id')) {
            $query->where('member_group_id', $request->member_group_id);
        }

        // 검색어 필터 (search_type + search_keyword)
        $searchKeyword = trim($request->input('search_keyword', ''));
        if (!empty($searchKeyword)) {
            $searchType = $request->input('search_type', 'all');
            
            $query->where(function ($q) use ($searchType, $searchKeyword) {
                if ($searchType === 'all') {
                    $q->where('name', 'like', "%{$searchKeyword}%")
                      ->orWhere('login_id', 'like', "%{$searchKeyword}%")
                      ->orWhere('school_name', 'like', "%{$searchKeyword}%")
                      ->orWhere('email', 'like', "%{$searchKeyword}%")
                      ->orWhere('contact', 'like', "%{$searchKeyword}%");
                } else {
                    $q->where($searchType, 'like', "%{$searchKeyword}%");
                }
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

    /**
     * 수신자 삭제
     */
    public function deleteRecipient(MailSmsMessage $message, int $recipientId): bool
    {
        return DB::transaction(function () use ($message, $recipientId) {
            $recipient = $message->recipients()->where('id', $recipientId)->first();
            
            if (!$recipient) {
                throw new InvalidArgumentException('수신자를 찾을 수 없습니다.');
            }

            return $recipient->delete();
        });
    }
}


