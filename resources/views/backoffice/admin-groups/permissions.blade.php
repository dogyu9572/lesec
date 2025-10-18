@extends('backoffice.layouts.app')

@section('title', '권한 그룹 권한 설정')

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

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>{{ $group->name }} - 권한 설정</h6>
            </div>
        </div>
        <div class="board-card-body">
            <form id="permissionForm" action="{{ route('backoffice.admin-groups.permissions.update', $group) }}" method="POST">
                @csrf
                
                <div class="form-section">
                    <h3>메뉴 접근 권한</h3>
                    <div class="permissions-container">
                        @foreach($menus as $menu)
                            <div class="permission-category">
                                <div class="permission-category-header">
                                    <h4>{{ $menu->name }}</h4>
                                    <label class="permission-item parent-menu">
                                        <input type="checkbox" name="permissions[{{ $menu->id }}]" value="1" @checked(in_array($menu->id, $groupPermissions))>
                                        <span>{{ $menu->name }} 메뉴</span>
                                    </label>
                                </div>
                                @if($menu->children->count() > 0)
                                    <div class="permission-items">
                                        @foreach($menu->children as $child)
                                            <label class="permission-item child-menu">
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

