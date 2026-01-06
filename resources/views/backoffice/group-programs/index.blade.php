@extends('backoffice.layouts.app')

@section('title', '단체 프로그램 관리')

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
            <a href="{{ route('backoffice.group-programs.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
<div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.group-programs.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="education_type" class="filter-label">교육유형</label>
                            <select id="education_type" name="education_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($educationTypes as $key => $name)
                                    <option value="{{ $key }}" @selected(request('education_type') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="reception_type" class="filter-label">접수유형</label>
                            <select id="reception_type" name="reception_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($receptionTypes as $key => $name)
                                    <option value="{{ $key }}" @selected(request('reception_type') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="education_start_date" class="filter-label">교육일정 시작</label>
                            <input type="date" id="education_start_date" name="education_start_date" class="filter-input"
                                value="{{ request('education_start_date') }}">
                        </div>
                        <div class="filter-group">
                            <label for="education_end_date" class="filter-label">교육일정 종료</label>
                            <input type="date" id="education_end_date" name="education_end_date" class="filter-input"
                                value="{{ request('education_end_date') }}">
                        </div>
                        <div class="filter-group">
                            <label for="application_start_date" class="filter-label">신청기간 시작</label>
                            <input type="date" id="application_start_date" name="application_start_date" class="filter-input"
                                value="{{ request('application_start_date') }}">
                        </div>
                        <div class="filter-group">
                            <label for="application_end_date" class="filter-label">신청기간 종료</label>
                            <input type="date" id="application_end_date" name="application_end_date" class="filter-input"
                                value="{{ request('application_end_date') }}">
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search_type" class="filter-label">검색 구분</label>
                            <select id="search_type" name="search_type" class="filter-select">
                                <option value="">전체</option>
                                <option value="program_name" @selected(request('search_type') == 'program_name')>프로그램명</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search_keyword" class="filter-label">검색어</label>
                            <input type="text" id="search_keyword" name="search_keyword" class="filter-input"
                                placeholder="검색어를 입력하세요" value="{{ request('search_keyword') }}">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.group-programs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($programs->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $programs->total() }}</span>
                    </div>
                    <div class="list-controls">                       
                        <form method="GET" action="{{ route('backoffice.group-programs.index') }}" class="per-page-form">
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
                                <th>교육유형</th>
                                <th>프로그램명</th>
                                <th>접수유형</th>
                                <th>교육일정</th>
                                <th>신청기간</th>
                                <th>정원</th>
                                <th>결제수단</th>
                                <th style="width: 150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programs as $program)
                                <tr>
                                    <td>{{ $programs->total() - ($programs->currentPage() - 1) * $programs->perPage() - $loop->index }}</td>
                                    <td>{{ $program->education_type_name }}</td>
                                    <td>{{ $program->program_name }}</td>
                                    <td>{{ $program->reception_type_name ?? '-' }}</td>
                                    <td>
                                        @if($program->education_start_date && $program->education_end_date)
                                            @php
                                                $dayNames = ['일', '월', '화', '수', '목', '금', '토'];
                                                $startDay = $dayNames[$program->education_start_date->dayOfWeek] ?? '';
                                                $endDay = $dayNames[$program->education_end_date->dayOfWeek] ?? '';
                                            @endphp
                                            {{ $program->education_start_date->format('Y-m-d') }}({{ $startDay }}) ~ {{ $program->education_end_date->format('Y-m-d') }}({{ $endDay }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->application_start_date && $program->application_end_date)
                                            {{ $program->application_start_date->format('Y-m-d') }} 00:00 ~ {{ $program->application_end_date->format('Y-m-d') }} 23:59
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->is_unlimited_capacity)
                                            제한없음
                                        @else
                                            {{ $program->capacity ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->payment_method_names)
                                            {{ implode(', ', $program->payment_method_names) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.group-programs.edit', $program) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form action="{{ route('backoffice.group-programs.destroy', $program) }}" method="POST" class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?');">
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

                <x-pagination :paginator="$programs" />
            @else
                <div class="no-data">
                    <p>등록된 프로그램이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
