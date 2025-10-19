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
                <form method="GET" action="{{ route('backoffice.board-templates.index') }}" class="board-filter-form">
                    <div class="board-filter-row">
                        <div class="board-filter-col">
                            <input type="text" name="name" class="board-form-control" placeholder="템플릿명 검색" value="{{ request('name') }}">
                        </div>
                        <div class="board-filter-col">
                            <select name="skin_id" class="board-form-control">
                                <option value="">전체 스킨</option>
                                @foreach($skins as $skin)
                                    <option value="{{ $skin->id }}" @selected(request('skin_id') == $skin->id)>{{ $skin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="board-filter-col">
                            <select name="is_active" class="board-form-control">
                                <option value="">전체 상태</option>
                                <option value="1" @selected(request('is_active') === '1')>활성</option>
                                <option value="0" @selected(request('is_active') === '0')>비활성</option>
                            </select>
                        </div>
                        <div class="board-filter-col">
                            <select name="is_system" class="board-form-control">
                                <option value="">전체 유형</option>
                                <option value="1" @selected(request('is_system') === '1')>시스템</option>
                                <option value="0" @selected(request('is_system') === '0')>사용자</option>
                            </select>
                        </div>
                        <div class="board-filter-col">
                            <button type="submit" class="btn btn-primary">검색</button>
                            <a href="{{ route('backoffice.board-templates.index') }}" class="btn btn-secondary">초기화</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 템플릿 목록 테이블 -->
            <div class="board-table-wrapper">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th width="5%">번호</th>
                            <th width="25%">템플릿명</th>
                            <th width="15%">스킨</th>
                            <th width="10%">유형</th>
                            <th width="8%">사용 게시판</th>
                            <th width="8%">상태</th>
                            <th width="15%">등록일</th>
                            <th width="14%">관리</th>
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
                                <td>
                                    @if($template->is_system)
                                        <span class="badge badge-info">시스템</span>
                                    @else
                                        <span class="badge badge-secondary">사용자</span>
                                    @endif
                                </td>
                                <td>{{ $template->boards()->count() }}개</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge badge-success">활성</span>
                                    @else
                                        <span class="badge badge-secondary">비활성</span>
                                    @endif
                                </td>
                                <td>{{ $template->created_at->format('Y-m-d H:i') }}</td>
                                <td style="text-align: center;">
                                    <div class="template-actions">
                                        <a href="{{ route('backoffice.board-templates.edit', $template) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <form action="{{ route('backoffice.board-templates.duplicate', $template) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info">
                                                <i class="fas fa-copy"></i> 복제
                                            </button>
                                        </form>
                                        @unless($template->is_system)
                                            <form action="{{ route('backoffice.board-templates.destroy', $template) }}" method="POST" 
                                                  onsubmit="return confirm('정말 삭제하시겠습니까?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">등록된 템플릿이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 페이지네이션 -->
            @if($templates->hasPages())
                <div class="board-pagination">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

