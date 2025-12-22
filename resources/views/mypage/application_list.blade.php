@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">

		<div class="btit"><strong>나의 신청내역(단체)</strong></div>
		
		<div class="board_top vab">
			<div class="total">TOTAL<strong>{{ $applications->total() }}</strong></div>
		</div>

		@if($applications->count() > 0)
		<div class="board_list tablet_break_tbl">
			<table>
				<colgroup>
					<col class="w5_6"/>
					<col class="w7_6"/>
					<col class="w7_6"/>
					<col/>
					<col class="w19"/>
					<col class="w10"/>
					<col class="w6_7"/>
					<col class="w6_7"/>
					<col class="w7"/>
					<col class="w7"/>
				</colgroup>
				<thead>
					<tr>
						<th>NO.</th>
						<th>신청번호</th>
						<th>교육유형</th>
						<th>프로그램명</th>
						<th>참가일</th>
						<th>결제방법</th>
						<th>결제상태</th>
						<th>신청상태</th>
						<th>명단입력</th>
						<th>신청취소</th>
					</tr>
				</thead>
				<tbody>
					@foreach($applications as $application)
					<tr>
						<td class="num">{{ $applications->total() - ($applications->currentPage() - 1) * $applications->perPage() - $loop->index }}</td>
						<td class="appli02">{{ $application->application_number ?? '-' }}</td>
						<td class="appli03">{{ $application->education_type_label }}</td>
						<td class="appli04 tal">
							<a href="{{ route('mypage.application_view', $application->id) }}">
								{{ $application->reservation->program_name ?? '-' }}
							</a>
						</td>
						<td class="appli05">{{ $application->participation_date_formatted ?? '-' }}</td>
						<td class="appli06">{{ $application->payment_method_label }}</td>
						<td class="appli07">
							@if($application->payment_status === 'unpaid')
								<strong class="c_red">미입금</strong>
							@elseif($application->payment_status === 'paid')
								<strong>입금완료</strong>
							@elseif($application->payment_status === 'cancelled')
								<strong>신청 취소</strong>
							@else
								-
							@endif
						</td>
						<td class="appli08">
							@if($application->application_status === 'pending')
								<div class="statebox wait">승인대기</div>
							@elseif($application->application_status === 'approved')
								<div class="statebox complet">승인완료</div>
							@elseif($application->application_status === 'cancelled')
								<div class="statebox">취소/반려</div>
							@else
								-
							@endif
						</td>
						<!-- <td class="appli09">
							@if($application->application_status === 'approved')
								<a href="{{ route('mypage.application_write', $application->id) }}" class="btn btn_kwk">수정</a>
							@else
								<a href="{{ route('mypage.application_write', $application->id) }}" class="btn btn_wkk">입력</a>
							@endif
						</td> -->
						<td class="appli09">

							{{-- 승인대기: 불가 버튼만 표시 --}}
							@if($application->application_status === 'pending')
								<a href="javascript:void(0);" class="btn btn_gray">불가</a>

							{{-- 승인완료: 수정 버튼 --}}
							@elseif($application->application_status === 'approved')
								<a href="{{ route('mypage.application_write', $application->id) }}" class="btn btn_wkk">입력</a>
							@else
								<a href="{{ route('mypage.application_write', $application->id) }}" class="btn btn_kwk">수정</a>
							@endif

						</td>

						<td class="appli10">
							@if($application->payment_status === 'cancelled')
								<a href="javascript:void(0);" class="btn btn_gray">취소 완료</a>
							@else
								<form method="POST" action="{{ route('mypage.application_cancel', $application->id) }}" style="display: inline;" onsubmit="return confirm('정말 신청을 취소하시겠습니까?');">
									@csrf
									<button type="submit" class="btn btn_kwk">취소</button>
								</form>
							@endif
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="board_bottom">
			<div class="paging">
				@php
					$currentPage = $applications->currentPage();
					$lastPage = $applications->lastPage();
					$start = max(1, $currentPage - 2);
					$end = min($lastPage, $start + 4);
					$start = max(1, $end - 4);
				@endphp
				<a href="{{ $applications->url(1) }}" class="arrow two first">맨끝</a>
				<a href="{{ $applications->previousPageUrl() ?? $applications->url(1) }}" class="arrow one prev">이전</a>
				@for($page = $start; $page <= $end; $page++)
				<a href="{{ $applications->url($page) }}" @if($page === $currentPage) class="on" @endif>{{ $page }}</a>
				@endfor
				<a href="{{ $applications->nextPageUrl() ?? $applications->url($lastPage) }}" class="arrow one next">다음</a>
				<a href="{{ $applications->url($lastPage) }}" class="arrow two last">맨끝</a>
			</div>
		</div> <!-- //board_bottom -->
		@else
		<div class="board_list">
			<p class="empty_message">신청내역이 없습니다.</p>
		</div>
		@endif

	</div>

</main>

@if(session('success'))
<script>
	alert('{{ session('success') }}');
</script>
@endif

@if($errors->has('cancel'))
<script>
	alert('{{ $errors->first('cancel') }}');
</script>
@endif

<div class="popup pop_info" id="pop_cannot">
	<div class="dm" onclick="layerHide('pop_cannot')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_cannot')"></button>
		<div class="tit mb0">수정 불가</div>
		<p>참가자 명단은 참가일 기준 <strong class="c_blue">3일 전</strong>까지만 변경가능합니다.</p>
		<div class="gbox flex_center colm">
			<p>수정을 원할 경우 연락주세요.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check btn btn_wkk" onclick="layerHide('pop_cannot')">확인</button>
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