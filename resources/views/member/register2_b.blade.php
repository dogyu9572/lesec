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
			<p class="stb">본인 확인을 위해 <strong class="c_blue">휴대전화 또는 문자 인증</strong>을 선택해 진행해 주세요.<br/>안전한 회원가입을 위해 인증 완료 후 다음 단계로 이동합니다.</p>
			<div class="mem_half">				
				<div class="phone_certification sms">
					<strong>SMS 인증</strong>
					<p>휴대전화 번호로 발송된 인증번호를 입력하여 <br/> 본인 확인을 진행할 수 있습니다.</p>
					<button type="button" class="btn btn_wkk" onclick="location.href='/member/register3_b'">SMS 인증하기</button>
				</div>
			</div>
		</div>
	</div>

</main>
@endsection