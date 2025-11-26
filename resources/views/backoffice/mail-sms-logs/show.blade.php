@extends('backoffice.layouts.app')

@section('title', '메일/SMS 발송 로그 상세')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/rosters.css') }}">
<style>
.program-info-row {
    padding: 12px 24px;
}
</style>
@endsection

@section('content')
<div class="board-container users-page rosters-page">
    <div class="board-page-header roster-header">
        <div class="board-page-buttons roster-header-right">
            <a href="{{ route('backoffice.mail-sms-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>메일/SMS 발송 관리(상세)</h6>
            </div>
        </div>
        <div class="board-card-body">
            @php
                $messageInfo = [
                    '구분' => $message->message_type_label,
                    '회원그룹' => $message->memberGroup->name ?? '-',
                    '회원' => $message->recipients->pluck('member_name')->join(', ') ?: '-',
                    '제목' => $message->title,
                    '내용' => $message->content,
                    '발송일시' => optional($message->send_completed_at)->format('Y.m.d H:i:s') ?? '-',
                ];
            @endphp
            <div class="program-section">
                <div class="section-title">발송 정보</div>
                <dl class="program-info-list">
                    @foreach($messageInfo as $label => $value)
                        <div class="program-info-row">
                            <dt>{{ $label }}</dt>
                            <dd>
                                @if($label === '내용')
                                    {!! nl2br(e($value)) !!}
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="program-section">
                <div class="section-title">발송 내역</div>
                @if($logs->count())
                    <div class="table-responsive">
                        <table class="board-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>회원명</th>
                                    <th>연락처</th>
                                    <th>이메일</th>
                                    <th>발송일시</th>
                                    <th>성공여부</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $index => $log)
                                    <tr>
                                        <td>{{ $logs->total() - ($logs->currentPage() - 1) * $logs->perPage() - $index }}</td>
                                        <td>{{ $log->member->name ?? $log->member_name ?? '-' }}</td>
                                        <td>{{ $log->member->contact ?? $log->member_contact ?? '-' }}</td>
                                        <td>{{ $log->member->email ?? $log->member_email ?? '-' }}</td>
                                        <td>{{ optional($log->sent_at)->format('Y.m.d H:i') ?? '-' }}</td>
                                        <td>
                                            @if($log->result_status === 'success')
                                                <span class="text-success">성공</span>
                                            @elseif($log->result_status === 'failure')
                                                <span class="text-danger">실패</span>
                                                @if($log->response_message)
                                                    <br><small class="text-muted">{{ $log->response_message }}</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <x-pagination :paginator="$logs" />
                @else
                    <div class="no-data">
                        <p>발송 로그가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

