@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<div class="btit"><strong>유형 선택</strong><p>프로그램 신청 유형을 선택해 주세요.</p></div>
		<div class="program_type">
			<a href="/program/middle_semester_apply_a" class="c1">
				<span class="tit">단체</span>
				<p>학교 또는 기관 단위로 신청하는 경우 선택해 주세요.</p>
				<p class="s">10인 이상 신청 가능하며, 잔여석이 있는 경우 4명 이상 단체 신청 가능합니다.</p>
			</a>
			<a href="/program/middle_semester_apply_b" class="c2">
				<span class="tit">개인</span>
				<p>개인 신청 시 선택해 주세요.</p>
			</a>
		</div>
	</div>

</main>
@endsection