@extends('backoffice.layouts.app')

@section('title', '권한 그룹 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/admin-permissions.js') }}"></script>
@endsection

@section('content')
<div class="board-container admins-page">
    <div class="board-header">
        <a href="{{ route('backoffice.admin-groups.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="board-card">
        <div class="board-card-body">
            <form id="adminGroupForm" action="{{ route('backoffice.admin-groups.update', $group) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-section">
                    <h3>기본 정보</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">그룹명</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $group->name) }}" required placeholder="그룹명을 입력하세요">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">그룹 설명</label>
                            <textarea id="description" name="description" rows="4" placeholder="그룹 설명을 입력하세요">{{ old('description', $group->description) }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="is_active">활성화 여부</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $group->is_active) == 1)>
                                    <span>활성화</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $group->is_active) == 0)>
                                    <span>비활성화</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>메뉴 접근 권한</h3>
                    <div class="permission-simple-list">
                        @foreach($menus as $menu)
                            <div class="permission-simple-item">
                                <label class="permission-main-menu">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}]" value="1" @checked(in_array($menu->id, $groupPermissions))>
                                    <span>{{ $menu->name }} 메뉴</span>
                                </label>
                                @if($menu->children->count() > 0)
                                    <span class="permission-separator">-</span>
                                    <div class="permission-sub-menus">
                                        @foreach($menu->children as $child)
                                            <label class="permission-sub-menu">
                                                <input type="checkbox" name="permissions[{{ $child->id }}]" value="1" @checked(in_array($child->id, $groupPermissions))>
                                                <span>{{ $child->name }}</span>
                                            </label>                                           
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.admin-groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 취소
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

