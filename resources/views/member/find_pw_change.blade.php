@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit mb0">비밀번호 변경</div>
			<p class="tac">새로운 비밀번호를 설정해 주세요.</p>
			<div class="inputs">
				@if ($errors->has('find_pw_flow'))
				<p class="error_alert mb16">{{ $errors->first('find_pw_flow') }}</p>
				@endif
				<form method="POST" action="{{ route('member.find_pw_change.submit') }}">
					@csrf
					<input type="password" name="password" class="text" placeholder="새 비밀번호">
					@error('password')
					<p class="error_alert">{{ $message }}</p>
					@enderror
					<input type="password" name="password_confirmation" class="text mt16" placeholder="새 비밀번호 확인">
					<p class="c_green">* 영문/숫자/특수문자를 포함하여 8~20자리로 입력해주세요.</p>
					<button type="submit" class="btn btn_wbb mt4">변경</button>
				</form>
			</div>
		</div>
	</div>

</main>
@endsection