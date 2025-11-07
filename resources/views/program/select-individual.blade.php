@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="btit"><strong>교육 선택</strong><p>원하는 교육 프로그램을 선택 후 신청하기 버튼을 눌러주세요.</p></div>

		<div class="board_top">
			<div class="total">TOTAL <strong>{{ $programs->count() }}</strong></div>
			<div class="selects">
				<select name="filter_month" id="filter_month">
					<option value="">월 전체</option>
					@for($m = 1; $m <= 12; $m++)
						<option value="{{ $m }}" @selected($m == $month)>{{ $m }}월</option>
					@endfor
				</select>
			</div>
		</div>

		<div class="board_list tablet_break_tbl">
			<table>
				<colgroup>
					<col class="w6_9">
					<col class="w8_3">
					<col class="w8_3">
					<col>
					<col class="w19_4">
					<col class="w11_1">
					<col class="w8_3">
				</colgroup>
				<thead>
					<tr>
						<th>NO.</th>
						<th>신청유형</th>
						<th>참가일</th>
						<th>프로그램명</th>
						<th>신청기간</th>
						<th>인원/정원</th>
						<th>신청하기</th>
					</tr>
				</thead>
				<tbody>
					@forelse($programs as $index => $program)
					<tr>
						<td class="num">{{ $programs->count() - $index }}</td>
						<td class="edu02">
							<div class="statebox_tb {{ $program->reception_type }}">{{ $program->reception_type_name }}</div>
						</td>
						<td class="edu03">{{ $program->education_start_date->format('Y.m.d') }}</td>
						<td class="edu04 tal">{{ $program->program_name }}</td>
						<td class="edu05">
							@if($program->application_start_date && $program->application_end_date)
								{{ $program->application_start_date->format('Y.m.d') }} ~ {{ $program->application_end_date->format('Y.m.d') }}
							@else
								-
							@endif
						</td>
						<td class="edu06">
							@if($program->is_unlimited_capacity)
								제한없음
							@else
								{{ $program->applied_count ?? 0 }}/{{ $program->capacity }}
							@endif
						</td>
						<td class="edu07">
							@php
								$currentReceptionType = $program->current_reception_type;
							@endphp
							@if($currentReceptionType === 'closed')
								<span class="btn btn_kwk disabled">마감</span>
							@elseif($program->reception_type === 'naver_form' && $program->naver_form_url)
								<a href="{{ $program->naver_form_url }}" target="_blank" class="btn btn_wkk">신청하기</a>
							@else
								<a href="{{ route('program.complete.individual', $type) }}" class="btn btn_wkk">신청하기</a>
							@endif
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="7" class="text-center" style="padding: 40px;">등록된 프로그램이 없습니다.</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>
		
	</div>

</main>

<div class="popup pop_info" id="pop_restriction">
	<div class="dm" onclick="layerHide('pop_restriction')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_restriction')"></button>
		<div class="tit mb">신청 제한 안내</div>
		<p class="">교사 1인당 최대 3개의 프로그램까지 신청할 수 있습니다.</p>
		<div class="gbox flex_center colm">
			<p>추가로 신청이 필요하신 경우,전화 문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_restriction')">확인하기</button>
	</div>
</div>

<script>
$(function () {
	// 월 필터 변경
	$('#filter_month').on('change', function() {
		const selectedMonth = $(this).val();
		if (selectedMonth) {
			window.location.href = `{{ route('program.select.individual', $type) }}?year={{ $year }}&month=${selectedMonth}`;
		} else {
			window.location.href = `{{ route('program.select.individual', $type) }}?year={{ $year }}`;
		}
	});
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

