@extends('backoffice.layouts.app')

@section('title', $board->name ?? '게시판')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/sorting.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common/modal.css') }}">
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success board-hidden-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="board-container">
        <div class="board-page-header">
            <div class="board-page-buttons">
                <button type="button" id="bulk-delete-btn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> 선택 삭제
                </button>
                <a href="{{ route('backoffice.board-posts.create', $board->slug ?? 'notice') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> 새 게시글
                </a>              
            </div>
        </div>

        <div class="board-card">
    <div class="board-card-body">
                <!-- 검색 필터 -->
                <div class="board-filter">
                    <form method="GET" action="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="start_date" class="filter-label">등록일 시작</label>
                                <input type="date" id="start_date" name="start_date" class="filter-input"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="filter-group">
                                <label for="end_date" class="filter-label">등록일 끝</label>
                                <input type="date" id="end_date" name="end_date" class="filter-input"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="filter-group">
                                <label for="search_type" class="filter-label">검색 구분</label>
                                <select id="search_type" name="search_type" class="filter-select">
                                    <option value="">전체</option>
                                    <option value="title" @selected(request('search_type') == 'title')>제목
                                    </option>
                                    <option value="content" @selected(request('search_type') == 'content')>내용
                                    </option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="keyword" class="filter-label">검색어</label>
                                <input type="text" id="keyword" name="keyword" class="filter-input"
                                    placeholder="검색어를 입력하세요" value="{{ request('keyword') }}">
                            </div>
                            <div class="filter-group">
                                <div class="filter-buttons">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> 검색
                                    </button>
                                    <a href="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}"
                                        class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> 초기화
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $posts->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}" class="per-page-form">
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                            <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                            <input type="hidden" name="search_type" value="{{ request('search_type') }}">
                            <label for="per_page" class="per-page-label">표시 개수:</label>
                            <select name="per_page" id="per_page" class="per-page-select" onchange="this.form.submit()">
                                <option value="10" @selected(request('per_page', 15) == 10)>10개</option>
                                <option value="20" @selected(request('per_page', 15) == 20)>20개</option>
                                <option value="50" @selected(request('per_page', 15) == 50)>50개</option>
                                <option value="100" @selected(request('per_page', 15) == 100)>100개</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="board-table {{ $board->enable_sorting ? 'sortable-table' : '' }}">
                        <thead>
                            <tr>
                                <th class="w5 board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                @if($board->enable_sorting)
                                    <th class="w5">순서</th>
                                @endif
                                <th class="w5">번호</th>
                                <th class="w15">팀</th>
                                <th class="w10">이름</th>
                                <th class="w10">직위</th>
                                <th class="w10">담당업무</th>
                                <th class="w15">전화번호</th>
                                <th class="w10">노출여부</th>
                                <th class="w10">등록일</th>
                                <th class="w15">관리</th>
                            </tr>
                        </thead>
                        <tbody @if($board->enable_sorting) id="sortable-tbody" @endif>
                            @forelse($posts as $post)
                                @php
                                    $customFields = is_string($post->custom_fields) ? json_decode($post->custom_fields, true) : ($post->custom_fields ?? []);
                                    $team = $customFields['team'] ?? '';
                                    $name = $customFields['name'] ?? '';
                                    $position = $customFields['position'] ?? '';
                                    $responsibilities = $customFields['responsibilities'] ?? '';
                                    $contact = $customFields['contact'] ?? '';
                                @endphp
                                <tr @if($board->enable_sorting) data-post-id="{{ $post->id }}" @endif>
                                    <td>
                                        <input type="checkbox" name="selected_posts[]" value="{{ $post->id }}" class="form-check-input post-checkbox">
                                    </td>
                                    @if($board->enable_sorting)
                                        <td class="sort-handle-cell">
                                            <i class="fas fa-grip-vertical sort-handle" title="드래그하여 순서 변경"></i>
                                        </td>
                                    @endif
                                    <td>{{ $post->id }}</td>
                                    <td>{{ $team }}</td>
                                    <td>{{ $name }}</td>
                                    <td>{{ $position }}</td>
                                    <td class="text-start">{{ $responsibilities }}</td>
                                    <td>{{ $contact }}</td>
                                    <td>
                                        <span class="status-badge {{ $post->is_active ? 'status-success' : 'status-muted' }}">
                                            {{ $post->is_active ? 'Y' : 'N' }}
                                        </span>
                                    </td>
                                    <td>{{ $post->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.board-posts.edit', [$board->slug ?? 'notice', $post->id]) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form
                                                action="{{ route('backoffice.board-posts.destroy', [$board->slug ?? 'notice', $post->id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('정말 이 게시글을 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $board->enable_sorting ? '11' : '10' }}" class="text-center">등록된 게시글이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$posts" />
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/backoffice/board-posts.js') }}"></script>
    @if($board->enable_sorting)
        <script src="{{ asset('js/backoffice/sorting.js') }}"></script>
    @endif
@endsection
