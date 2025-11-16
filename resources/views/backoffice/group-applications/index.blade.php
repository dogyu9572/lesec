@extends('backoffice.layouts.app')

@section('title', '단체 신청 내역')

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
            <a href="{{ route('backoffice.group-applications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>단체 신청 내역</h6>
            </div>
        </div>
        <div class="board-card-body">
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.group-applications.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="application_status" class="filter-label">신청상태</label>
                            <select id="application_status" name="application_status" class="filter-select">
                                <option value="">전체</option>
                                @foreach($filters['application_statuses'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('application_status') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="education_type" class="filter-label">교육유형</label>
                            <select id="education_type" name="education_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($filters['education_types'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('education_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">참가일정</label>
                            <div class="date-range">
                                <input type="date" name="participation_start_date" class="filter-input" value="{{ request('participation_start_date') }}">
                                <span class="date-separator">~</span>
                                <input type="date" name="participation_end_date" class="filter-input" value="{{ request('participation_end_date') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">신청기간</label>
                            <div class="date-range">
                                <input type="date" name="applied_start_date" class="filter-input" value="{{ request('applied_start_date') }}">
                                <span class="date-separator">~</span>
                                <input type="date" name="applied_end_date" class="filter-input" value="{{ request('applied_end_date') }}">
                            </div>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search_type" class="filter-label">검색 구분</label>
                            <select id="search_type" name="search_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($filters['search_types'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('search_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search_keyword" class="filter-label">검색어</label>
                            <input type="text" id="search_keyword" name="search_keyword" class="filter-input" placeholder="검색어를 입력하세요" value="{{ request('search_keyword') }}">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.group-applications.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($applications->count() > 0)
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $applications->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.group-applications.index') }}" class="per-page-form">
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
                                <th style="width: 40px;"><input type="checkbox" disabled></th>
                                <th>No</th>
                                <th>신청번호</th>
                                <th>신청상태</th>
                                <th>신청자명</th>
                                <th>학교명</th>
                                <th>교육유형</th>
                                <th>프로그램명</th>
                                <th>신청인원</th>
                                <th>접수상태</th>
                                <th>결제방법</th>
                                <th>참가비</th>
                                <th>참가일</th>
                                <th>신청일시</th>
                                <th style="width: 120px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td><input type="checkbox" disabled></td>
                                    <td>{{ $applications->total() - ($applications->currentPage() - 1) * $applications->perPage() - $loop->index }}</td>
                                    <td>{{ data_get($application, 'application_number', '-') }}</td>
                                    <td>{{ data_get($application, 'application_status_label', '-') }}</td>
                                    <td>{{ data_get($application, 'applicant_name', '-') }}</td>
                                    <td>{{ data_get($application, 'school_name', '-') }}</td>
                                    <td>{{ data_get($application, 'education_type_label', '-') }}</td>
                                    <td>{{ data_get($application, 'program_name_label', '-') }}</td>
                                    <td>{{ data_get($application, 'applicant_count', '-') }}</td>
                                    <td>{{ data_get($application, 'reception_status_label', '-') }}</td>
                                    <td>{{ data_get($application, 'payment_method_label', '-') }}</td>
                                    <td>
                                        @php
                                            $fee = data_get($application, 'participation_fee');
                                        @endphp
                                        {{ $fee !== null && $fee !== '' ? number_format((int) $fee) : '-' }}
                                    </td>
                                    <td>{{ data_get($application, 'participation_date_formatted', data_get($application, 'participation_date', '-')) }}</td>
                                    <td>{{ data_get($application, 'applied_at_formatted', data_get($application, 'applied_at', '-')) }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.group-applications.edit', data_get($application, 'id', 0)) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$applications" />
            @else
                <div class="no-data">
                    <p>등록된 단체 신청 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


