@extends('backoffice.layouts.app')

@section('title', '회원 정보')

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
            <button type="button" id="export-btn" class="btn btn-secondary">
                <i class="fas fa-download"></i> 엑셀 다운로드
            </button>
            <button type="button" id="bulk-delete-btn" class="btn btn-danger">
                <i class="fas fa-trash"></i> 선택 삭제
            </button>
            <a href="{{ route('backoffice.members.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">      
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.members.index') }}" class="filter-form">
                    <!-- 첫 번째 줄 -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="member_type" class="filter-label">회원구분</label>
                            <select id="member_type" name="member_type" class="filter-select">
                                <option value="">전체</option>
                                <option value="teacher" @selected(request('member_type') == 'teacher')>교사</option>
                                <option value="student" @selected(request('member_type') == 'student')>학생</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="member_group_id" class="filter-label">회원그룹</label>
                            <select id="member_group_id" name="member_group_id" class="filter-select">
                                <option value="">전체</option>
                                @foreach($memberGroups as $group)
                                    <option value="{{ $group->id }}" @selected(request('member_group_id') == $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="city" class="filter-label">지역</label>
                            <select id="city" name="city" class="filter-select">
                                <option value="">전체</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}" @selected(request('city') == $city)>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                       
                    </div>
                    
                    <!-- 두 번째 줄 -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="joined_from" class="filter-label">가입일</label>
                            <div class="date-range">
                                <input type="date" id="joined_from" name="joined_from" class="filter-input"
                                    value="{{ request('joined_from') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="joined_to" name="joined_to" class="filter-input"
                                    value="{{ request('joined_to') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">수신동의</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="email_consent" value="1" @checked(request('email_consent'))>
                                    EMAIL
                                </label>
                                <label>
                                    <input type="checkbox" name="sms_consent" value="1" @checked(request('sms_consent'))>
                                    SMS
                                </label>
                                <label>
                                    <input type="checkbox" name="kakao_consent" value="1" @checked(request('kakao_consent'))>
                                    카카오 알림톡
                                </label>
                            </div>
                        </div>                      
                    </div>
                    <div class="filter-row">                       
                        <div class="filter-group">
                            <label for="search_type" class="filter-label">검색</label>
                            <div class="search-input-wrapper">
                                <select id="search_type" name="search_type" class="filter-select search-type-select">
                                    <option value="all" @selected(request('search_type', 'all') == 'all')>전체</option>
                                    <option value="login_id" @selected(request('search_type') == 'login_id')>ID</option>
                                    <option value="name" @selected(request('search_type') == 'name')>이름</option>
                                    <option value="school_name" @selected(request('search_type') == 'school_name')>학교명</option>
                                    <option value="email" @selected(request('search_type') == 'email')>이메일</option>
                                    <option value="contact" @selected(request('search_type') == 'contact')>연락처</option>
                                    <option value="city" @selected(request('search_type') == 'city')>소속/시도</option>
                                    <option value="grade" @selected(request('search_type') == 'grade')>학년</option>
                                </select>
                                <input type="text" id="search_keyword" name="search_keyword" class="filter-input search-keyword-input"
                                    placeholder="검색어를 입력하세요" value="{{ request('search_keyword') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.members.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($members->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $members->total() }}</span>
                    </div>
                    <div class="list-controls">                       
                        <form method="GET" action="{{ route('backoffice.members.index') }}" class="per-page-form">
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
                                <th style="width: 40px;" class="board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>No</th>
                                <th>회원구분</th>
                                <th>ID</th>
                                <th>이름</th>
                                <th>성별</th>
                                <th>소속 시/도</th>
                                <th>소속학교</th>
                                <th>학년</th>
                                <th>반</th>
                                <th>생년월일</th>
                                <th>연락처1</th>
                                <th>연락처2</th>
                                <th>이메일주소</th>
                                <th>가입일시</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $index => $member)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="form-check-input member-checkbox">
                                    </td>
                                    <td>{{ $members->total() - ($members->currentPage() - 1) * $members->perPage() - $loop->index }}</td>
                                    <td>
                                        <span class="badge {{ $member->member_type === 'teacher' ? 'badge-primary' : 'badge-info' }}">
                                            {{ $member->member_type === 'teacher' ? '교사' : '학생' }}
                                        </span>
                                    </td>
                                    <td>{{ $member->login_id }}</td>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->gender === 'male' ? '남' : '여' }}</td>
                                    <td>{{ $member->city }}</td>
                                    <td>{{ $member->school_name }}</td>
                                    <td>{{ $member->grade ?? '-' }}</td>
                                    <td>{{ $member->class_number ?? '-' }}</td>
                                    <td>{{ $member->birth_date ? $member->birth_date->format('Ymd') : '-' }}</td>
                                    <td>{{ $member->formatted_contact ?? '-' }}</td>
                                    <td>{{ $member->formatted_parent_contact ?? '-' }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->joined_at ? $member->joined_at->format('Y.m.d') : '-' }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.members.show', $member) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> 보기
                                            </a>
                                            <a href="{{ route('backoffice.members.edit', $member) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="withdrawMember({{ $member->id }})">
                                                <i class="fas fa-trash"></i> 탈퇴
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 하단 액션 영역 -->
                <div class="list-footer-actions" style="margin-top: 15px;">
                    <div class="footer-actions-right">
                        <select class="btn btn-secondary btn-sm" disabled style="margin-right: 10px;">
                            <option>메일LIST</option>
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" disabled style="margin-right: 10px;">
                            메일전송
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" disabled style="margin-right: 10px;">
                            미리보기
                        </button>
                        <select class="btn btn-secondary btn-sm" disabled style="margin-right: 10px;">
                            <option>문자LIST</option>
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" disabled>
                            문자전송
                        </button>
                    </div>
                </div>

                <x-pagination :paginator="$members" />
            @else
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="board-checkbox-column">
                                    <input type="checkbox" id="select-all" class="form-check-input" disabled>
                                </th>
                                <th>No</th>
                                <th>회원구분</th>
                                <th>ID</th>
                                <th>이름</th>
                                <th>성별</th>
                                <th>소속 시/도</th>
                                <th>소속학교</th>
                                <th>학년</th>
                                <th>반</th>
                                <th>생년월일</th>
                                <th>연락처1</th>
                                <th>연락처2</th>
                                <th>이메일주소</th>
                                <th>가입일시</th>
                                <th style="width: 150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="16" class="text-center">등록된 회원이 없습니다.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function withdrawMember(memberId) {
    if (confirm('이 회원을 탈퇴 처리하시겠습니까?')) {
        // 추후 구현
        alert('탈퇴 기능은 준비 중입니다.');
    }
}

// 일괄 삭제 초기화
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    if (!selectAllCheckbox || !bulkDeleteBtn) return;

    // 전체 선택/해제
    selectAllCheckbox.addEventListener('change', function() {
        memberCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // 개별 체크박스 변경 시
    memberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });

    // 일괄 삭제 버튼 클릭
    bulkDeleteBtn.addEventListener('click', function() {
        const selectedMembers = Array.from(memberCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedMembers.length === 0) {
            alert('삭제할 회원을 선택해주세요.');
            return;
        }

        if (confirm(`선택한 ${selectedMembers.length}명의 회원을 삭제하시겠습니까?`)) {
            bulkDeleteMembers(selectedMembers);
        }
    });

    // 엑셀 다운로드 버튼
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportSelectedMembers();
        });
    }

    // 초기 버튼 상태 설정
    updateBulkDeleteButton();
    
    if (memberCheckboxes.length === 0 || Array.from(memberCheckboxes).filter(cb => cb.checked).length === 0) {
        bulkDeleteBtn.disabled = true;
    }
});

// 전체 선택 체크박스 상태 업데이트
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    const checkedCount = Array.from(memberCheckboxes).filter(checkbox => checkbox.checked).length;
    const totalCount = memberCheckboxes.length;

    if (selectAllCheckbox && totalCount > 0) {
        selectAllCheckbox.checked = checkedCount === totalCount;
    }
}

// 일괄 삭제 버튼 상태 업데이트
function updateBulkDeleteButton() {
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const checkedCount = Array.from(document.querySelectorAll('.member-checkbox'))
        .filter(checkbox => checkbox.checked).length;

    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = checkedCount === 0;
        if (checkedCount > 0) {
            bulkDeleteBtn.innerHTML = `선택 삭제 (${checkedCount})`;
        } else {
            bulkDeleteBtn.innerHTML = '선택 삭제';
        }
    }
}

// 일괄 삭제 실행
function bulkDeleteMembers(memberIds) {
    const formData = new FormData();
    memberIds.forEach(id => formData.append('member_ids[]', id));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/backoffice/members/bulk-destroy', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('삭제 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}

// 선택된 회원 엑셀 다운로드
function exportSelectedMembers() {
    const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox'))
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);

    if (selectedMembers.length === 0) {
        alert('다운로드할 회원을 선택해주세요.');
        return;
    }

    // 현재 필터 파라미터 유지하면서 선택된 회원 ID만 추가
    const params = new URLSearchParams(window.location.search);
    selectedMembers.forEach(id => {
        params.append('member_ids[]', id);
    });

    // 엑셀 다운로드 URL로 이동
    window.location.href = '{{ route("backoffice.members.export") }}?' + params.toString();
}
</script>
@endsection

