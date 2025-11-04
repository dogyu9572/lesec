@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="apply_write">
			<div class="point" id="start"></div>
			<div class="point" id="end"></div>
			<div class="btit"><strong>개인 신청</strong></div>
			<div class="absoarea">
				<div class="tit">프로그램 개요</div>
				<ul>
					<li class="i1"><b>주최</b>서울대학교 농업생명과학대학 농생명과학공동기기원(NICEM)</li>
					<li class="i2"><b>기간</b>2025년 9월 1일(월) ~ 12월 19일(금) <br/>날짜 중 1회 선택 참가</li>
					<li class="i3"><b>장소</b>서울대학교 관악캠퍼스 75동 608호</li>
					<li class="i4"><b>대상</b>전국 중학생</li>
				</ul>
				<button type="button" class="btn_apply pc_vw" onclick="location.href='/program/middle_semester_apply_b2'">신청하기</button>
			</div>
			<div class="itit mt0">상세 내용</div>
			<div class="glbox">
				<div class="tit">시간표</div>
				<div class="tbl">
					<table>
						<colgroup>
							<col class="w26_8"/>
							<col/>
						</colgroup>
						<thead>
							<tr>
								<th>시간</th>
								<th>내용</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>~ 09:50</td>
								<td>강의실 도착</td>
							</tr>
							<tr>
								<td>10:00 – 12:40</td>
								<td>이론강의 및 실험</td>
							</tr>
							<tr>
								<td>12:40 – 13:40</td>
								<td>점심식사 (중식 제공: 토요일 햄버거)</td>
							</tr>
							<tr>
								<td>13:40 – 15:50</td>
								<td>이론강의 및 실험</td>
							</tr>
							<tr>
								<td>15:50 – 16:00</td>
								<td>수료식 (실험진행 상황에 따라 종료시간이 변동될 수 있습니다.)</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="tit">신청 일정</div>
				<ul class="schedule_box">
					<li class="i1"><div class="tt">신청 기간<span>선착순</span></div><p>2025년 9월 3일(수) 낮 12시 정각 ~ 선착순 마감</p></li>
					<li class="i2"><div class="tt">입금 기간</div><p>참여 확정 문자 수신 후 ~ 9월 4일(목) 오전 10시</p></li>
				</ul>
				<div class="tit">참가비 안내</div>
				<div class="participation_fee">
					<dl>
						<dt>참가비</dt>
						<dd>60,000원(1인)</dd>
					</dl>
					<p class="c_green">* 결제방법 및 기간은 이메일 또는 유선으로 문의 바랍니다.</p>
				</div>
				<div class="tit">문의</div>
				<ul class="contact_list">
					<li class="mail"><b>E-mail</b><p>nicemedu@snu.ac.kr</p></li>
					<li class="tel"><b>전화</b><p><span>02-888-0932,</span> <span>02-888-0933,</span> <span>02-880-4948</span></p></li>
				</ul>
			</div>
			<div class="itit">기타 안내</div>
			<div class="glbox etc_info">
				<p>중식 제공(토요일: 햄버거 제공)므로 전화문의 바랍니다.</p>
				<p>수료증 수여</p>
				<p>조별로 서울대학교 재학생이 배치되어 멘토 역할 수행 (참여 학생이 적을 경우, 조교 없이 진행)</p>
				<p>준비물: 가방, 실험 기록용 카메라(휴대폰 카메라 가능)</p>
				<p>수업시간 이외에 발생하는 참가학생의 사고에 대해서는 본 기관이 책임지지 않습니다.</p>
				<p class="c_blue">청소년수련활동인증프로그램 (한국청소년활동진흥원 인증번호 제5455A08B-10363호)</p>
				<p class="c_red">10인 이상 단체신청 가능하며, 예약은 전화문의 바랍니다.(상시 신청가능)</p>
				<p class="c_red">10인 미만 소규모 단체의 경우도 개설되어 있는 단체반에 추가 신청이 가능하므로 전화문의 바랍니다.</p>
			</div>
			<div class="btn_apply_wrap mo_vw">
				<button type="button" class="btn_apply" onclick="location.href='/program/middle_semester_apply_b2'">신청하기</button>
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