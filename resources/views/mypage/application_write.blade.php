@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="stit nbd_b">신청내역</div>
		<div class="tbl board_write board_row">
			<table>
				<tbody>
					<tr>
						<th>신청번호</th>
						<td>신청번호</td>
					</tr>
					<tr>
						<th>신청상태</th>
						<td><span class="statebox wait">승인대기</span></td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>2025.09.10 09:00 </td>
					</tr>
					<tr>
						<th>신청자명</th>
						<td>홍길동</td>
					</tr>
					<tr>
						<th>학교</th>
						<td>제주중앙고등학교 </td>
					</tr>
					<tr>
						<th>신청인원</th>
						<td>10명</td>
					</tr>
					<tr>
						<th>교육유형</th>
						<td>고등학기</td>
					</tr>
					<tr>
						<th>프로그램명</th>
						<td>프로그램명입니다. 프로그램명입니다.</td>
					</tr>
					<tr>
						<th>교육일</th>
						<td>2025.09.10(수)</td>
					</tr>
					<tr>
						<th>교육료</th>
						<td>150,000원</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>
							<div class="flex radios">
								<label class="radio"><input type="radio" name="payment" checked><i></i>무통장 입금</label>
								<label class="radio"><input type="radio" name="payment"><i></i>방문 카드결제</label>
								<label class="radio"><input type="radio" name="payment"><i></i>온라인 카드결제</label>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="stit nbd_b mt">명단 입력<span class="c_green">* 는 필수 입력 사항입니다.</span>
			<div class="abso_btns">
				<button class="btn btn_kwy">일괄 업로드</button>
				<button class="btn btn_wkk btn_add">추가</button>
			</div>
		</div>
		<div class="over_tbl">
			<div class="scroll">
				<div class="tbl board_write board_apply_list">
					<table>
						<thead>
							<tr>
								<th>이름<span>*</span></th>
								<th>학년<span>*</span></th>
								<th>반<span>*</span></th>
								<th>생년월일</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><input type="text" class="w100p" placeholder="이름을 입력해주세요."></td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">1학년</option>
									</select>
								</td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">1반</option>
									</select>
								</td>
								<td><input type="text" class="w100p" placeholder="20010101"></td>
							</tr>
							<tr>
								<td><input type="text" class="w100p" placeholder="이름을 입력해주세요."></td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">학년을 선택해주세요.</option>
									</select>
								</td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">반을 선택해주세요.</option>
									</select>
								</td>
								<td><input type="text" class="w100p" placeholder="20010101"></td>
							</tr>
							<tr>
								<td><input type="text" class="w100p" placeholder="이름을 입력해주세요."></td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">학년을 선택해주세요.</option>
									</select>
								</td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">반을 선택해주세요.</option>
									</select>
								</td>
								<td><input type="text" class="w100p" placeholder="20010101"></td>
							</tr>
							<tr>
								<td><input type="text" class="w100p" placeholder="이름을 입력해주세요."></td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">학년을 선택해주세요.</option>
									</select>
								</td>
								<td>
									<select name="" id="" class="w100p">
										<option value="">반을 선택해주세요.</option>
									</select>
								</td>
								<td><input type="text" class="w100p" placeholder="20010101"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="stit nbd_b mt">개인정보 수집 이용동의</div>
		<div class="term_area">
			<div class="textarea">
				<div class="scroll">
					<strong>개인정보의 수집이용 목적</strong>
					체험학습 신청, 회원관리, 수료증 발급
					<strong>수집하려는 개인정보의 항목</strong>
					아이디, 비밀번호, 이름, 사용자구분, 소속(지역, 학교명, 학년, 반, 생년월일, 성별), 전화/핸드폰, 이메일
					<strong>개인정보의 보유 및 이용기간</strong>
					1년 (1년 후 파기)
					<strong>거부권 및 거부시의 불이익</strong>
					동의를 거부할 수 있으며, 동의 거부시 체험학습 신청이 제한될 수 있습니다. 
				</div>
			</div>
			<div class="check_area">
				<label class="check"><input type="checkbox"><i></i><strong>(필수)</strong>개인정보 처리방침에 동의합니다.</label>
			</div>
		</div>

		<div class="btns_tac">
			<button type="submit" class="btn_submit btn_wbb">저장하기</button>
			<button type="submit" class="btn btn_kwy">신청취소</button>
		</div>

	</div>

</main>

<script>
$(document).ready(function () {
	function updatePlaceholders() {
		const isMobile = window.innerWidth <= 768;

		if (isMobile) {
			$('input[placeholder="이름을 입력해주세요."]').attr('placeholder', '이름');
			$('select').each(function() {
				const firstOption = $(this).find('option').first();
				if (firstOption.text().includes('학년을 선택해주세요.')) firstOption.text('학년');
				if (firstOption.text().includes('반을 선택해주세요.')) firstOption.text('반');
			});
		} else {
			$('input[placeholder="이름"]').attr('placeholder', '이름을 입력해주세요.');
			$('select').each(function() {
				const firstOption = $(this).find('option').first();
				if (firstOption.text() === '학년') firstOption.text('학년을 선택해주세요.');
				if (firstOption.text() === '반') firstOption.text('반을 선택해주세요.');
			});
		}
	}

	// 처음 실행
	updatePlaceholders();

	// 브라우저 크기 변경 시 즉시 반영
	$(window).on('resize', updatePlaceholders);

	$('.btn_add').on('click', function () {
		const newRow = `
			<tr>
				<td><input type="text" class="w100p" placeholder="이름을 입력해주세요."></td>
				<td>
					<select name="" id="" class="w100p">
						<option value="">학년을 선택해주세요.</option>
					</select>
				</td>
				<td>
					<select name="" id="" class="w100p">
						<option value="">반을 선택해주세요.</option>
					</select>
				</td>
				<td><input type="text" class="w100p" placeholder="20010101"></td>
			</tr>
		`;
		
		$('.board_apply_list tbody').append(newRow);
		updatePlaceholders();
	});
//over_tbl
	$(window).on('scroll resize', function() {
		$(".over_tbl").each(function() {
			var elementTop = $(this).offset().top;
			var elementHeight = $(this).outerHeight();
			var elementBottom = elementTop + elementHeight;

			var viewportTop = $(window).scrollTop();
			var viewportMiddle = viewportTop + ($(window).height() / 2);

			// 요소가 브라우저 중간 지점에 도달했을 때 클래스 추가
			if (elementTop < viewportMiddle && elementBottom > viewportTop) {
				$(this).addClass("on");
			}
		});
	});

	// 터치 이벤트 추가: 손가락으로 스와이프할 때 클래스 추가
	$(".over_tbl").each(function() {
		let startX = 0, moveX = 0;
		$(this).on('touchstart', function(e) {
			startX = e.originalEvent.touches[0].pageX;
		});
		$(this).on('touchmove', function(e) {
			moveX = e.originalEvent.touches[0].pageX;
			let distance = Math.abs(moveX - startX);

			if (distance > 30) {
				$(this).addClass("on");
			}
		});
	});
});
</script>

@endsection