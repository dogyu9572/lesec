@extends('backoffice.layouts.app')

@section('title', '관리자 정보 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
@endsection


@section('content')
<div class="admin-form-container">
    <div class="form-header">      
        <a href="{{ route('backoffice.admins.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">목록으로</span>
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

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6>기본 정보</h6>
                </div>
                <div class="admin-card-body">
                    <form id="adminForm" action="{{ route('backoffice.admins.update', $admin) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-section">
            <h3>기본 정보</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="login_id">아이디</label>
                    <input type="text" id="login_id" name="login_id" value="{{ old('login_id', $admin->login_id) }}" placeholder="로그인 아이디를 입력하세요" readonly>
                </div>
                
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" placeholder="새 비밀번호를 입력하세요">
                    <small class="form-text">*비밀번호는 수정시만 입력해주세요.</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">비밀번호 확인</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="새 비밀번호를 다시 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="name">성명</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}" required placeholder="성명을 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="contact">연락처</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $admin->contact) }}" placeholder="연락처를 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required placeholder="이메일을 입력하세요">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>사용 여부</h3>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $admin->is_active ? '1' : '0') == '1')>
                    <span>사용</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $admin->is_active ? '1' : '0') == '0')>
                    <span>미사용</span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <h3>권한 설정</h3>
            @if($admin->isSuperAdmin())
                <div class="alert alert-info">
                    <i class="fas fa-crown"></i>
                    <strong>슈퍼 관리자</strong><br>
                    슈퍼 관리자는 모든 메뉴에 자동으로 접근 권한이 부여됩니다. 권한 그룹 설정이 필요하지 않습니다.
                </div>
            @else
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_group_id">권한 그룹</label>
                        <select id="admin_group_id" name="admin_group_id" required>
                            <option value="">그룹을 선택하세요</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @selected(old('admin_group_id', $admin->admin_group_id) == $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
