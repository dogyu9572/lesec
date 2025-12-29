@extends('backoffice.layouts.app')

@section('title', '회원 그룹 관리')

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
            <button type="button" id="bulk-delete-btn" class="btn btn-danger">
                <i class="fas fa-trash"></i> 선택 삭제
            </button>
            <a href="{{ route('backoffice.member-groups.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">       
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.member-groups.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="created_from" class="filter-label">등록일</label>
                            <div class="date-range">
                                <input type="date" id="created_from" name="created_from" class="filter-input"
                                    value="{{ request('created_from') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="created_to" name="created_to" class="filter-input"
                                    value="{{ request('created_to') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="name" class="filter-label">그룹명</label>
                            <input type="text" id="name" name="name" class="filter-input"
                                placeholder="그룹명을 입력하세요" value="{{ request('name') }}">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.member-groups.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($groups->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $groups->total() }}</span>
                    </div>
                    <div class="list-controls">                       
                        <form method="GET" action="{{ route('backoffice.member-groups.index') }}" class="per-page-form">
                            @foreach(request()->except('per_page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="per_page" class="per-page-label">목록 개수:</label>
                            <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                                <option value="10" @selected(request('per_page', 20) == 10)>10개</option>
                                <option value="20" @selected(request('per_page', 20) == 20)>20개</option>
                                <option value="30" @selected(request('per_page') == 30)>30개</option>
                                <option value="40" @selected(request('per_page') == 40)>40개</option>
                                <option value="50" @selected(request('per_page') == 50)>50개</option>
                                <option value="60" @selected(request('per_page') == 60)>60개</option>
                                <option value="70" @selected(request('per_page') == 70)>70개</option>
                                <option value="80" @selected(request('per_page') == 80)>80개</option>
                                <option value="90" @selected(request('per_page') == 90)>90개</option>
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
                                <th>번호</th>
                                <th>그룹명</th>
                                <th>등록인원</th>
                                <th>등록일</th>
                                <th style="width: 150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="group-checkbox" value="{{ $group->id }}">
                                    </td>
                                    <td>{{ $groups->total() - ($groups->currentPage() - 1) * $groups->perPage() - $loop->index }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->members_count ?? $group->member_count }}명</td>
                                    <td>{{ $group->created_at->format('Y.m.d') }}</td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.member-groups.edit', $group) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form action="{{ route('backoffice.member-groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('이 회원 그룹을 삭제하시겠습니까?');">
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
                    <p>등록된 회원 그룹이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkDeleteBtnFooter = document.getElementById('bulk-delete-btn-footer');

    if (!selectAllCheckbox || !bulkDeleteBtn) return;

    // 전체 선택/해제
    selectAllCheckbox.addEventListener('change', function() {
        groupCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // 개별 체크박스 변경 시
    groupCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });

    // 일괄 삭제 버튼 클릭
    function handleBulkDelete() {
        const selectedGroups = Array.from(groupCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedGroups.length === 0) {
            alert('삭제할 그룹을 선택해주세요.');
            return;
        }

        if (confirm(`선택한 ${selectedGroups.length}개의 그룹을 삭제하시겠습니까?`)) {
            bulkDeleteGroups(selectedGroups);
        }
    }
    
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', handleBulkDelete);
    }
    if (bulkDeleteBtnFooter) {
        bulkDeleteBtnFooter.addEventListener('click', handleBulkDelete);
    }

    // 초기 버튼 상태 설정
    updateBulkDeleteButton();

    // 전체 선택 체크박스 상태 업데이트
    function updateSelectAllCheckbox() {
        const checkedCount = Array.from(groupCheckboxes).filter(checkbox => checkbox.checked).length;
        const totalCount = groupCheckboxes.length;

        if (selectAllCheckbox && totalCount > 0) {
            selectAllCheckbox.checked = checkedCount === totalCount;
        }
    }

    // 일괄 삭제 버튼 상태 업데이트
    function updateBulkDeleteButton() {
        const checkedCount = Array.from(groupCheckboxes)
            .filter(checkbox => checkbox.checked).length;

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = checkedCount === 0;
        }
        if (bulkDeleteBtnFooter) {
            bulkDeleteBtnFooter.disabled = checkedCount === 0;
            if (checkedCount > 0) {
                bulkDeleteBtnFooter.innerHTML = `<i class="fas fa-trash"></i> 선택 삭제 (${checkedCount})`;
            } else {
                bulkDeleteBtnFooter.innerHTML = '<i class="fas fa-trash"></i> 선택 삭제';
            }
        }
    }

    // 일괄 삭제 실행
    function bulkDeleteGroups(groupIds) {
        const formData = new FormData();
        groupIds.forEach(id => formData.append('group_ids[]', id));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/backoffice/member-groups/bulk-destroy', {
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
});
</script>
@endsection

