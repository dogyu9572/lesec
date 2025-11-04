@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<div class="apply_write">
			<div class="point" id="start"></div>
			<div class="point" id="end"></div>
			<div class="btit"><strong>단체 신청</strong></div>
			<div class="absoarea">
				<div class="tit">프로그램 개요</div>
				<ul>
					<li class="i1"><b>주최</b>{{ $program ? ($program->host ?? '') : '' }}</li>
					<li class="i2">
						<b>기간</b>
						@if($program && $program->period_start && $program->period_end)
							@php
								$days = ['일', '월', '화', '수', '목', '금', '토'];
								$startDay = $days[$program->period_start->dayOfWeek] ?? '';
								$endDay = $days[$program->period_end->dayOfWeek] ?? '';
							@endphp
							{{ $program->period_start->format('Y년 n월 j일') }}({{ $startDay }}) ~ 
							{{ $program->period_end->format('n월 j일') }}({{ $endDay }}) 
							<br/>날짜 중 1회 선택 참가
						@endif
					</li>
					<li class="i3"><b>장소</b>{{ $program ? ($program->location ?? '') : '' }}</li>
					<li class="i4"><b>대상</b>{{ $program ? ($program->target ?? '') : '' }}</li>
				</ul>
				<button type="button" class="btn_apply pc_vw" onclick="location.href='{{ $programService->getGroupApplySelectRoute($type) }}'">신청하기</button>
			</div>
			<div class="itit mt0">상세 내용</div>
			<div class="glbox">
				@if($program && $program->detail_content)
					{!! $program->detail_content !!}
				@endif
			</div>
			<div class="itit">기타 안내</div>
			<div class="glbox etc_info">
				@if($program && $program->other_info)
					{!! $program->other_info !!}
				@endif
			</div>
			<div class="btn_apply_wrap mo_vw">
				<button type="button" class="btn_apply" onclick="location.href='{{ $programService->getGroupApplySelectRoute($type) }}'">신청하기</button>
			</div>
		</div>
	</div>

</main>

<script>
$(function() {
	var $wrap = $('.apply_write');
	var $abso = $wrap.find('.absoarea');
	var $start = $wrap.find('#start');
	var $end = $wrap.find('#end');
	var $header = $('.header');

	// ① absoarea 높이에서 header 높이만큼 빼서 bottom 설정
	var bottomValue = $abso.outerHeight() + $header.outerHeight();
	if (bottomValue < 0) bottomValue = 0; // 음수 방지

	$end.css('bottom', bottomValue + 'px');

	// ② 스크롤 감지
	$(window).on('scroll', function() {
		var scrollTop = $(this).scrollTop();
		var startTop = $start.offset().top;
		var endTop = $end.offset().top;

		if (scrollTop >= endTop) {
			$wrap.addClass('end').removeClass('start');
		} else if (scrollTop >= startTop) {
			$wrap.addClass('start').removeClass('end');
		} else {
			$wrap.removeClass('start end');
		}
	}).trigger('scroll');
});
</script>

@endsection