@extends('backoffice.layouts.app')

@section('title', '카테고리 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/categories.css') }}">
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
        <button type="button" class="btn btn-success add-category-btn" id="addCategoryBtn">
            <i class="fas fa-plus"></i> 추가
        </button>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <!-- 그룹 탭 -->
            <div class="category-group-tabs">
                <div class="category-group-tabs-inner">
                    @foreach($groups as $group)
                        <a href="{{ route('backoffice.categories.index', ['group' => $group->id]) }}" 
                           class="group-tab {{ $selectedGroupId == $group->id ? 'active' : '' }}">
                            {{ $group->name }}
                        </a>
                    @endforeach
                </div>
                <!-- 선택된 그룹 정보 및 수정/삭제 버튼 (선택된 그룹이 있을 때만 표시) -->
                @if($selectedGroupId && $selectedGroup)
                    <!-- 선택된 그룹 정보 및 수정/삭제 버튼 -->
                    <div class="selected-group-info">                     
                        <button type="button" class="btn btn-info btn-sm edit-category-btn" data-category-id="{{ $selectedGroup->id }}">
                            <i class="fas fa-cog"></i> 그룹수정
                         </button>
                        <form action="{{ route('backoffice.categories.destroy', $selectedGroup) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('정말 이 그룹을 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-category-btn">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <ul id="categoryList" class="menu-list">
                    @forelse($categories as $category)
                        <!-- 1차 카테고리 -->
                        <li class="category-menu-item category-menu-item-depth-1 collapsed" 
                            data-id="{{ $category->id }}" 
                            data-depth="1"
                            data-code="{{ $category->code ?? '' }}"
                            data-name="{{ $category->name }}"
                            data-is-active="{{ $category->is_active ? '1' : '0' }}">
                            <div class="category-content">
                                <div class="category-menu-info">
                                    <button type="button" class="expand-toggle" data-category-id="{{ $category->id }}">
                                        <i class="fa fa-angle-down submenu-icon"></i>
                                    </button>
                                    <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                                    <span class="menu-name" id="name-{{ $category->id }}" data-field="name" data-id="{{ $category->id }}">
                                        {{ $category->name }}
                                    </span>
                                </div>
                                <div class="board-btn-group">
                                    <button type="button" class="btn btn-primary btn-sm edit-category-btn" data-category-id="{{ $category->id }}">
                                        <i class="fas fa-edit"></i> 수정
                                    </button>
                                    <form action="{{ route('backoffice.categories.destroy', $category) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('정말 이 카테고리를 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm delete-category-btn">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- 2차 카테고리 컨테이너 -->
                            <ul class="category-children" id="children-{{ $category->id }}">
                                @if($category->children && $category->children->count() > 0)
                                    @foreach($category->children as $child2)
                                        <!-- 2차 카테고리 -->
                                        <li class="category-menu-item category-menu-item-depth-2" 
                                            data-id="{{ $child2->id }}" 
                                            data-depth="2"
                                            data-code="{{ $child2->code ?? '' }}"
                                            data-name="{{ $child2->name }}"
                                            data-is-active="{{ $child2->is_active ? '1' : '0' }}">
                                            <div class="category-content">
                                                <div class="category-menu-info">
                                                    <span class="expand-toggle-placeholder"></span>
                                                    <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                                                    <span class="menu-name" id="name-{{ $child2->id }}" data-field="name" data-id="{{ $child2->id }}">
                                                        {{ $child2->name }}
                                                    </span>
                                                </div>
                                                <div class="board-btn-group">
                                                    <button type="button" class="btn btn-primary btn-sm edit-category-btn" data-category-id="{{ $child2->id }}">
                                                        <i class="fas fa-edit"></i> 수정
                                                    </button>
                                                    <form action="{{ route('backoffice.categories.destroy', $child2) }}" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('정말 이 카테고리를 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-category-btn">
                                                            <i class="fas fa-trash"></i> 삭제
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </li>
                    @empty
                        <li class="no-category-item">
                            @if($selectedGroupId)
                                선택된 그룹에 하위 카테고리가 없습니다.
                            @else
                                등록된 카테고리가 없습니다.
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- 모달 팝업 -->
<div id="categoryModal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay" id="categoryModalOverlay"></div>
    <div class="category-modal-content">
        <div class="category-modal-header">
            <h5 id="modalTitle">새 카테고리 추가</h5>
            <button type="button" class="category-modal-close" id="categoryModalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <form id="categoryModalForm">
                @csrf
                <input type="hidden" id="modalCategoryId" name="category_id" value="">
                <input type="hidden" id="modalParentId" name="parent_id" value="">
                
                <!-- 뎁스 타입 선택 -->
                <div class="modal-form-group">
                    <label for="modalDepthType" class="modal-form-label">추가할 항목 <span class="required">*</span></label>
                    <select class="modal-form-control" id="modalDepthType" name="depth_type" required>
                        <option value="0">그룹</option>
                        <option value="1">1차 카테고리</option>
                        <option value="2">2차 카테고리</option>
                    </select>
                </div>

                <!-- 그룹 선택 (1차/2차 선택 시) -->
                <div class="modal-form-group" id="groupSelectWrapper" style="display: none;">
                    <label for="modalGroupId" class="modal-form-label">그룹 선택 <span class="required">*</span></label>
                    <select class="modal-form-control" id="modalGroupId" name="group_id">
                        <option value="">그룹 선택</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 1차 카테고리 선택 (2차 선택 시) -->
                <div class="modal-form-group" id="parentCategorySelectWrapper" style="display: none;">
                    <label for="modalParentCategoryId" class="modal-form-label">1차 카테고리 선택 <span class="required">*</span></label>
                    <select class="modal-form-control" id="modalParentCategoryId" name="parent_category_id">
                        <option value="">1차 카테고리 선택</option>
                        <!-- JavaScript로 동적으로 채워짐 -->
                    </select>
                </div>
                
                <div class="modal-form-group">
                    <label for="modalName" class="modal-form-label">명칭 <span class="required">*</span></label>
                    <input type="text" class="modal-form-control" id="modalName" name="name" required>
                </div>

                <div class="modal-form-group">
                    <label for="modalCode" class="modal-form-label">코드</label>
                    <input type="text" class="modal-form-control" id="modalCode" name="code">
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-col">
                        <div class="modal-form-group">
                            <label for="modalDisplayOrder" class="modal-form-label">순서</label>
                            <input type="number" class="modal-form-control" id="modalDisplayOrder" name="display_order" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-form-col">
                        <div class="modal-form-group">
                            <label for="modalIsActive" class="modal-form-label">사용 여부</label>
                            <select class="modal-form-control" id="modalIsActive" name="is_active">
                                <option value="1">Y</option>
                                <option value="0">N</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="modalError" class="modal-error" style="display: none;"></div>
            </form>
        </div>
        <div class="category-modal-footer">
            <button type="button" class="btn btn-primary" id="saveCategoryModalBtn">저장</button>
            <button type="button" class="btn btn-secondary" id="closeCategoryModalBtn">취소</button>
        </div>
    </div>
</div>

<div id="orderSavedMessage" class="order-saved-msg">
    <i class="fa fa-check-circle"></i> 카테고리 순서가 저장되었습니다.
</div>

<div id="toastMessage" class="toast-message" style="display: none;"></div>
@endsection

@section('scripts')
<script>
    // 선택된 그룹 ID를 전역 변수로 설정
    window.selectedGroupId = {{ $selectedGroupId ?? 'null' }};
</script>
<script src="{{ asset('js/backoffice/categories.js') }}"></script>
@endsection
