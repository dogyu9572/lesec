@extends('backoffice.layouts.app')

@section('title', '명단 관리')

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
            <button type="button" id="download-btn" class="btn btn-dark">
                <i class="fas fa-download"></i> 다운로드
            </button>
            <button type="button" id="sms-email-btn" class="btn btn-info">
                <i class="fas fa-envelope"></i> SMS/메일 발송
            </button>
        </div>
    </div>

    <div class="board-card">
<div class="board-card-body">
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.rosters.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="application_type" class="filter-label">신청구분</label>
                            <select id="application_type" name="application_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($filters['application_types'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('application_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="reception_type" class="filter-label">신청유형</label>
                            <select id="reception_type" name="reception_type" class="filter-select">
                                <option value="">전체</option>
                                @foreach($filters['reception_types'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('reception_type') == $value)>{{ $label }}</option>
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
                            <label for="participation_date" class="filter-label">참가일</label>
                            <input type="date" id="participation_date" name="participation_date" class="filter-input" value="{{ request('participation_date') }}">
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
                                <a href="{{ route('backoffice.rosters.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($rosters->count() > 0)
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $rosters->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.rosters.index') }}" class="per-page-form">
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
                                <th style="width: 40px;"><input type="checkbox" id="select-all-checkbox"></th>
                                <th>신청구분</th>
                                <th>신청유형</th>
                                <th>교육유형</th>
                                <th>프로그램명</th>
                                <th>참가일</th>
                                <th>정원</th>
                                <th>신청인원</th>
                                <th style="width: 120px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rosters as $roster)
                                <tr>
                                    <td><input type="checkbox" class="roster-checkbox" value="{{ $roster['id'] }}" data-type="{{ $roster['application_type'] }}"></td>
                                    <td>{{ $roster['application_type'] === 'individual' ? '개인' : '단체' }}</td>
                                    <td>
                                        @if($roster['application_type'] === 'individual')
                                            {{ $filters['reception_types'][$roster['reception_type']] ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $filters['education_types'][$roster['education_type']] ?? '-' }}</td>
                                    <td>{{ $roster['program_name'] }}</td>
                                    <td>{{ $roster['participation_date_formatted'] ?? '-' }}</td>
                                    <td>
                                        @if($roster['is_unlimited_capacity'])
                                            무제한
                                        @else
                                            {{ $roster['capacity'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $roster['applicant_count'] }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.rosters.edit', $roster['id']) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$rosters" />
            @else
                <div class="no-data">
                    <p>등록된 명단이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 전체 선택 체크박스
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rosterCheckboxes = document.querySelectorAll('.roster-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rosterCheckboxes.forEach(function(checkbox) {
                checkbox.checked = this.checked;
            }, this);
        });
    }

    // 다운로드 버튼
    const downloadBtn = document.getElementById('download-btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // 선택된 행 ID 수집 (선택 없으면 전체)
            const selectedIds = Array.from(document.querySelectorAll('.roster-checkbox:checked'))
                .map(function(checkbox) {
                    return checkbox.value;
                });

            // 폼 생성 및 제출
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("backoffice.rosters.download-list") }}';

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            // 선택된 ID 추가 (빈 배열이면 전체 다운로드)
            selectedIds.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    }

    // SMS/메일 발송 버튼 (기능 없음)
    const smsEmailBtn = document.getElementById('sms-email-btn');
    if (smsEmailBtn) {
        smsEmailBtn.addEventListener('click', function() {
            // TODO: SMS/메일 발송 기능 구현
            alert('SMS/메일 발송 기능은 준비 중입니다.');
        });
    }
});
</script>
@endsection

