@extends('backoffice.layouts.app')

@section('title', '카테고리 등록')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/menus.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.categories.index', ['group' => $group]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>카테고리 등록 - {{ $group }}</h6>
        </div>
        <div class="board-card-body">
            <form action="{{ route('backoffice.categories.store') }}" method="POST">
                @csrf

                <!-- 카테고리 그룹 선택 -->
                <div class="board-form-group">
                    <label for="category_group_select" class="board-form-label">카테고리 그룹 <span class="required">*</span></label>
                    <select class="board-form-control" id="category_group_select" onchange="toggleNewGroupInput()">
                        @foreach($groups as $existingGroup)
                            <option value="{{ $existingGroup }}" @selected($group === $existingGroup)>{{ $existingGroup }}</option>
                        @endforeach
                        <option value="__new__">+ 새 그룹 추가</option>
                    </select>
                </div>

                <!-- 새 그룹명 입력 (숨김 상태로 시작) -->
                <div class="board-form-group" id="new_group_input_wrapper" style="display: none;">
                    <label for="new_category_group" class="board-form-label">새 그룹명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="new_category_group" placeholder="영문으로 입력 (예: product, notice)">
                    <small class="board-form-text">영문 소문자로 입력해주세요.</small>
                </div>

                <!-- 실제 전송되는 hidden 필드 -->
                <input type="hidden" name="category_group" id="category_group" value="{{ $group }}">

                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="name" class="board-form-label">카테고리명 <span class="required">*</span></label>
                            <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="board-alert board-alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="parent_id" class="board-form-label">상위 카테고리</label>
                            <select class="board-form-control" id="parent_id" name="parent_id">
                                <option value="">없음 (1차 카테고리)</option>
                                @foreach($categories as $category)
                                    @if($category->depth < 3)
                                        <option value="{{ $category->id }}" 
                                                @selected(old('parent_id', $parentId) == $category->id)
                                                data-depth="{{ $category->depth }}">
                                            {{ $category->full_path }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="board-form-text">최대 3단계까지 생성 가능합니다.</small>
                            @error('parent_id')
                                <div class="board-alert board-alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="display_order" class="board-form-label">정렬 순서</label>
                            <input type="number" class="board-form-control" id="display_order" name="display_order" value="{{ old('display_order', 0) }}" min="0">
                            <small class="board-form-text">0으로 입력하면 자동으로 마지막 순서로 지정됩니다.</small>
                            @error('display_order')
                                <div class="board-alert board-alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label class="board-form-label">활성화</label>
                            <div class="board-checkbox-item">
                                <input type="checkbox" class="board-checkbox-input" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                                <label for="is_active" class="board-form-label">활성화</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">등록</button>
                    <a href="{{ route('backoffice.categories.index', ['group' => $group]) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// 새 그룹 입력 토글
function toggleNewGroupInput() {
    const select = document.getElementById('category_group_select');
    const newGroupWrapper = document.getElementById('new_group_input_wrapper');
    const newGroupInput = document.getElementById('new_category_group');
    const hiddenInput = document.getElementById('category_group');
    const parentIdSelect = document.getElementById('parent_id');
    
    if (select.value === '__new__') {
        // 새 그룹 추가 선택
        newGroupWrapper.style.display = 'block';
        newGroupInput.required = true;
        
        // 새 그룹명 입력 시 hidden 필드 업데이트
        newGroupInput.addEventListener('input', function() {
            hiddenInput.value = this.value.toLowerCase().trim();
        });
        
        // 상위 카테고리 선택 초기화 (새 그룹이므로 상위 카테고리 없음)
        if (parentIdSelect) {
            parentIdSelect.value = '';
            parentIdSelect.disabled = true;
        }
    } else {
        // 기존 그룹 선택
        newGroupWrapper.style.display = 'none';
        newGroupInput.required = false;
        newGroupInput.value = '';
        hiddenInput.value = select.value;
        
        // 상위 카테고리 선택 활성화
        if (parentIdSelect) {
            parentIdSelect.disabled = false;
        }
        
        // 선택한 그룹의 카테고리 목록 로드
        loadCategoriesByGroup(select.value);
    }
}

// 그룹별 카테고리 목록 로드 (AJAX)
function loadCategoriesByGroup(group) {
    const parentIdSelect = document.getElementById('parent_id');
    if (!parentIdSelect) return;
    
    // 현재 선택된 값 저장
    const currentValue = parentIdSelect.value;
    
    // 로딩 상태
    parentIdSelect.innerHTML = '<option value="">로딩 중...</option>';
    
    fetch(`/backoffice/categories/active/${group}`)
        .then(response => response.json())
        .then(categories => {
            // 옵션 재구성
            parentIdSelect.innerHTML = '<option value="">없음 (1차 카테고리)</option>';
            
            categories.forEach(category => {
                if (category.depth < 3) {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.full_path;
                    option.dataset.depth = category.depth;
                    parentIdSelect.appendChild(option);
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            parentIdSelect.innerHTML = '<option value="">없음 (1차 카테고리)</option>';
        });
}

// 상위 카테고리 선택 시 depth 체크
document.getElementById('parent_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const parentDepth = parseInt(selectedOption.dataset.depth || 0);
    
    if (parentDepth >= 3) {
        alert('3단계 카테고리는 하위 카테고리를 생성할 수 없습니다.');
        this.value = '';
    }
});
</script>
@push('scripts')
<script src="{{ asset('js/backoffice/categories.js') }}"></script>
@endpush
@endsection



