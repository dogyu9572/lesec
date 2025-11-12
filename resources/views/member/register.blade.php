@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 on"><i></i><p>회원 구분</p></li>
				<li class="i2"><i></i><p>본인 인증</p></li>
				<li class="i3"><i></i><p>회원정보 입력</p></li>
				<li class="i4"><i></i><p>회원가입 완료</p></li>
			</ol>
		
			@if ($errors->has('member_type'))
			<p class="error_alert">{{ $errors->first('member_type') }}</p>
			@endif
			@if ($errors->has('process'))
			<p class="error_alert">{{ $errors->first('process') }}</p>
			@endif
			<div class="stit">회원 구분</div>
			<div class="mem_half join_type">
				<a href="{{ route('member.register2', ['member_type' => 'teacher']) }}" class="btn01"><p>교사</p><i></i></a>
				<a href="{{ route('member.register2', ['member_type' => 'student']) }}" class="btn02"><p>학생</p><i></i></a>
			</div>
		</div>
	</div>

</main>
@endsection