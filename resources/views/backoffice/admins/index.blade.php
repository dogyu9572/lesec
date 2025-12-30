@extends('backoffice.layouts.app')

@section('title', '관리자 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
@endsection

@section('content')
<div class="board-container admins-page">   
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
                <form method="GET" action="{{ route('backoffice.admins.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search_type" class="filter-label">검색</label>
                            <div class="search-input-wrapper">
                                <select id="search_type" name="search_type" class="filter-select search-type-select">
                                    <option value="all" @selected(request('search_type', 'all') == 'all')>전체</option>
                                    <option value="name" @selected(request('search_type') == 'name')>이름</option>
                                    <option value="login_id" @selected(request('search_type') == 'login_id')>아이디</option>
                                </select>
                                <input type="text" id="search_keyword" name="search_keyword" class="filter-input search-keyword-input"
                                    placeholder="검색어를 입력하세요" value="{{ request('search_keyword') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="role" class="filter-label">권한</label>
                            <select id="role" name="role" class="filter-select">
                                <option value="">전체</option>
                                <option value="super_admin" @selected(request('role') == 'super_admin')>슈퍼관리자</option>
                                <option value="admin" @selected(request('role') == 'admin')>관리자</option>
                            </select>
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
                                <a href="{{ route('backoffice.admins.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($admins->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="admin-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $admins->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.admins.index') }}" class="per-page-form">
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
                        <button type="button" id="bulk-delete-btn-header" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> 선택 삭제
                        </button>
                        <a href="{{ route('backoffice.admins.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> 등록
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th class="w5 board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>번호</th>
                                <th>아이디</th>
                                <th>성명</th>
                                <th>권한</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $index => $admin)
                                <tr>
                                    <td>
                                        @if($admin->role !== 'super_admin')
                                            <input type="checkbox" name="selected_admins[]" value="{{ $admin->id }}" class="form-check-input admin-checkbox">
                                        @endif
                                    </td>
                                    <td>{{ $admins->total() - ($admins->currentPage() - 1) * $admins->perPage() - $loop->index }}</td>
                                    <td>{{ $admin->login_id ?: '-' }}</td>
                                    <td>{{ $admin->name }}</td>
                                    <td>
                                        <span class="role-badge role-{{ $admin->role }}">
                                            @switch($admin->role)
                                                @case('super_admin')
                                                    슈퍼관리자
                                                    @break
                                                @case('admin')
                                                    관리자
                                                    @break
                                                @default
                                                    {{ $admin->role }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.admins.show', $admin) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> 보기
                                            </a>
                                            <a href="{{ route('backoffice.admins.edit', $admin) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th class="w5 board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input" disabled>
                                </th>
                                <th>번호</th>
                                <th>아이디</th>
                                <th>성명</th>
                                <th>권한</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">등록된 관리자가 없습니다.</td>
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
<script src="{{ asset('js/backoffice/admins.js') }}"></script>
@endsection
