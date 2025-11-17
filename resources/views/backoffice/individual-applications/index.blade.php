@extends('backoffice.layouts.app')

@section('title', '개인 신청 내역')

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

    <div id="bulk-upload-config"
        data-upload-url="{{ route('backoffice.individual-applications.bulk-upload') }}"
        data-csrf-token="{{ csrf_token() }}"></div>
    <div class="board-page-header">
        <div class="board-page-buttons">
            <button type="button" id="bulk-upload-btn" class="btn btn-primary">
                <i class="fas fa-upload"></i> 일괄 업로드
            </button>
            <input type="file" id="bulk-upload-file" accept=".csv,.xlsx,.xls" style="display: none;">
            <a href="{{ route('backoffice.individual-applications.sample') }}" class="btn btn-secondary">
                <i class="fas fa-download"></i> 샘플파일 받기
            </a>
            <a href="{{ route('backoffice.individual-applications.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>개인 신청 내역</h6>
            </div>
        </div>
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.individual-applications.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="reception_type" class="filter-label">신청유형</label>
                            <select id="reception_type" name="reception_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($receptionTypes as $key => $name)
                                    <option value="{{ $key }}" @selected(request('reception_type') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <label for="draw_result" class="filter-label">추첨결과</label>
                            <select id="draw_result" name="draw_result" class="filter-select">
                                <option value="">전체</option>
                                @foreach($drawResults as $key => $name)
                                    <option value="{{ $key }}" @selected(request('draw_result') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>                   
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="participation_start_date" class="filter-label">참가일정</label>
                            <div class="date-range">
                                <input type="date" id="participation_start_date" name="participation_start_date" class="filter-input"
                                    value="{{ request('participation_start_date') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="participation_end_date" name="participation_end_date" class="filter-input"
                                    value="{{ request('participation_end_date') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="applied_start_date" class="filter-label">신청기간</label>
                            <div class="date-range">
                                <input type="date" id="applied_start_date" name="applied_start_date" class="filter-input"
                                    value="{{ request('applied_start_date') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="applied_end_date" name="applied_end_date" class="filter-input"
                                    value="{{ request('applied_end_date') }}">
                            </div>
                        </div>                       
                    </div>                  
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search_type" class="filter-label">검색 구분</label>
                            <select id="search_type" name="search_type" class="filter-select">
                                <option value="">전체</option>
                                <option value="applicant_name" @selected(request('search_type') == 'applicant_name')>신청자명</option>
                                <option value="school_name" @selected(request('search_type') == 'school_name')>학교명</option>
                                <option value="program_name" @selected(request('search_type') == 'program_name')>프로그램명</option>
                                <option value="application_number" @selected(request('search_type') == 'application_number')>신청번호</option>
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
                                <a href="{{ route('backoffice.individual-applications.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($applications->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $applications->total() }}</span>
                    </div>
                    <div class="list-controls">                       
                        <form method="GET" action="{{ route('backoffice.individual-applications.index') }}" class="per-page-form">
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
                                <th>신청번호</th>
                                <th>신청유형</th>
                                <th>신청자명</th>
                                <th>학교명</th>
                                <th>교육유형</th>
                                <th>프로그램명</th>
                                <th>추첨결과</th>
                                <th>결제방법</th>
                                <th>결제상태</th>
                                <th>참가비</th>
                                <th>참가일</th>
                                <th>신청일시</th>
                                <th style="width: 150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td>{{ $applications->total() - ($applications->currentPage() - 1) * $applications->perPage() - $loop->index }}</td>
                                    <td>{{ $application->application_number }}</td>
                                    <td>{{ $application->reception_type_label }}</td>
                                    <td>{{ $application->applicant_name }}</td>
                                    <td>{{ $application->applicant_school_name ?? '-' }}</td>
                                    <td>{{ $application->education_type_label }}</td>
                                    <td>{{ $application->program_name }}</td>
                                    <td>
                                        {{ $application->draw_result_label }}
                                    </td>
                                    <td>{{ $application->payment_method ? ($paymentMethods[$application->payment_method] ?? $application->payment_method) : '-' }}</td>
                                    <td>{{ $application->payment_status_label }}</td>
                                    <td>{{ $application->participation_fee ? number_format($application->participation_fee) : '-' }}</td>
                                    <td>{{ optional($application->participation_date)->format('Y.m.d') ?? '-' }}</td>
                                    <td>{{ optional($application->applied_at)->format('Y.m.d H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.individual-applications.edit', $application) }}" class="btn btn-primary btn-sm">
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
                    <p>등록된 신청 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/individual-applications-index.js') }}"></script>
@endsection

