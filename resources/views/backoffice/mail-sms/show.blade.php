@extends('backoffice.layouts.app')

@section('title', '메일/SMS 상세')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
<div class="board-container users-page">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.mail-sms.index') }}" class="btn btn-secondary">목록</a>
            @if($message->status === \App\Models\MailSmsMessage::STATUS_PREPARED)
                <form action="{{ route('backoffice.mail-sms.send', $message) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('선택한 회원에게 발송하시겠습니까?');">
                    @csrf
                    <button type="submit" class="btn btn-success">발송</button>
                </form>
            @endif
            <a href="{{ route('backoffice.mail-sms.edit', $message) }}" class="btn btn-primary">수정</a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>발송 정보</h6>
        </div>
        <div class="board-card-body">
            <table class="detail-table">
                <tbody>
                    <tr>
                        <th>구분</th>
                        <td>{{ $message->message_type_label }}</td>
                        <th>회원 그룹</th>
                        <td>{{ $message->memberGroup->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>작성자</th>
                        <td>{{ $message->writer->name ?? '-' }}</td>
                        <th>상태</th>
                        <td>{{ $message->status_label }}</td>
                    </tr>
                    <tr>
                        <th>발송 요청</th>
                        <td>{{ optional($message->send_requested_at)->format('Y.m.d H:i') ?? '-' }}</td>
                        <th>발송 완료</th>
                        <td>{{ optional($message->send_completed_at)->format('Y.m.d H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>성공/실패</th>
                        <td colspan="3">{{ number_format($message->success_count) }} / {{ number_format($message->failure_count) }}</td>
                    </tr>
                    <tr>
                        <th>제목</th>
                        <td colspan="3">{{ $message->title }}</td>
                    </tr>
                    <tr>
                        <th>내용</th>
                        <td colspan="3" class="content-cell">
                            {!! nl2br(e($message->content)) !!}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>상세 발송 내역</h6>
        </div>
        <div class="board-card-body">
            <form method="GET" action="{{ route('backoffice.mail-sms.show', $message) }}" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">성공 여부</label>
                        <select name="result_status" class="filter-select">
                            <option value="all" @selected(request('result_status', 'all') === 'all')>전체</option>
                            <option value="success" @selected(request('result_status') === 'success')>성공</option>
                            <option value="failure" @selected(request('result_status') === 'failure')>실패</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">회원 검색</label>
                        <input type="text" name="keyword" class="filter-input" value="{{ request('keyword') }}" placeholder="이름/연락처/이메일">
                    </div>
                    <div class="filter-group">
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                            <a href="{{ route('backoffice.mail-sms.show', $message) }}" class="btn btn-secondary">초기화</a>
                        </div>
                    </div>
                </div>
            </form>

            @if($logs->count())
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>회원명</th>
                                <th>이메일</th>
                                <th>연락처</th>
                                <th>성공 여부</th>
                                <th>발송일시</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $index => $log)
                                <tr>
                                    <td>{{ $logs->total() - ($logs->currentPage() - 1) * $logs->perPage() - $index }}</td>
                                    <td>{{ $log->member_name }}</td>
                                    <td>{{ $log->member_email ?? '-' }}</td>
                                    <td>{{ $log->member_contact ?? '-' }}</td>
                                    <td>{{ $log->result_status_label }}</td>
                                    <td>{{ optional($log->sent_at)->format('Y.m.d H:i') ?? '-' }}</td>
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
@endsection


