@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <div class="logo-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#3490dc" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="#3490dc" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="#3490dc" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2>관리자 로그인</h2>
                <p class="login-subtitle">백오피스 관리 시스템에 오신 것을 환영합니다</p>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="email">이메일 주소</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input id="email" type="email" class="form-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="이메일을 입력하세요">
                </div>
                @error('email')
                    <span class="error-message">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="#666" stroke-width="2"/>
                        <circle cx="12" cy="16" r="1" fill="#666"/>
                        <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input id="password" type="password" class="form-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="비밀번호를 입력하세요">
                </div>
                @error('password')
                    <span class="error-message">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input class="checkbox-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                    <label class="checkbox-label" for="remember">
                        <span class="checkmark"></span>
                        로그인 상태 유지
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a class="forgot-password" href="{{ route('password.request') }}">
                        비밀번호를 잊으셨나요?
                    </a>
                @endif
            </div>

            <button type="submit" class="login-button">
                <span>로그인</span>
                <svg class="button-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection
