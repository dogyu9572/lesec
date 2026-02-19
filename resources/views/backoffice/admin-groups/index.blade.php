@extends('backoffice.layouts.app')

@section('title', '관리자 권한 그룹 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
@endsection

@section('content')
<div class="board-container admins-page">
    <div class="board-header">
        <a href="{{ route('backoffice.admin-groups.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> 새 권한 그룹 추가
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="board-card">
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="admin-filter">
                <form method="GET" action="{{ route('backoffice.admin-groups.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="name" class="filter-label">그룹명</label>
                            <input type="text" id="name" name="name" class="filter-input"
                                placeholder="그룹명을 입력하세요" value="{{ request('name') }}">
                        </div>
                        <div class="filter-group">
                            <label for="is_active" class="filter-label">상태</label>
                            <select id="is_active" name="is_active" class="filter-select">
                                <option value="">전체</option>
                                <option value="1" @selected(request('is_active') == '1')>활성화</option>
                                <option value="0" @selected(request('is_active') == '0')>비활성화</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.admin-groups.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($groups->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="admin-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $groups->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.admin-groups.index') }}" class="per-page-form">
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
                                <th>번호</th>
                                <th>그룹명</th>
                                <th>설명</th>
                                <th>사용 관리자 수</th>
                                <th>상태</th>
                                <th>등록일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td>{{ $groups->total() - ($groups->currentPage() - 1) * $groups->perPage() - $loop->index }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->description ?: '-' }}</td>
                                    <td>{{ $group->users->count() }}명</td>
                                    <td>
                                        <span class="status-badge {{ $group->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $group->is_active ? '활성화' : '비활성화' }}
                                        </span>
                                    </td>
                                    <td>{{ $group->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.admin-groups.edit', $group) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form action="{{ route('backoffice.admin-groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('이 권한 그룹을 삭제하시겠습니까?');">
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
                <x-pagination :paginator="$groups" />
            @else
                <div class="no-data">
                    <p>등록된 권한 그룹이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

