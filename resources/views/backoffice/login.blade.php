<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>백오피스 로그인</title>
    <link rel="stylesheet" href="{{ asset('css/backoffice.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/login.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="login-logo">
                    <h2>관리자 로그인</h2>
                    <p class="login-subtitle">백오피스 관리 시스템에 오신 것을 환영합니다</p>
                </div>
            </div>

            @if(session('error'))
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form class="login-form" action="{{ url('/backoffice/login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="login_id">로그인 ID</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" id="login_id" name="login_id" class="form-input" required value="{{ old('login_id') }}" placeholder="로그인 ID를 입력하세요">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="#666" stroke-width="2"/>
                            <circle cx="12" cy="16" r="1" fill="#666"/>
                            <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="비밀번호를 입력하세요">
                    </div>
                </div>

                <button type="submit" class="login-button">
                    로그인
                </button>
            </form>
        </div>
    </div>
</body>
</html>
