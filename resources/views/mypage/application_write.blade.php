@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<form method="POST" action="{{ route('mypage.application_write.update', $application->id) }}" enctype="multipart/form-data" id="participantForm" data-is-free="{{ ($application->reservation && $application->reservation->is_free) ? '1' : '0' }}">
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
						<td>{{ $application->display_total_fee_label }}</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>
							@php
								$allowedPaymentMethods = is_array($application->reservation->payment_methods ?? null) ? $application->reservation->payment_methods : [];
								$selectedPayment = in_array($application->payment_method, $allowedPaymentMethods) ? $application->payment_method : ($allowedPaymentMethods[0] ?? null);
								$paymentMethodLabels = \App\Models\GroupApplication::PAYMENT_METHOD_LABELS;
							@endphp
							<div class="flex radios">
								@foreach($allowedPaymentMethods as $methodKey)
									@if(isset($paymentMethodLabels[$methodKey]))
										<label class="radio"><input type="radio" name="payment_method" value="{{ $methodKey }}" {{ $selectedPayment === $methodKey ? 'checked' : '' }}><i></i>{{ $paymentMethodLabels[$methodKey] }}</label>
									@endif
								@endforeach
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="stit nbd_b mt">명단 입력<span class="c_green">* 는 필수 입력 사항입니다.</span>
			@if($application->application_status !== 'approved')
			<div style="color: #ff0000; margin-bottom: 10px; font-weight: bold;">승인 완료 후 명단을 입력할 수 있습니다.</div>
			@endif
			<div style="margin-bottom: 10px; font-size: 16px; font-weight: 500; line-height: 20px;">참여할 모든 학생의 명단을 한 번에 업로드 해주세요. 참여 3일 전까지는 수정 가능합니다.</div>
			<div class="abso_btns mo_long">
				<a href="{{ route('mypage.application_write.sample', $application->id) }}" class="btn btn_kwy">샘플파일 받기</a>
				<label for="csv_upload" class="btn btn_kwy {{ $application->application_status !== 'approved' ? 'disabled' : '' }}" style="cursor: {{ $application->application_status === 'approved' ? 'pointer' : 'not-allowed' }}; margin-left: 5px; {{ $application->application_status !== 'approved' ? 'opacity: 0.5; pointer-events: none;' : '' }}">일괄 업로드</label>
				<input type="file" id="csv_upload" name="csv_file" accept=".csv,.xlsx,.xls" style="display: none;" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
				<button type="button" class="btn btn_wkk btn_add" {{ ($application->application_status !== 'approved' || ($application->applicant_count > 0 && count($application->participants) >= $application->applicant_count)) ? 'disabled' : '' }}>추가</button>
			</div>
		</div>
		<div class="over_tbl">
			<div class="scroll">
				<div class="tbl board_write board_apply_list">
					<table>
						<colgroup>
							<col width="6%">
							<col>
							<col>
							<col>
							<col>
							<col width="10%">
						</colgroup>
						<thead>
							<tr>
								<th>No</th>
								<th>이름<span>*</span></th>
								<th>학년<span>*</span></th>
								<th>반<span>*</span></th>
								<th>생년월일</th>
								<th>삭제</th>
							</tr>
						</thead>
						<tbody>
							@forelse($application->participants as $participant)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>
									<input type="hidden" name="participants[{{ $loop->index }}][id]" value="{{ $participant->id }}">
									<input type="text" name="participants[{{ $loop->index }}][name]" class="w100p" placeholder="이름을 입력해주세요." value="{{ $participant->name ?? '' }}" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
								</td>
								<td>
									<select name="participants[{{ $loop->index }}][grade]" class="w100p" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
										<option value="">학년을 선택해주세요.</option>
										@for($i = 1; $i <= 3; $i++)
										<option value="{{ $i }}" {{ $participant->grade == $i ? 'selected' : '' }}>{{ $i }}학년</option>
										@endfor
									</select>
								</td>
								<td>
									<select name="participants[{{ $loop->index }}][class]" class="w100p" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
										<option value="">반을 선택해주세요.</option>
										@for($i = 1; $i <= 20; $i++)
										<option value="{{ $i }}" {{ $participant->class == $i ? 'selected' : '' }}>{{ $i }}반</option>
										@endfor
									</select>
								</td>
								<td><input type="text" name="participants[{{ $loop->index }}][birthday]" class="w100p" placeholder="20010101" value="{{ $participant->birthday ? $participant->birthday->format('Ymd') : '' }}" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}></td>
								<td>
									<button type="button" class="btn btn_gray btn_delete_participant" style="width: 100%; padding: 5px;" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>삭제</button>
								</td>
							</tr>
							@empty
							<tr>
								<td>1</td>
								<td><input type="text" name="participants[0][name]" class="w100p" placeholder="이름을 입력해주세요." {{ $application->application_status !== 'approved' ? 'disabled' : '' }}></td>
								<td>
									<select name="participants[0][grade]" class="w100p" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
										<option value="">학년을 선택해주세요.</option>
										@for($i = 1; $i <= 3; $i++)
										<option value="{{ $i }}">{{ $i }}학년</option>
										@endfor
									</select>
								</td>
								<td>
									<select name="participants[0][class]" class="w100p" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>
										<option value="">반을 선택해주세요.</option>
										@for($i = 1; $i <= 20; $i++)
										<option value="{{ $i }}">{{ $i }}반</option>
										@endfor
									</select>
								</td>
								<td><input type="text" name="participants[0][birthday]" class="w100p" placeholder="20010101" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}></td>
								<td>
									<button type="button" class="btn btn_gray btn_delete_participant" style="width: 100%; padding: 5px;" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>삭제</button>
								</td>
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
			<button type="submit" class="btn_submit btn_wbb" id="group-application-submit-btn" {{ $application->application_status !== 'approved' ? 'disabled' : '' }}>저장하기</button>
			<a href="{{ route('mypage.application_list') }}" class="btn btn_kwy">취소</a>
		</div>

		</form>
	</div>

</main>

@include('partials.toss-payment-modal')

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

@once
	@push('scripts')
		<script src="{{ asset('js/vendor/tosspayments-v2.js') }}"></script>
	@endpush
@endonce

<script>
$(document).ready(function () {
	var currentTossPayment = null;

	function loadTossScript() {
		return new Promise(function (resolve, reject) {
			if (window.TossPayments) {
				resolve();
				return;
			}
			const s = document.createElement("script");
			s.src = "/js/vendor/tosspayments-v2.js";
			s.onload = resolve;
			s.onerror = function () {
				reject(new Error("결제 스크립트를 불러올 수 없습니다. (네트워크/방화벽 확인)"));
			};
			document.head.appendChild(s);
		});
	}

	// 폼 제출 시 유효성 검사 및 토스 결제 처리
	$('#participantForm').on('submit', function(e) {
		@if($application->application_status !== 'approved')
		e.preventDefault();
		alert('승인 완료 후 명단을 저장할 수 있습니다.');
		return false;
		@endif
		if (!$('input[name="privacy_agree"]').is(':checked')) {
			e.preventDefault();
			alert('개인정보 처리방침에 동의해주세요.');
			$('input[name="privacy_agree"]').focus();
			return false;
		}
		
		// 신청인원 초과 체크
		const currentRows = $('.board_apply_list tbody tr').length;
		const maxApplicants = {{ $application->applicant_count ?? 0 }};
		if (maxApplicants > 0 && currentRows > maxApplicants) {
			e.preventDefault();
			alert('신청인원(' + maxApplicants + '명)을 초과할 수 없습니다.');
			return false;
		}

		const isFree = $('#participantForm').data('is-free') === 1;
		if (isFree) {
			return;
		}

		// 온라인 카드결제 선택 시 토스 결제 흐름
		const selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
		if (selectedPaymentMethod === 'online_card') {
			e.preventDefault();

			let participantCount = 0;
			$('.board_apply_list tbody tr').each(function () {
				const $row = $(this);
				const name = $row.find('input[name*="[name]"]').val();
				const grade = $row.find('select[name*="[grade]"]').val();
				const cls = $row.find('select[name*="[class]"]').val();
				if (name && grade && cls) {
					participantCount++;
				}
			});
			if (participantCount <= 0) {
				alert('명단을 1명 이상 입력한 뒤 결제해 주세요.');
				return false;
			}

			const applicationId = {{ $application->id }};
			const prepareUrl = "{{ route('mypage.application_write.payment.prepare', $application->id) }}";
			const csrf = $('meta[name="csrf-token"]').attr("content");

			if (!prepareUrl || !csrf) {
				alert("결제 정보를 확인할 수 없습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.");
				return false;
			}

			$.ajax({
				url: prepareUrl,
				method: "POST",
				data: { participant_count: participantCount },
				headers: {
					"X-CSRF-TOKEN": csrf,
					Accept: "application/json",
				},
				success: function (data) {
					loadTossScript()
						.then(function () {
							var customerKey = "lesec_group_" + Date.now() + "_" + Math.random().toString(36).substring(2, 12);
							var tossPayments = window.TossPayments(data.client_key);
							var widgets = tossPayments.widgets({ customerKey: customerKey });

							$("#toss-payment-widget").empty();
							$("#toss-agreement").empty();

							return widgets
								.setAmount({ currency: "KRW", value: data.amount })
								.then(function () {
									return widgets.renderPaymentMethods({
										selector: "#toss-payment-widget",
										variantKey: "DEFAULT",
									});
								})
								.then(function () {
									return widgets.renderAgreement({
										selector: "#toss-agreement",
										variantKey: "AGREEMENT",
									});
								})
								.then(function () {
									currentTossPayment = {
										widgets: widgets,
										orderId: data.orderId,
										orderName: "단체 프로그램 신청",
										successUrl: data.success_url,
										failUrl: data.fail_url,
										applicationId: applicationId,
									};
									$("#pop_toss_payment").fadeIn(300);
								});
						})
						.catch(function (err) {
							alert(err && err.message ? err.message : "결제 창을 여는 데 실패했습니다.");
						});
				},
				error: function (xhr) {
					const msg =
						xhr.responseJSON && xhr.responseJSON.message
							? xhr.responseJSON.message
							: "결제 준비에 실패했습니다. 다시 시도해 주세요.";
					alert(msg);
				},
			});

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
		@if($application->application_status !== 'approved')
		alert('승인 완료 후 명단을 추가할 수 있습니다.');
		return false;
		@endif
		
		// 버튼이 비활성화되어 있으면 클릭 차단
		if ($(this).prop('disabled')) {
			return false;
		}
		
		const currentRows = $('.board_apply_list tbody tr').length;
		const maxApplicants = {{ $application->applicant_count ?? 0 }};
		
		if (maxApplicants > 0 && currentRows >= maxApplicants) {
			alert('신청인원(' + maxApplicants + '명)까지만 추가할 수 있습니다.');
			return false;
		}
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
			<td><input type="text" name="participants[${currentRows}][name]" class="w100p" placeholder="이름을 입력해주세요."></td>
			<td>
				<select name="participants[${currentRows}][grade]" class="w100p">
					<option value="">학년을 선택해주세요.</option>
					${gradeOptions}
				</select>
			</td>
			<td>
				<select name="participants[${currentRows}][class]" class="w100p">
					<option value="">반을 선택해주세요.</option>
					${classOptions}
				</select>
			</td>
			<td><input type="text" name="participants[${currentRows}][birthday]" class="w100p" placeholder="20010101"></td>
			<td>
				<button type="button" class="btn btn_gray btn_delete_participant" style="width: 100%; padding: 5px;">삭제</button>
			</td>
		</tr>
	`;
		
		$('.board_apply_list tbody').append(newRow);
		updatePlaceholders();
		updateRowNumbers();
		checkAddButtonState();
	});
	
	// 추가 버튼 상태 체크 함수
	function checkAddButtonState() {
		const currentRows = $('.board_apply_list tbody tr').length;
		const maxApplicants = {{ $application->applicant_count ?? 0 }};
		const $addButton = $('.btn_add');
		const isApproved = {{ $application->application_status === 'approved' ? 'true' : 'false' }};
		
		// 승인 완료 상태이고, 신청인원이 설정되어 있으며, 현재 명단 수가 신청인원에 도달한 경우
		if (isApproved && maxApplicants > 0 && currentRows >= maxApplicants) {
			$addButton.prop('disabled', true);
			$addButton.css('opacity', '0.5');
			$addButton.css('cursor', 'not-allowed');
		} else if (!isApproved) {
			// 승인 전이면 비활성화
			$addButton.prop('disabled', true);
			$addButton.css('opacity', '0.5');
			$addButton.css('cursor', 'not-allowed');
		} else {
			$addButton.prop('disabled', false);
			$addButton.css('opacity', '1');
			$addButton.css('cursor', 'pointer');
		}
	}
	
	// 페이지 로드 시 추가 버튼 상태 체크
	$(document).ready(function() {
		checkAddButtonState();
	});

	// 삭제 버튼 클릭 이벤트 (이벤트 위임 사용)
	$(document).on('click', '.btn_delete_participant', function() {
		@if($application->application_status !== 'approved')
		alert('승인 완료 후 명단을 삭제할 수 있습니다.');
		return false;
		@endif
		if (confirm('정말 삭제하시겠습니까?')) {
			$(this).closest('tr').remove();
			updateRowNumbers();
			checkAddButtonState();
		}
	});

	// 행 번호 업데이트 함수
	function updateRowNumbers() {
		$('.board_apply_list tbody tr').each(function(index) {
			$(this).find('td:first').text(index + 1);
			// name 속성 인덱스도 업데이트
			const rowIndex = index;
			$(this).find('input, select').each(function() {
				const name = $(this).attr('name');
				if (name) {
					$(this).attr('name', name.replace(/participants\[\d+\]/, `participants[${rowIndex}]`));
				}
			});
		});
	}

	$('#csv_upload').on('change', function() {
		@if($application->application_status !== 'approved')
		alert('승인 완료 후 명단을 업로드할 수 있습니다.');
		$(this).val('');
		return false;
		@endif
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
					if (response.success && response.participants && response.participants.length > 0) {
						const $tbody = $('.board_apply_list tbody');
						$tbody.empty();

						response.participants.forEach(function(p, idx) {
							const gradeSelected = (p.grade >= 1 && p.grade <= 3) ? ' selected' : '';
							const gradeOpts = '<option value="">학년을 선택해주세요.</option>' +
								[1,2,3].map(function(i) { return '<option value="' + i + '"' + (p.grade == i ? ' selected' : '') + '>' + i + '학년</option>'; }).join('');
							const classOpts = '<option value="">반을 선택해주세요.</option>' +
								Array.from({length:20}, function(_, i) { var v = i + 1; return '<option value="' + v + '"' + (p.class == v ? ' selected' : '') + '>' + v + '반</option>'; }).join('');

							const row = '<tr><td>' + (idx + 1) + '</td>' +
								'<td><input type="text" name="participants[' + idx + '][name]" class="w100p" placeholder="이름을 입력해주세요." value="' + (p.name || '').replace(/"/g, '&quot;') + '"></td>' +
								'<td><select name="participants[' + idx + '][grade]" class="w100p">' + gradeOpts + '</select></td>' +
								'<td><select name="participants[' + idx + '][class]" class="w100p">' + classOpts + '</select></td>' +
								'<td><input type="text" name="participants[' + idx + '][birthday]" class="w100p" placeholder="20010101" value="' + (p.birthday || '') + '"></td>' +
								'<td><button type="button" class="btn btn_gray btn_delete_participant" style="width: 100%; padding: 5px;">삭제</button></td></tr>';
							$tbody.append(row);
						});

						updatePlaceholders();
						updateRowNumbers();
						checkAddButtonState();
						alert(response.message || '명단을 불러왔습니다. 저장하기 버튼을 눌러 저장해주세요.');
					} else if (response.success) {
						alert(response.message || '명단을 불러왔습니다. 저장하기 버튼을 눌러 저장해주세요.');
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
		$(this).val('');
	});

	// 토스 결제 모달 "결제하기" 버튼 클릭
	$(document).off("click", "#toss-payment-submit-btn");
	$(document).on("click", "#toss-payment-submit-btn", function () {
		if (!currentTossPayment) {
			return;
		}
		var payload = currentTossPayment;
		var $btn = $(this);
		$btn.prop("disabled", true);
		payload.widgets
			.requestPayment({
				orderId: payload.orderId,
				orderName: payload.orderName,
				successUrl: payload.successUrl,
				failUrl: payload.failUrl,
			})
			.catch(function (err) {
				alert(err && err.message ? err.message : "결제 요청에 실패했습니다.");
				$btn.prop("disabled", false);
			});
	});

	// 토스 결제 모달 닫기 시 상태 초기화
	$(document).on("click", "[data-layer-close='pop_toss_payment']", function () {
		currentTossPayment = null;
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
