@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit">LOGIN<div class="tb">로그인</div></div>
			<div class="inputs">
				@if (session('status'))
					<p class="c_green mb16">{{ session('status') }}</p>
				@endif
				@if ($errors->has('login_failed'))
					<p class="error_alert mb16">{{ $errors->first('login_failed') }}</p>
				@endif
				<form method="POST" action="{{ route('member.login.submit') }}" class="js-member-form">
					@csrf
					<input type="text" name="login_id" class="text" placeholder="아이디를 입력해주세요." value="{{ old('login_id') }}">
					@error('login_id')
						<p class="error_alert">{{ $message }}</p>
					@enderror
					<input type="password" name="password" class="text mt16" placeholder="비밀번호를 입력해주세요.">
					@error('password')
						<p class="error_alert">{{ $message }}</p>
					@enderror
					<div class="btns">
						<label class="check">
							<input type="checkbox" name="remember_login_id" value="1" {{ old('remember_login_id') ? 'checked' : '' }}>
							<i></i>아이디 저장
						</label>
						<div class="finds">
							<a href="{{ route('member.find_id') }}">아이디 찾기</a>
							<a href="{{ route('member.find_pw') }}">비밀번호 변경</a>
						</div>
					</div>
					<button type="submit" class="btn btn_wbb">로그인</button>
				</form>
				<a href="{{ route('member.register') }}" class="btn btn_bwb">회원가입</a>
			</div>
		</div>
	</div>

</main>
@endsection

@push('scripts')
<script src="{{ asset('js/member/form-common.js') }}?v={{ time() }}"></script>
@endpush