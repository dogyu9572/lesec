@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="btit"><strong>단체 신청</strong></div>
		<div
            class="schedule_wrap"
            data-program-page="select"
            data-select-mode="group"
            data-programs='@json($programs ?? [])'
            data-type="{{ $type }}"
            data-year="{{ $year }}"
            data-month="{{ $month }}"
            data-base-url="{{ route('program.select.group', $type) }}"
            data-complete-url="{{ route('program.complete.group', $type) }}"
            data-apply-url="{{ route('program.apply.group.submit', $type) }}">

			<div class="schedule_table">
				<div class="schedule_top">
					<div class="month">
						<button type="button" class="arrow prev">이전달</button>
						<strong>{{ $year }}.{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</strong>
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
										$educationDate = optional($program->education_start_date)->format('Y-m-d') ?? '';
										$educationType = $program->education_type ?? '';
										$educationFee = $program->education_fee ?? 0;
									@endphp
									<li class="{{ $statusClass }}" 
										data-applied="{{ $appliedCount }}" 
										data-total="{{ $capacity }}" 
										data-program-id="{{ $program->id }}"
										data-program-name="{{ $program->program_name }}"
										data-education-date="{{ $educationDate }}"
										data-education-type="{{ $educationType }}"
										data-education-fee="{{ $educationFee }}">{{ $program->program_name }}</li>
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
					<dl class="day"><dt>선택일자</dt><dd>{{ $year }}.{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}.01 <span>(일)</span></dd></dl>
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
									<td colspan="5" class="text-center" style="padding: 40px;">날짜를 선택하세요</td>
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
						<button type="button" class="btn_apply" data-layer-open="pop_approval">신청하기</button>
					</div>
				</div>
			</div>

		</div>
	</div>

</main>

<div class="popup pop_info" id="pop_approval">
	<div class="dm" data-layer-close="pop_approval"></div>
	<div class="inbox">
		<button type="button" class="btn_close" data-layer-close="pop_approval"></button>
		<div class="tit mb">승인 안내</div>
		<p class="">본 신청은 관리자의 승인 절차를 거쳐 최종 확정됩니다.</p>
		<div class="gbox flex_center colm">
			<p>승인 완료 후에는 해당 신청 건이 확정 처리되므로, <br/>변경을 원할 경우 전화 문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<div class="flex_center check_area">
			<label class="check"><input type="checkbox" id="agreement_checkbox"><i></i>위의 내용을 모두 읽었으며, 내용에 동의합니다.</label>
		</div>
		<button type="button" class="btn_check" id="group-submit-btn" data-navigate-complete="{{ route('program.complete.group', $type) }}">확인하기</button>
	</div>
</div>

</div>

@once
	@push('scripts')
		<script src="{{ asset('js/program/program.js') }}"></script>
	@endpush
@endonce

@endsection



