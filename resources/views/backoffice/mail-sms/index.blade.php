@extends('backoffice.layouts.app')

@section('title', '메일/SMS 관리')

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
        <div class="board-card-body">
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.mail-sms.index') }}" class="filter-form">
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
                            <label class="filter-label">작성일</label>
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
                                <a href="{{ route('backoffice.mail-sms.index') }}" class="btn btn-secondary">
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
                    <form method="GET" action="{{ route('backoffice.mail-sms.index') }}" class="per-page-form">
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
                    @if($messages->count() > 0)
                        <button type="button" id="bulk-delete-btn-header" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> 선택 삭제
                        </button>
                    @endif
                    <a href="{{ route('backoffice.mail-sms.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> 등록
                    </a>
                </div>
            </div>

            @if($messages->count())
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th class="w5 board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>No</th>
                                <th>구분</th>
                                <th>제목</th>
                                <th>작성자</th>
                                <th>작성일</th>
                                <th style="width: 160px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $index => $message)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_messages[]" value="{{ $message->id }}" class="form-check-input message-checkbox">
                                    </td>
                                    <td>{{ $messages->total() - ($messages->currentPage() - 1) * $messages->perPage() - $index }}</td>
                                    <td>{{ $message->message_type_label }}</td>
                                    <td>{{ $message->title }}</td>
                                    <td>{{ $message->writer->name ?? '-' }}</td>
                                    <td>{{ optional($message->created_at)->format('Y.m.d') ?? '-' }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.mail-sms.edit', $message) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form action="{{ route('backoffice.mail-sms.destroy', $message) }}" method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$messages" />
            @else
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th class="w5 board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input" disabled>
                                </th>
                                <th>No</th>
                                <th>구분</th>
                                <th>제목</th>
                                <th>작성자</th>
                                <th>작성일</th>
                                <th style="width: 160px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">등록된 발송 정보가 없습니다.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/mail-sms-index.js') }}"></script>
@endsection

