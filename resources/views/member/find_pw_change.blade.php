@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit mb0">비밀번호 변경</div>
			<p class="tac">새로운 비밀번호를 설정해 주세요.</p>
			<div class="inputs">
				<input type="password" class="text" placeholder="새 비밀번호">
				<input type="password" class="text" placeholder="새 비밀번호 확인">
				<p class="c_green">* 영문/숫자/특수문자를 포함하여 9~12자리로 입력해주세요.</p>
				<button type="button" class="btn btn_wbb mt4" onclick="location.href='/member/find_pw_end'">변경</button>
			</div>
		</div>
	</div>

</main>
@endsection