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
						<td>{{ $application->application_number ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>{{ optional($application->applied_at)->format('Y.m.d H:i') ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청유형</th>
						<td>{{ $application->reception_type_label }}</td>
					</tr>
					<tr>
						<th>신청자명</th>
						<td>{{ $application->applicant_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>학교</th>
						<td>{{ $application->applicant_school_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>학년</th>
						<td>{{ $application->applicant_grade ? $application->applicant_grade . '학년' : '-' }}</td>
					</tr>
					<tr>
						<th>반</th>
						<td>{{ $application->applicant_class ? $application->applicant_class . '반' : '-' }}</td>
					</tr>
					<tr>
						<th>교육유형</th>
						<td>{{ $application->education_type_label }}</td>
					</tr>
					<tr>
						<th>프로그램명</th>
						<td>{{ $application->program_name ?? $application->reservation->program_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>참가일</th>
						<td>
							@php
								$startDate = $application->reservation?->education_start_date ?? null;
								$endDate = $application->reservation?->education_end_date ?? null;
								if ($startDate && $endDate) {
									echo $startDate->format('Y.m.d') . ' ~ ' . $endDate->format('Y.m.d');
								} elseif ($startDate) {
									echo $startDate->format('Y.m.d') . ' ~';
								} elseif ($endDate) {
									echo '~ ' . $endDate->format('Y.m.d');
								} else {
									echo '-';
								}
							@endphp
						</td>
					</tr>
					<tr>
						<th>교육료</th>
						<td>{{ $application->participation_fee ? number_format($application->participation_fee) . '원' : '-' }}</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>
							@php
								$paymentMethods = [
									'bank_transfer' => '무통장 입금',
									'on_site_card' => '방문 카드결제',
									'online_card' => '온라인 카드결제',
								];
							@endphp
							{{ $application->payment_method ? ($paymentMethods[$application->payment_method] ?? $application->payment_method) : '-' }}
						</td>
					</tr>
					<tr>
						<th>결제상태</th>
						<td>
							@if($application->payment_status === 'unpaid')
								<span class="statebox complet">미입금</span>
							@elseif($application->payment_status === 'paid')
								<span class="statebox complet">입금완료</span>
							@elseif($application->payment_status === 'refunded')
								<span class="statebox complet">환불</span>
							@elseif($application->payment_status === 'cancelled')
								<span class="statebox complet">신청 취소</span>
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>추첨결과</th>
						<td>{{ $application->draw_result_label }}</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="btns_tac">
			<a href="{{ route('mypage.application_indi_list') }}" class="btn btn_wbb">목록</a>
			@if($application->payment_status === 'cancelled')
				<a href="javascript:void(0);" class="btn btn_gray">취소 완료</a>
			@elseif($application->draw_result !== 'fail' && $application->payment_status !== 'refunded')
				<form method="POST" action="{{ route('mypage.application_indi_cancel', $application->id) }}" style="display: inline;" onsubmit="return confirm('정말 신청을 취소하시겠습니까?');">
					@csrf
					<button type="submit" class="btn btn_bwb">신청취소</button>
				</form>
			@endif
		</div>

	</div>

</main>

<div class="popup pop_info" id="pop_cancel">
	<div class="dm" onclick="layerHide('pop_cancel')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_cancel')"></button>
		<div class="tit mb0">신청 취소</div>
		<p>참여가 어려울 경우 빠른 취소 부탁드리며, <br/>부득이하게 취소수수료가 발생하는 점 양해 바랍니다.<br/>취소를 원하실 경우 전화<strong class="c_blue">(02-880-4948)</strong><br/>혹은 <strong class="c_blue">이메일(nicemedu@snu.ac.kr)</strong> 로 연락주시기 바랍니다.</p>
		<div class="tbl mt">
			<table>
				<colgroup>
					<col width="59%">
					<col width="41%">
				</colgroup>
				<thead>
					<tr>
						<th>취소일</th>
						<th>취소 수수료</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>체험학습 참여 8일 전까지</td>
						<td>없음</td>
					</tr>
					<tr>
						<td>체험학습 참여 7일 전 ~ 3일 전까지</td>
						<td>체험학습비의 50%</td>
					</tr>
					<tr>
						<td>체험학습 참여 2일 전 ~ 당일</td>
						<td>체험학습비의 100%</td>
					</tr>
				</tbody>
			</table>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_cancel')">확인</button>
	</div>
</div>

<script>
//팝업
function layerShow(id) {
	$("#" + id).fadeIn(300);
}
function layerHide(id) {
	$("#" + id).fadeOut(300);
}
</script>

@endsection