@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="apply_write" data-program-page="apply" data-apply-mode="individual">
			<div class="point" id="start"></div>
			<div class="point" id="end"></div>
			<div class="btit"><strong>개인 신청</strong></div>
			<div class="absoarea">
				<div class="tit">프로그램 개요</div>
				<ul>
					<li class="i1"><b>주최</b>{{ $program ? ($program->host ?? '') : '' }}</li>
					<li class="i2">
						<b>기간</b>
						@if($periodSummary)
							{{ $periodSummary }}
							<br/>날짜 중 1회 선택 참가
						@endif
					</li>
					<li class="i3"><b>장소</b>{{ $program ? ($program->location ?? '') : '' }}</li>
					<li class="i4"><b>대상</b>{{ $program ? ($program->target ?? '') : '' }}</li>
				</ul>
				<button type="button" class="btn_apply pc_vw" data-navigate-select="individual" data-select-url="{{ route('program.select.individual', $type) }}">신청하기</button>
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
				<button type="button" class="btn_apply" data-navigate-select="individual" data-select-url="{{ route('program.select.individual', $type) }}">신청하기</button>
			</div>
		</div>
	</div>

</main>

@once
	@push('scripts')
		<script src="{{ asset('js/program/program.js') }}"></script>
	@endpush
@endonce

@endsection