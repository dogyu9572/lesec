@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="btit"><strong>단체 신청</strong></div>
		<div class="schedule_wrap">

			<div class="schedule_table">
				<div class="schedule_top">
					<div class="month">
						<button type="button" class="arrow prev">이전달</button>
						<strong>2025.10</strong>
						<button type="button" class="arrow next">다음달</button>
						<button type="button" class="btn_today">당일보기</button>
					</div>
					<ul class="info flex">
						<li class="i_select">선택날짜</li>
						<li class="i_possible" data-applied="0" data-total="24">예약가능</li>
						<li class="i_impossible">예약불가</li>
					</ul>
				</div>
				<div class="table">
					<table>
						<thead>
							<tr>
								<th>일</th>
								<th>월</th>
								<th>화</th>
								<th>수</th>
								<th>목</th>
								<th>금</th>
								<th>토</th>
							</tr>
					</thead>
					<tbody>
						@foreach($calendar as $week)
						<tr>
							@foreach($week as $dayData)
							<td @if($dayData['disabled']) class="disabled" @endif>
								<span>{{ $dayData['day'] }}</span>
								@if(!$dayData['disabled'] && count($dayData['programs']) > 0)
								<ul class="list">
									@foreach($dayData['programs'] as $program)
									@php
										$appliedCount = $program->applied_count ?? 0;
										$capacity = $program->capacity ?? 0;
										$statusClass = 'i_possible';
										if (!$program->is_unlimited_capacity && $appliedCount >= $capacity) {
											$statusClass = 'i_impossible';
										}
									@endphp
									<li class="{{ $statusClass }}" data-applied="{{ $appliedCount }}" data-total="{{ $capacity }}" data-program-id="{{ $program->id }}">{{ $program->program_name }}</li>
									@endforeach
								</ul>
								@endif
							</td>
							@endforeach
						</tr>
						@endforeach
					</tbody>
					</table>
				</div>

				<div class="nebox">
					<p>단체 신청 가능 인원은 최소 10명입니다.</p>
					<p>잔여석이 있을 경우 4명 이상 단체도 신청 가능합니다.</p>
				</div>
			</div>

			<div class="glbox select_day">
				<div class="top">
					<dl class="day"><dt>선택일자</dt><dd>2025.10.20 <span>(월)</span></dd></dl>
					<div class="scroll tbl">
						<table>
							<colgroup>
								<col class="w80"/>
								<col class="w140"/>
								<col/>
								<col class="w100">
								<col class="w120">
							</colgroup>
							<thead>
								<tr>
									<th>선택</th>
									<th>교육기간</th>
									<th>프로그램명</th>
									<th>신청인원/정원</th>
									<th>접수</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="edu11"><label class="check solo"><input type="radio" name="select_day"><i></i></label></td>
									<td class="edu12">2025-10-20 09:00</td>
									<td class="edu13 over_dot">M1. 광학현미경을 이용한 세포 관찰</td>
									<td class="edu14">0/24</td>
									<td class="edu15"><i class="state c1">신청가능</i></td>
								</tr>
								<tr>
									<td class="edu11"><label class="check solo"><input type="radio" name="select_day"><i></i></label></td>
									<td class="edu12">2025-10-20 12:00</td>
									<td class="edu13 over_dot">M2. DNA 추출(단체만 신청 가능)</td>
									<td class="edu14">15/24</td>
									<td class="edu15"><i class="state c2">잔여석 신청 가능</i></td>
								</tr>
								<tr>
									<td class="edu11"><label class="check solo"><input type="radio" name="select_day"><i></i></label></td>
									<td class="edu12">2025-10-20 09:00</td>
									<td class="edu13 over_dot">M3. 분류학으로 보는 생물의 세계</td>
									<td class="edu14">24/24</td>
									<td class="edu15"><i class="state c3">마감</i></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="btm">
					<div class="count">
						<strong>신청 인원수</strong>
						<div class="flex">
							<button class="btn minus">-</button>
							<input type="text" value="10" readonly>
							<button class="btn plus">+</button>
						</div>
					</div>
					<div class="btns">
						<!-- 다른 두 가지 디자인에 대한 버튼 -->
						<!-- <button type="button" class="btn_apply" onclick="layerShow('pop_restriction1')">신청하기</button>
						<button type="button" class="btn_apply" onclick="layerShow('pop_restriction2')">신청하기</button> -->
						<button type="button" class="btn_apply" onclick="layerShow('pop_approval')">신청하기</button>
					</div>
				</div>
			</div>

		</div>
	</div>

</main>

<div class="popup pop_info" id="pop_restriction1">
	<div class="dm" onclick="layerHide('pop_restriction1')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_restriction1')"></button>
		<div class="tit mb">신청 제한 안내</div>
		<p class="">단체신청은 교사만 가능합니다.</p>
		<div class="gbox flex_center colm">
			<p>교사가 아닌 단체 대표가 신청을 원할 경우, 전화문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_restriction1')">확인하기</button>
	</div>
</div>

<div class="popup pop_info" id="pop_restriction2">
	<div class="dm" onclick="layerHide('pop_restriction2')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_restriction2')"></button>
		<div class="tit mb">신청 제한 안내</div>
		<p class="">교사 1인당 최대 3개의 프로그램까지 신청할 수 있습니다.</p>
		<div class="gbox flex_center colm">
			<p>추가로 신청이 필요하신 경우,전화 문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_restriction2')">확인하기</button>
	</div>
</div>

<div class="popup pop_info" id="pop_approval">
	<div class="dm" onclick="layerHide('pop_approval')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_approval')"></button>
		<div class="tit mb">승인 안내</div>
		<p class="">본 신청은 관리자의 승인 절차를 거쳐 최종 확정됩니다.</p>
		<div class="gbox flex_center colm">
			<p>승인 완료 후에는 해당 신청 건이 확정 처리되므로, <br/>변경을 원할 경우 전화 문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<div class="flex_center check_area">
			<label class="check"><input type="checkbox"><i></i>위의 내용을 모두 읽었으며, 내용에 동의합니다.</label>
		</div>
		<button type="button" class="btn_check" onclick="location.href='middle_semester_apply_a_end'">확인하기</button>
	</div>
</div>

<script>
// 백오피스 프로그램 데이터
const programsData = @json($programs ?? []);

$(function () {
	// 날짜 계산용 변수
	let currentDate = new Date({{ $year ?? date('Y') }}, {{ ($month ?? date('m')) - 1 }}, 1); // Month는 0-indexed
	const daysKor = ['일', '월', '화', '수', '목', '금', '토'];

	// 1. 이전달 / 다음달 버튼
	function updateMonthDisplay() {
		const year = currentDate.getFullYear();
		const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
		$('.schedule_top .month strong').text(`${year}.${month}`);
	}

	$('.arrow.prev').on('click', function () {
		currentDate.setMonth(currentDate.getMonth() - 1);
		const year = currentDate.getFullYear();
		const month = currentDate.getMonth() + 1;
		window.location.href = `{{ url('/program/middle_semester_apply_a2') }}?year=${year}&month=${month}`;
	});

	$('.arrow.next').on('click', function () {
		currentDate.setMonth(currentDate.getMonth() + 1);
		const year = currentDate.getFullYear();
		const month = currentDate.getMonth() + 1;
		window.location.href = `{{ url('/program/middle_semester_apply_a2') }}?year=${year}&month=${month}`;
	});
	
	updateMonthDisplay(); // 초기 실행
	selectTodayIfVisible();

	// 2. 당일보기
	function selectTodayIfVisible() {
		const today = new Date();

		// currentDate 기준으로 오늘이 현재 달에 포함되어 있는 경우에만 실행
		if (
			currentDate.getFullYear() === today.getFullYear() &&
			currentDate.getMonth() === today.getMonth()
		) {
			$('.schedule_table .table tbody td').each(function () {
				const dayText = $(this).find('span').text();
				if (!$(this).hasClass('disabled')) {
					if (parseInt(dayText) === today.getDate()) {
						$(this).addClass('select');
						updateSelectionInfo($(this));
					}
				}
			});
		}
	}

	// 오늘보기 버튼
	$('.btn_today').on('click', function () {
		$('.schedule_table .table tbody td').removeClass('select');
		selectTodayIfVisible();
	});


	// 3. 날짜 선택 시 select 클래스 토글
	$('.schedule_table .table tbody td').on('click', function () {
		if ($(this).hasClass('disabled')) return;

		$('.schedule_table .table tbody td').removeClass('select');
		$(this).addClass('select');

		updateSelectionInfo($(this));
	});

	// 4. 날짜 선택 후 상세정보 반영
	function updateSelectionInfo($td) {
		const day = $td.find('span').text().padStart(2, '0');
		const year = currentDate.getFullYear();
		const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
		const selectedDate = `${year}.${month}.${day}`;
		const dateObj = new Date(`${year}-${month}-${day}`);
		const dayOfWeek = daysKor[dateObj.getDay()];

		// 상단 날짜 변경
		$('.glbox.select_day .day dd').html(`${selectedDate} <span>(${dayOfWeek})</span>`);

		// 프로그램 리스트 갱신
		const $listItems = $td.find('.list li');
		const $tbody = $('.glbox.select_day .tbl tbody');
		$tbody.empty();

		$listItems.each(function (index) {
			const title = $(this).text();
			const programId = $(this).data('program-id') ?? 0;

			const applied = $(this).data('applied') ?? 0;
			const total = $(this).data('total') ?? 24;
			const remain = total - applied;

			const remainText = `${applied}/${total}`;

			let status = '';
			let stateClass = '';

			if (remain <= 0) {
				status = '마감';
				stateClass = 'c3';
			} else if (applied > 0) {
				status = '잔여석 신청 가능';
				stateClass = 'c2';
			} else {
				status = '신청가능';
				stateClass = 'c1';
			}

			$tbody.append(`
				<tr data-program-id="${programId}">
					<td class="edu11"><label class="check solo"><input type="radio" name="select_day" data-program-id="${programId}"><i></i></label></td>
					<td class="edu12">${year}-${month}-${day} 09:00</td>
					<td class="edu13 over_dot">${title}</td>
					<td class="edu14">${remainText}</td>
					<td class="edu15"><i class="state ${stateClass}">${status}</i></td>
				</tr>
			`);
		});
	}

	// 5. 신청인원수 증감 로직
	function getMaxCount() {
		let max = 0;
		$('.glbox.select_day .tbl tbody tr').each(function () {
			if ($(this).find('input[type="radio"]').is(':checked')) {
				const countText = $(this).find('td').eq(3).text(); // "0/24"
				const [applied, total] = countText.split('/').map(Number);
				max = total - applied;
			}
		});
		return max;
	}

	// ✅ 신청 인원 input 값 보정 함수
	function updateApplyInput() {
		const max = getMaxCount();
		let input = $('.count input');
		let current = parseInt(input.val(), 10);

		if (max <= 0) {
			current = 0;
		} else if (max < 10) {
			current = Math.min(current, max); // 1~max
			if (current < 1) current = 1;
		} else {
			if (current < 10) current = 10; // 최소 10
			if (current > max) current = max;
		}

		input.val(current);
	}

	// ✅ 라디오 버튼 변경 시 input 자동 보정
	$('.glbox.select_day .tbl').on('change', 'input[type="radio"]', function () {
		updateApplyInput();
	});

	// ✅ 인원수 증가
	$('.btn.plus').on('click', function () {
		let val = parseInt($('.count input').val(), 10);
		let max = getMaxCount();

		if (max <= 0) {
			$('.count input').val(0);
			return;
		}

		if (val < max) {
			val++;
			$('.count input').val(val);
		}
	});

	// ✅ 인원수 감소
	$('.btn.minus').on('click', function () {
		let val = parseInt($('.count input').val(), 10);
		let max = getMaxCount();

		if (max <= 0) {
			$('.count input').val(0);
			return;
		}

		if (max < 10) {
			if (val > 1) val--;
		} else {
			if (val > 10) val--; // 최소 10
		}

		$('.count input').val(val);
	});

	//높이 설정
	function adjustScrollHeight() {
		const scheduleTable = $('.schedule_table');
		const nebox = $('.nebox');
		const scheduleHeight = scheduleTable.outerHeight(true) - (nebox.outerHeight(true) || 0); // .nebox 높이 제외
		const minusHeight = nebox.outerHeight(true) || 0; // scroll 계산 시에도 동일하게 반영

		if (window.innerWidth >= 1200) {
			$('.select_day').css({'height': scheduleHeight, 'max-height': ''});
		} else {
			//$('.select_day').css({'max-height': scheduleHeight, 'height': ''});
		}

		const btmHeight = $('.select_day .btm').outerHeight(true) || 0; // margin 포함
		const dayHeight = $('.select_day .day').outerHeight(true) || 0;
		const top = $('.select_day .top');
		const topPadding = parseInt(top.css('padding-top')) + parseInt(top.css('padding-bottom')) || 0;

		// scroll 영역 높이 계산 (nebox 포함하여 제외)
		const scrollHeight = scheduleHeight - btmHeight - dayHeight - topPadding;

		if (window.innerWidth >= 1200) {
			$('.select_day .scroll').css({'height': scrollHeight, 'max-height': ''});
		} else {
			//$('.select_day .scroll').css({'max-height': scrollHeight, 'height': ''});
		}
	}

	// 실행 및 리사이즈 대응
	$(document).ready(adjustScrollHeight);
	$(window).on('resize', adjustScrollHeight);


	let resizeTimer;
	adjustScrollHeight();
});

//팝업
function layerShow(id) {
	$("#" + id).fadeIn(300);
}
function layerHide(id) {
	$("#" + id).fadeOut(300);
}
</script>

@endsection