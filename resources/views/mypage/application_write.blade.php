@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<form method="POST" action="{{ route('mypage.application_write.update', $application->id) }}" enctype="multipart/form-data" id="participantForm">
			@csrf
		
		<div class="stit nbd_b">신청내역</div>
		<div class="tbl board_write board_row">
			<table>
				<tbody>
					<tr>
						<th>신청번호</th>
						<td>{{ $application->application_number ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청상태</th>
						<td>
							@if($application->application_status === 'pending')
								<span class="statebox wait">승인대기</span>
							@elseif($application->application_status === 'approved')
								<span class="statebox complet">승인완료</span>
							@elseif($application->application_status === 'cancelled')
								<span class="statebox">취소/반려</span>
							@else
								<span class="statebox wait">승인대기</span>
							@endif
						</td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>{{ optional($application->applied_at)->format('Y.m.d H:i') ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청자명</th>
						<td>{{ $application->applicant_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>학교</th>
						<td>{{ $application->school_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청인원</th>
						<td>{{ $application->applicant_count ? $application->applicant_count . '명' : '-' }}</td>
					</tr>
					<tr>
						<th>교육유형</th>
						<td>{{ $application->education_type_label }}</td>
					</tr>
					<tr>
						<th>프로그램명</th>
						<td>{{ $application->reservation->program_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>교육일</th>
						<td>
							@if($application->participation_date)
								{{ $application->participation_date->format('Y.m.d') }}
								@php
									$dayNames = ['일', '월', '화', '수', '목', '금', '토'];
									$dayName = $dayNames[$application->participation_date->dayOfWeek];
								@endphp
								({{ $dayName }})
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>교육료</th>
						<td>{{ $application->participation_fee ? number_format($application->participation_fee) . '원' : '-' }}</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>
							<div class="flex radios">
								<label class="radio"><input type="radio" name="payment_method" value="bank_transfer" {{ $application->payment_method === 'bank_transfer' ? 'checked' : '' }}><i></i>무통장 입금</label>
								<label class="radio"><input type="radio" name="payment_method" value="on_site_card" {{ $application->payment_method === 'on_site_card' ? 'checked' : '' }}><i></i>방문 카드결제</label>
								<label class="radio"><input type="radio" name="payment_method" value="online_card" {{ $application->payment_method === 'online_card' ? 'checked' : '' }}><i></i>온라인 카드결제</label>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="stit nbd_b mt">명단 입력<span class="c_green">* 는 필수 입력 사항입니다.</span>
			<div class="abso_btns">
				<a href="{{ route('mypage.application_write.sample', $application->id) }}" class="btn btn_kwy">샘플파일 받기</a>
				<label for="csv_upload" class="btn btn_kwy" style="cursor: pointer; margin-left: 5px;">일괄 업로드</label>
				<input type="file" id="csv_upload" name="csv_file" accept=".csv" style="display: none;">
				<button type="button" class="btn btn_wkk btn_add">추가</button>
			</div>
		</div>
		<div class="over_tbl">
			<div class="scroll">
				<div class="tbl board_write board_apply_list">
					<table>
						<colgroup>
							<col width="4%">
							<col>
							<col>
							<col>
							<col>
						</colgroup>
						<thead>
							<tr>
								<th>No</th>
								<th>이름<span>*</span></th>
								<th>학년<span>*</span></th>
								<th>반<span>*</span></th>
								<th>생년월일</th>
							</tr>
						</thead>
						<tbody>
							@forelse($application->participants as $participant)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>
									<input type="hidden" name="participants[{{ $loop->index }}][id]" value="{{ $participant->id }}">
									<input type="text" name="participants[{{ $loop->index }}][name]" class="w100p" placeholder="이름을 입력해주세요." value="{{ $participant->name ?? '' }}" required>
								</td>
								<td>
									<select name="participants[{{ $loop->index }}][grade]" class="w100p" required>
										<option value="">학년을 선택해주세요.</option>
										@for($i = 1; $i <= 3; $i++)
										<option value="{{ $i }}" {{ $participant->grade == $i ? 'selected' : '' }}>{{ $i }}학년</option>
										@endfor
									</select>
								</td>
								<td>
									<select name="participants[{{ $loop->index }}][class]" class="w100p" required>
										<option value="">반을 선택해주세요.</option>
										@for($i = 1; $i <= 20; $i++)
										<option value="{{ $i }}" {{ $participant->class == $i ? 'selected' : '' }}>{{ $i }}반</option>
										@endfor
									</select>
								</td>
								<td><input type="text" name="participants[{{ $loop->index }}][birthday]" class="w100p" placeholder="20010101" value="{{ $participant->birthday ? $participant->birthday->format('Ymd') : '' }}"></td>
							</tr>
							@empty
							<tr>
								<td>1</td>
								<td><input type="text" name="participants[0][name]" class="w100p" placeholder="이름을 입력해주세요." required></td>
								<td>
									<select name="participants[0][grade]" class="w100p" required>
										<option value="">학년을 선택해주세요.</option>
										@for($i = 1; $i <= 3; $i++)
										<option value="{{ $i }}">{{ $i }}학년</option>
										@endfor
									</select>
								</td>
								<td>
									<select name="participants[0][class]" class="w100p" required>
										<option value="">반을 선택해주세요.</option>
										@for($i = 1; $i <= 20; $i++)
										<option value="{{ $i }}">{{ $i }}반</option>
										@endfor
									</select>
								</td>
								<td><input type="text" name="participants[0][birthday]" class="w100p" placeholder="20010101"></td>
							</tr>
							@endforelse
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
					이름, 소속(지역, 학교명, 학력선택, 학년, 반), 생년월일
					<strong>개인정보의 보유 및 이용기간</strong>
					1년 (1년 후 파기)
					<strong>거부권 및 거부시의 불이익</strong>
					동의를 거부할 수 있으며, 동의 거부시 체험학습 신청이 제한될 수 있습니다. 
				</div>
			</div>
			<div class="check_area">
				<label class="check"><input type="checkbox" name="privacy_agree"><i></i><strong>(필수)</strong>개인정보 처리방침에 동의합니다.</label>
			</div>
		</div>

		<div class="btns_tac">
			<button type="submit" class="btn_submit btn_wbb">저장하기</button>
			<a href="{{ route('mypage.application_list') }}" class="btn btn_kwy">신청취소</a>
		</div>

		</form>
	</div>

</main>

@if(session('success'))
<script>
	alert('{{ session('success') }}');
</script>
@endif

@if($errors->has('error'))
<script>
	alert('{{ $errors->first('error') }}');
</script>
@endif

<script>
$(document).ready(function () {
	// 폼 제출 시 유효성 검사
	$('#participantForm').on('submit', function(e) {
		if (!$('input[name="privacy_agree"]').is(':checked')) {
			e.preventDefault();
			alert('개인정보 처리방침에 동의해주세요.');
			$('input[name="privacy_agree"]').focus();
			return false;
		}
	});

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
		const currentRows = $('.board_apply_list tbody tr').length;
		let gradeOptions = '';
		let classOptions = '';
		for (let i = 1; i <= 3; i++) {
			gradeOptions += '<option value="' + i + '">' + i + '학년</option>';
		}
		for (let i = 1; i <= 20; i++) {
			classOptions += '<option value="' + i + '">' + i + '반</option>';
		}
		
		/*const newRow = `
			<tr>
				<td><input type="text" name="participants[${currentRows}][name]" class="w100p" placeholder="이름을 입력해주세요." required></td>
				<td>
					<select name="participants[${currentRows}][grade]" class="w100p" required>
						<option value="">학년을 선택해주세요.</option>
						${gradeOptions}
					</select>
				</td>
				<td>
					<select name="participants[${currentRows}][class]" class="w100p" required>
						<option value="">반을 선택해주세요.</option>
						${classOptions}
					</select>
				</td>
				<td><input type="text" name="participants[${currentRows}][birthday]" class="w100p" placeholder="20010101"></td>
			</tr>
		`;*/
		
		const newRow = `
		<tr>
			<td>${currentRows + 1}</td>   <!-- 번호 자동 증가 -->
			<td><input type="text" name="participants[${currentRows}][name]" class="w100p" placeholder="이름을 입력해주세요." required></td>
			<td>
				<select name="participants[${currentRows}][grade]" class="w100p" required>
					<option value="">학년을 선택해주세요.</option>
					${gradeOptions}
				</select>
			</td>
			<td>
				<select name="participants[${currentRows}][class]" class="w100p" required>
					<option value="">반을 선택해주세요.</option>
					${classOptions}
				</select>
			</td>
			<td><input type="text" name="participants[${currentRows}][birthday]" class="w100p" placeholder="20010101"></td>
		</tr>
	`;
		
		$('.board_apply_list tbody').append(newRow);
		updatePlaceholders();
	});

	$('#csv_upload').on('change', function() {
		if (this.files && this.files[0]) {
			const formData = new FormData();
			formData.append('csv_file', this.files[0]);
			formData.append('_token', '{{ csrf_token() }}');

			$.ajax({
				url: '{{ route("mypage.application_write.upload", $application->id) }}',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						alert('명단이 업로드되었습니다.');
						location.reload();
					} else {
						alert(response.message || '업로드 중 오류가 발생했습니다.');
					}
				},
				error: function(xhr) {
					let message = '업로드 중 오류가 발생했습니다.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						message = xhr.responseJSON.message;
					}
					alert(message);
				}
			});
		}
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
