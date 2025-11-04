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
		
			<div class="stit">회원 구분</div>
			<div class="mem_half join_type">
				<a href="/member/register2" class="btn01"><p>교사</p><i></i></a>
				<a href="/member/register2" class="btn02"><p>학생</p><i></i></a>
			</div>
		</div>
	</div>

</main>
@endsection