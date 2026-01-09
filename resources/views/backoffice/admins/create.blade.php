@extends('backoffice.layouts.app')

@section('title', '관리자 등록')

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
                <div class="admin-card-body">
                    <form id="adminForm" action="{{ route('backoffice.admins.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-section">
            <h3>관리자 등록</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="login_id">아이디</label>
                    <div class="login-id-input-wrapper">
                        <input type="text" id="login_id" name="login_id" value="{{ old('login_id') }}" placeholder="로그인 아이디를 입력하세요" class="login-id-input">
                        <button type="button" id="check-login-id-btn" class="btn btn-primary btn-check-id">
                            <i class="fas fa-check"></i> 중복 확인
                        </button>
                    </div>
                    <div id="login-id-message" class="login-id-message"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required placeholder="비밀번호를 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">비밀번호 확인</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="비밀번호를 다시 입력하세요">
                    <div class="password-policy-info">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            비밀번호는 영문 대소문자, 숫자, 특수문자 중 2종류 이상 조합하여 10자리 이상으로 입력해주세요.
                        </small>                      
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name">이름</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="이름을 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="contact">연락처</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact') }}" placeholder="연락처를 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="이메일을 입력하세요">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>사용 여부</h3>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', '1') == '1')>
                    <span>Y</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active') == '0')>
                    <span>N</span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <h3>권한 설정</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="admin_group_id">권한 그룹</label>
                    <select id="admin_group_id" name="admin_group_id" required>
                        <option value="">그룹을 선택하세요</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected(old('admin_group_id') == $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="permission-notice">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    일반 관리자는 권한 그룹을 선택해야 합니다. 슈퍼 관리자는 자동으로 모든 권한이 부여됩니다.
                </small>
            </div>
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

@section('scripts')
<script src="{{ asset('js/backoffice/admin-form.js') }}"></script>
@endsection
