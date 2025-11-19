@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('title', $pageTitle ?? '게시판 템플릿 관리')

@section('content')

@if(session('success'))
    <div class="alert alert-success hidden-alert">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger hidden-alert">
        @foreach($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
@endif

<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.board-templates.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> 새 템플릿
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>게시판 템플릿 목록</h6>
            </div>
        </div>
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="board-filter">
                <form method="GET" action="{{ route('backoffice.board-templates.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="name" class="filter-label">템플릿명</label>
                            <input type="text" id="name" name="name" class="filter-input"
                                placeholder="템플릿명을 입력하세요" value="{{ request('name') }}">
                        </div>
                        <div class="filter-group">
                            <label for="skin_id" class="filter-label">스킨</label>
                            <select id="skin_id" name="skin_id" class="filter-select">
                                <option value="">전체</option>
                                @foreach($skins as $skin)
                                    <option value="{{ $skin->id }}" @selected(request('skin_id') == $skin->id)>{{ $skin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="is_active" class="filter-label">상태</label>
                            <select id="is_active" name="is_active" class="filter-select">
                                <option value="">전체</option>
                                <option value="1" @selected(request('is_active') == '1')>활성</option>
                                <option value="0" @selected(request('is_active') == '0')>비활성</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.board-templates.index') }}" class="btn btn-secondary">
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
                    <span class="list-count">Total : {{ $templates->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.board-templates.index') }}" class="per-page-form">
                        @foreach(request()->except('per_page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="per_page" class="per-page-label">목록 개수:</label>
                        <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                            <option value="20" @selected(request('per_page') == 20)>20</option>
                            <option value="50" @selected(request('per_page') == 50)>50</option>
                            <option value="100" @selected(request('per_page') == 100)>100</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th width="5%">번호</th>
                            <th width="23%">템플릿명</th>
                            <th width="15%">스킨</th>
                            <th width="10%">사용 게시판</th>
                            <th width="10%">상태</th>
                            <th width="13%">등록일</th>
                            <th width="24%">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ $template->id }}</td>
                                <td>
                                    <strong>{{ $template->name }}</strong>
                                </td>
                                <td>{{ $template->skin->name ?? '-' }}</td>
                                <td>{{ $template->boards()->count() }}개</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge badge-success">활성</span>
                                    @else
                                        <span class="badge badge-secondary">비활성</span>
                                    @endif
                                </td>
                                <td>{{ $template->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="board-btn-group">
                                        <a href="{{ route('backoffice.board-templates.edit', $template) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <form action="{{ route('backoffice.board-templates.duplicate', $template) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fas fa-copy"></i> 복제
                                            </button>
                                        </form>
                                        @if($template->boards()->count() == 0)
                                            <form action="{{ route('backoffice.board-templates.destroy', $template) }}" method="POST" 
                                                  class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">등록된 템플릿이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$templates" />
        </div>
    </div>
</div>

@endsection

