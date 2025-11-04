@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 onf"><i></i><p>회원 구분</p></li>
				<li class="i2 on"><i></i><p>본인 인증</p></li>
				<li class="i3"><i></i><p>회원정보 입력</p></li>
				<li class="i4"><i></i><p>회원가입 완료</p></li>
			</ol>
		
			<div class="stit mb0">본인 인증</div>
			<p class="stb">만 14세 미만 회원은 <strong class="c_blue">보호자(법정대리인)의 동의</strong>가 필요합니다.<br/>보호자 인증 완료 후 회원가입을 진행할 수 있습니다.</p>
			<div class="phone_certification">
				<strong>휴대폰 인증</strong>
				<p>휴대전화 인증을 통해 보호자(법정대리인) 확인 후 <br/>안전하게 회원가입을 진행할 수 있습니다.</p>
				<button type="button" class="btn btn_wkk" onclick="location.href='/member/register3_a'">휴대폰 인증하기</button>
			</div>
		</div>
	</div>

</main>
@endsection