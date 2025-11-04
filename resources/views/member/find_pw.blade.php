@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit">아이디 찾기</div>
			<div class="tabs">
				<a href="/member/find_id">아이디 찾기</a>
				<a href="/member/find_pw" class="on">비밀번호 변경</a>
			</div>
			<div class="inputs">
				<input type="text" class="text" placeholder="이름을 입력해 주세요.">
				<input type="text " class="text" placeholder="휴대폰번호를 입력해 주세요.">
				<input type="text " class="text" placeholder="아이디를 입력해 주세요.">
				<button type="button" class="btn btn_wbb mt4" onclick="location.href='/member/find_pw_change'">확인</button>
			</div>
		</div>
	</div>

</main>
@endsection