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
        <!-- 그룹 선택 -->
        <div class="category-group-tabs">
            @foreach($groups as $groupName)
                <a href="{{ route('backoffice.categories.index', ['group' => $groupName]) }}" 
                   class="group-tab {{ $group === $groupName ? 'active' : '' }}">
                    {{ $groupName }}
                </a>
            @endforeach
        </div>
        
        <a href="{{ route('backoffice.categories.create', ['group' => $group]) }}" class="btn btn-success">
            <i class="fas fa-plus"></i> 새 카테고리 추가
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>카테고리 관리 - {{ $group }}</h6>
        </div>
        <div class="board-card-body">
            <div class="table-responsive">
                <ul id="categoryList" class="menu-list">
                    @forelse($categories as $category)
                        <!-- 1차 카테고리 -->
                        <li class="category-menu-item category-menu-item-depth-1" data-id="{{ $category->id }}" data-depth="1">
                            <div class="category-menu-info">
                                <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                                <span class="menu-name">{{ $category->name }}</span>
                                <span class="menu-status">
                                    <span class="status-badge {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $category->is_active ? '활성화' : '비활성화' }}
                                    </span>
                                </span>
                            </div>
                            <div class="board-btn-group">
                                <a href="{{ route('backoffice.categories.create', ['group' => $category->category_group, 'parent_id' => $category->id]) }}" 
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-plus"></i> 하위추가
                                </a>
                                <a href="{{ route('backoffice.categories.edit', $category) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> 수정
                                </a>
                                <form action="{{ route('backoffice.categories.destroy', $category) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('정말 이 카테고리를 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> 삭제
                                    </button>
                                </form>
                            </div>
                        </li>

                        @if($category->children && $category->children->count() > 0)
                            @foreach($category->children as $child2)
                                <!-- 2차 카테고리 -->
                                <li class="category-menu-item category-menu-item-depth-2" data-id="{{ $child2->id }}" data-depth="2">
                                    <div class="category-menu-info">
                                        <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                                        <span class="menu-name">{{ $child2->name }}</span>
                                        <span class="menu-status">
                                            <span class="status-badge {{ $child2->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $child2->is_active ? '활성화' : '비활성화' }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="board-btn-group">
                                        <a href="{{ route('backoffice.categories.create', ['group' => $child2->category_group, 'parent_id' => $child2->id]) }}" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-plus"></i> 하위추가
                                        </a>
                                        <a href="{{ route('backoffice.categories.edit', $child2) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <form action="{{ route('backoffice.categories.destroy', $child2) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('정말 이 카테고리를 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> 삭제
                                            </button>
                                        </form>
                                    </div>
                                </li>

                                @if($child2->children && $child2->children->count() > 0)
                                    @foreach($child2->children as $child3)
                                        <!-- 3차 카테고리 -->
                                        <li class="category-menu-item category-menu-item-depth-3" data-id="{{ $child3->id }}" data-depth="3">
                                            <div class="category-menu-info">
                                                <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                                                <span class="menu-name">{{ $child3->name }}</span>
                                                <span class="menu-status">
                                                    <span class="status-badge {{ $child3->is_active ? 'status-active' : 'status-inactive' }}">
                                                        {{ $child3->is_active ? '활성화' : '비활성화' }}
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="board-btn-group">
                                                <a href="{{ route('backoffice.categories.edit', $child3) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> 수정
                                                </a>
                                                <form action="{{ route('backoffice.categories.destroy', $child3) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('정말 이 카테고리를 삭제하시겠습니까?\n하위 카테고리가 있으면 삭제할 수 없습니다.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> 삭제
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @empty
                        <li class="no-category-item">등록된 카테고리가 없습니다.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="orderSavedMessage" class="order-saved-msg">
    <i class="fa fa-check-circle"></i> 카테고리 순서가 저장되었습니다.
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/categories.js') }}"></script>
@endsection

