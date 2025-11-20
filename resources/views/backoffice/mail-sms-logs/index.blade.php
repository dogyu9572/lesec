@extends('backoffice.layouts.app')

@section('title', '메일/SMS 발송 로그')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
<div class="board-container users-page">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>메일/SMS 발송 로그</h6>
            </div>
        </div>
        <div class="board-card-body">
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.mail-sms-logs.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">구분</label>
                            <div class="radio-group">
                                @foreach($messageTypes as $value => $label)
                                    <label class="radio-inline">
                                        <input type="radio" name="message_type" value="{{ $value }}" @checked(request('message_type', 'all') === $value)>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">발송일</label>
                            <div class="date-range">
                                <input type="date" name="send_start_date" class="filter-input" value="{{ request('send_start_date') }}">
                                <span class="date-separator">~</span>
                                <input type="date" name="send_end_date" class="filter-input" value="{{ request('send_end_date') }}">
                            </div>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label" for="title_keyword">제목</label>
                            <input type="text" id="title_keyword" name="title_keyword" class="filter-input" value="{{ request('title_keyword') }}" placeholder="제목을 입력하세요">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.mail-sms-logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $messages->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.mail-sms-logs.index') }}" class="per-page-form">
                        @foreach(request()->except('per_page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="per_page" class="per-page-label">목록 개수:</label>
                        <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="20" @selected(request('per_page', 20) == 20)>20개</option>
                            <option value="50" @selected(request('per_page') == 50)>50개</option>
                            <option value="100" @selected(request('per_page') == 100)>100개</option>
                        </select>
                    </form>
                </div>
            </div>

            @if($messages->count())
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>구분</th>
                                <th>제목</th>
                                <th>작성자</th>
                                <th>성공여부</th>
                                <th>발송일시</th>
                                <th style="width: 160px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $index => $message)
                                <tr>
                                    <td>{{ $messages->total() - ($messages->currentPage() - 1) * $messages->perPage() - $index }}</td>
                                    <td>{{ $message->message_type_label }}</td>
                                    <td>{{ $message->title }}</td>
                                    <td>{{ $message->writer->name ?? '-' }}</td>
                                    <td>
                                        @if($message->success_count > 0 || $message->failure_count > 0)
                                            성공: {{ $message->success_count }}, 실패: {{ $message->failure_count }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ optional($message->send_completed_at)->format('Y.m.d H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.mail-sms-logs.show', $message) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> 상세
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$messages" />
            @else
                <div class="no-data">
                    <p>발송 로그가 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

