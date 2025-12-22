@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap join_wrap">
			@if ($errors->has('age_group'))
			<p class="error_alert">{{ $errors->first('age_group') }}</p>
			@endif
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 onf"><i></i><p>회원 구분</p></li>
				<li class="i2 on"><i></i><p>본인 인증</p></li>
				<li class="i3"><i></i><p>회원정보 입력</p></li>
				<li class="i4"><i></i><p>회원가입 완료</p></li>
			</ol>
		
			<div class="stit mb0">본인 인증</div>
			<p class="stb">서비스 이용을 위해 <strong class="c_blue">본인 인증</strong>이 필요합니다.<br/>만 14세 미만 회원은 보호자 동의 후 가입이 가능하며, 만 14세 이상 회원은 본인 명의 휴대전화 및 문자로 인증을 진행합니다.</p>
			<div class="mem_half years_type">
				<!-- <a href="{{ route('member.register2_a') }}" class="btn01"><span class="tt">만 14세 미만 회원</span><p>만 14세 미안 회원은 개인정보보호법에 따라 <br/><strong>법정대리인(보호자)의 동의</strong>가 필요합니다.</p></a> -->
				@php
					$member_type = request('member_type');   // ★ GET 파라미터로 받기
				@endphp

				{{-- 만 14세 미만 회원: teacher가 아닐 때만 표시 --}}
				@if($member_type !== 'teacher')
					<a href="{{ route('member.register2_a') }}" class="btn01">
						<span class="tt">만 14세 미만 회원</span>
						<p>만 14세 미만 회원은 개인정보보호법에 따라 <br/>
							<strong>법정대리인(보호자)의 동의</strong>가 필요합니다.
						</p>
					</a>
				@endif
				<a href="{{ route('member.register2_b') }}" class="btn02"><span class="tt">만 14세 이상 회원</span><p>만 14세 이상 회원은 <strong>본인 명의의 <br/>휴대전화를 통해</strong> 인증이 진행됩니다.</p></a>
			</div>
		</div>
	</div>

</main>
@endsection