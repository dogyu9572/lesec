@extends('backoffice.layouts.app')

@section('title', '카테고리 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/menus.css') }}">
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success hidden-alert">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger hidden-alert">
        {{ session('error') }}
    </div>
@endif

<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.categories.index', ['group' => $category->category_group]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>카테고리 수정 - {{ $category->category_group }}</h6>
        </div>
        <div class="board-card-body">
            <form action="{{ route('backoffice.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 카테고리 그룹 (읽기 전용) -->
                <div class="board-form-group">
                    <label class="board-form-label">카테고리 그룹</label>
                    <input type="text" class="board-form-control" value="{{ $category->category_group }}" disabled>
                    <input type="hidden" name="category_group" value="{{ $category->category_group }}">
                </div>

                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="name" class="board-form-label">카테고리명 <span class="required">*</span></label>
                            <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
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
                                @foreach($categories as $parentCategory)
                                    @if($parentCategory->depth < 3)
                                        <option value="{{ $parentCategory->id }}" 
                                                @selected(old('parent_id', $category->parent_id) == $parentCategory->id)
                                                data-depth="{{ $parentCategory->depth }}">
                                            {{ $parentCategory->full_path }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="board-form-text">
                                현재 depth: {{ $category->depth }}단계 (최대 3단계)
                                @if($category->children && $category->children->count() > 0)
                                    <span class="text-warning">(하위 카테고리 {{ $category->children->count() }}개)</span>
                                @endif
                            </small>
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
                            <input type="number" class="board-form-control" id="display_order" name="display_order" value="{{ old('display_order', $category->display_order) }}" min="0">
                            @error('display_order')
                                <div class="board-alert board-alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label class="board-form-label">활성화</label>
                            <div class="board-checkbox-item">
                                <input type="checkbox" class="board-checkbox-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                                <label for="is_active" class="board-form-label">활성화</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">수정</button>
                    <a href="{{ route('backoffice.categories.index', ['group' => $category->category_group]) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// 알림 메시지 자동 숨김
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.hidden-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 3000);
    });

    // 상위 카테고리 선택 시 depth 체크
    document.getElementById('parent_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const parentDepth = parseInt(selectedOption.dataset.depth || 0);
        
        if (parentDepth >= 3) {
            alert('3단계 카테고리는 하위 카테고리를 생성할 수 없습니다.');
            this.value = '{{ $category->parent_id }}';
        }
    });
});
</script>
@push('scripts')
<script src="{{ asset('js/backoffice/categories.js') }}"></script>
@endpush
@endsection



