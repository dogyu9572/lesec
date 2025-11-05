@extends('backoffice.layouts.app')

@section('title', '학교 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
<div class="board-container users-page">
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

    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.schools.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>학교 목록</h6>
            </div>
        </div>
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.schools.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="source_type" class="filter-label">구분</label>
                            <select id="source_type" name="source_type" class="filter-select">
                                <option value="">전체</option>
                                <option value="api" @selected(request('source_type') == 'api')>API</option>
                                <option value="user_registration" @selected(request('source_type') == 'user_registration')>사용자 등록</option>
                                <option value="admin_registration" @selected(request('source_type') == 'admin_registration')>관리자 등록</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="city" class="filter-label">시/도</label>
                            <input type="text" id="city" name="city" class="filter-input"
                                placeholder="시/도" value="{{ request('city') }}">
                        </div>
                        <div class="filter-group">
                            <label for="district" class="filter-label">시/군/구</label>
                            <input type="text" id="district" name="district" class="filter-input"
                                placeholder="시/군/구" value="{{ request('district') }}">
                        </div>
                        <div class="filter-group">
                            <label for="school_level" class="filter-label">학교급</label>
                            <select id="school_level" name="school_level" class="filter-select">
                                <option value="">전체</option>
                                @foreach($schoolLevels as $key => $name)
                                    <option value="{{ $key }}" @selected(request('school_level') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="school_name" class="filter-label">학교명</label>
                            <input type="text" id="school_name" name="school_name" class="filter-input"
                                placeholder="학교명" value="{{ request('school_name') }}">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.schools.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($schools->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $schools->total() }}</span>
                    </div>
                    <div class="list-controls">                       
                        <form method="GET" action="{{ route('backoffice.schools.index') }}" class="per-page-form">
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

                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>학교구분</th>
                                <th>시/도</th>
                                <th>시/군/구</th>
                                <th>학교급</th>
                                <th>학교명</th>
                                <th>상태</th>
                                <th>업데이트 일시</th>
                                <th style="width: 150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                                <tr>
                                    <td>{{ $schools->total() - ($schools->currentPage() - 1) * $schools->perPage() - $loop->index }}</td>
                                    <td>{{ $school->source_type_name }}</td>
                                    <td>{{ $school->city ?? '-' }}</td>
                                    <td>{{ $school->district ?? '-' }}</td>
                                    <td>{{ $school->school_level_name ?? '-' }}</td>
                                    <td>{{ $school->school_name }}</td>
                                    <td>{{ $school->status_name }}</td>
                                    <td>{{ $school->updated_at->format('Y.m.d H:i:s') }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            @if($school->source_type === 'api')
                                                <a href="{{ route('backoffice.schools.show', $school) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> 확인
                                                </a>
                                            @else
                                                <a href="{{ route('backoffice.schools.edit', $school) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> 수정
                                                </a>
                                                <form action="{{ route('backoffice.schools.destroy', $school) }}" method="POST" class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?');">
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$schools" />
            @else
                <div class="no-data">
                    <p>등록된 학교가 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

