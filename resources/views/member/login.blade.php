@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit">LOGIN<div class="tb">로그인</div></div>
			<div class="inputs">
				<input type="text" class="text" placeholder="아이디를 입력해주세요.">
				<input type="password " class="text" placeholder="비밀번호를 입력해주세요.">
				<div class="btns">
					<label class="check"><input type="checkbox"><i></i>아이디 저장</label>
					<div class="finds">
						<a href="/member/find_id">아이디 찾기</a>
						<a href="/member/find_pw">비밀번호 변경</a>
					</div>
				</div>
				<button type="button" class="btn btn_wbb">로그인</button>
				<a href="/member/register" class="btn btn_bwb">회원가입</a>
			</div>
		</div>
	</div>

</main>
@endsection