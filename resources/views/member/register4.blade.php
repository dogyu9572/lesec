@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 onf"><i></i><p>회원 구분</p></li>
				<li class="i2 onf"><i></i><p>본인 인증</p></li>
				<li class="i3 onf"><i></i><p>회원정보 입력</p></li>
				<li class="i4 on"><i></i><p>회원가입 완료</p></li>
			</ol>
		
			<div class="register_end">
				<strong>회원가입 완료</strong>
				<p>회원가입이 완료되었습니다.</p>
				@if (!empty($completedLoginId))
				<p class="tac">아이디 <strong>{{ $completedLoginId }}</strong>로 로그인해 주세요.</p>
				@endif
				<a href="/member/login" class="btn_submit btn_wbb">로그인</a>
			</div>
		</div>
	</div>

</main>
@endsection