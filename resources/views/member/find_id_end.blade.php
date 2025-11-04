@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap find_end">
			<div class="ctit mb0">아이디 찾기 완료</div>
			<p class="tac">개인정보 도용 피해방지를 위해 일부 정보는 *로 표기됩니다.<br/>비밀번호가 기억나지 않으실 경우 변경이 가능합니다.</p>
			<div class="find_box">
				<strong>아이디 : ID***</strong>
				<dl>
					<dt>가입일</dt>
					<dd>YYYY-MM-DD</dd>
				</dl>
			</div>
			<div class="btns flex">
				<a href="/member/login" class="btn btn_wbb">로그인</a>
				<a href="/member/find_pw" class="btn btn_bwb mt0">비밀번호 변경</a>
			</div>
		</div>
	</div>

</main>
@endsection