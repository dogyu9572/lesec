<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta property="og:image" content="/images/og_image.jpg" />
	<link rel="icon" href="/images/favicon.png" type="image/x-icon"/>
    <title>백오피스 로그인</title>
    <link rel="stylesheet" href="{{ asset('css/backoffice.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/login.css') }}">
</head>
<body>
	<div class="intro_wrap">
		<div class="txt">
			<div class="inbox">
				<div class="aic">
					<div class="content">
						<h1>lOGIN</h1>
						<p>백오피스 관리 시스템에 오신 것을 환영합니다.</p>
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
								<input type="text" id="login_id" name="login_id" class="form-input" required value="{{ old('login_id') }}" placeholder="로그인 ID를 입력하세요">
							</div>

							<div class="form-group">
								<label for="password">비밀번호</label>
								<input type="password" id="password" name="password" class="form-input" required placeholder="비밀번호를 입력하세요">
							</div>

							<button type="submit" class="btn">로그인</button>
						</form>
					</div>
				</div>
				<div class="footer">
					© 2024 홈페이지 코리아. All rights reserved.
				</div>
			</div>
		</div>
		<div class="background_img"></div>
	</div>
	
    <script>
        // 자동 로그인 정보 입력
        document.addEventListener('DOMContentLoaded', function() {
            const loginIdInput = document.getElementById('login_id');
            const passwordInput = document.getElementById('password');
            
            // 자동 입력
            if (loginIdInput && passwordInput) {
                loginIdInput.value = 'demo';
                passwordInput.value = 'demo1234';
            }
        });
    </script>
</body>
</html>
