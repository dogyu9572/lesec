@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div
        class="inner"
        data-program-page="select"
        data-select-mode="individual"
        data-type="{{ $type }}"
        data-base-url="{{ route('program.select.individual', $type) }}">
		<div class="btit"><strong>교육 선택</strong><p>원하는 교육 프로그램을 선택 후 신청하기 버튼을 눌러주세요.</p></div>

		@php
			$authMember = Auth::guard('member')->user();
			$isGuest = !$authMember;
			$isTeacher = $authMember && $authMember->member_type === 'teacher';
			$isStudent = $authMember && $authMember->member_type === 'student';
			
			// 학교급 체크
			$schoolLevel = $isStudent ? $authMember->school?->school_level : null;
			$programLevel = str_starts_with($type, 'middle') ? 'middle' : (str_starts_with($type, 'high') ? 'high' : null);
			$isWrongLevel = $isStudent && $schoolLevel && $programLevel && $schoolLevel !== $programLevel;
			
			$statusOptions = [
				'' => '신청 상태 전체',
				'apply' => '신청하기',
				'waitlist' => '대기자 신청',
				'scheduled' => '접수예정',
			];
		@endphp
	

		<div class="board_top">
			<div class="total">TOTAL <strong>{{ $programs->count() }}</strong></div>
			<form id="individual-filter-form" method="GET" action="{{ route('program.select.individual', $type) }}" class="selects">
				<select name="month" id="filter_month">
					<option value="">월 전체</option>
					@for($m = 1; $m <= 12; $m++)
						<option value="{{ $m }}" @selected($filterMonth === $m)>{{ $m }}월</option>
					@endfor
				</select>
				<select name="program" id="filter_program">
					<option value="">프로그램 전체</option>
					@foreach($availableProgramNames as $programName)
						<option value="{{ $programName }}" @selected($filterProgram === $programName)>{{ $programName }}</option>
					@endforeach
				</select>
				<select name="status" id="filter_status">
					@foreach($statusOptions as $value => $label)
						<option value="{{ $value }}" @selected($filterStatus === $value)>{{ $label }}</option>
					@endforeach
				</select>
				<select name="sort" id="filter_sort">
					<option value="education_date" @selected($filterSort === 'education_date')>참가일순</option>
					<option value="application_start_date" @selected($filterSort === 'application_start_date')>신청시작일순</option>
				</select>
			</form>
		</div>

		@if ($errors->has('application'))
		<p class="error_alert mb16">{{ $errors->first('application') }}</p>
		@endif

		<div class="board_list tablet_break_tbl">
			<table>
				<colgroup>
					<col class="w5_6">
					<col class="w6_9">
					<col class="w19_4">
					<col>
					<col class="w19_4">
					<col class="w7">
					<col class="w7">
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
                        <td class="edu03">
                            @if($program->education_start_date && $program->education_end_date && !$program->education_start_date->equalTo($program->education_end_date))
                                {{ $program->education_start_date->format('Y.m.d') }} ~ {{ $program->education_end_date->format('Y.m.d') }}
                            @elseif($program->education_start_date)
                                {{ $program->education_start_date->format('Y.m.d') }}
                            @else
                                -
                            @endif
                        </td>
						<td class="edu04 tal">{{ $program->program_name }}</td>
						<td class="edu05">
							@if($program->application_start_date && $program->application_end_date)
								{{ $program->application_start_date->format('Y.m.d') }} ~ {{ $program->application_end_date->format('Y.m.d') }}
							@else
								-
							@endif
						</td>
						<td class="edu06">
							@if($program->reception_type === 'lottery' || $program->reception_type === 'naver_form')
								
							@elseif($program->is_unlimited_capacity)
								제한없음
							@else
								{{ $program->applied_count_display }}/{{ $program->capacity }}
							@endif
						</td>
						<td class="edu07">
							@if($isGuest)
								<button type="button" class="btn btn_kwk disabled" disabled>로그인 필요</button>
							@elseif($isTeacher)
								<button type="button" class="btn btn_kwk disabled" disabled>학생만 신청 가능</button>
							@elseif($isWrongLevel)
								<button type="button" class="btn btn_kwk disabled" disabled>학교급 불일치</button>
							@elseif($program->individual_action_type === 'waitlist')
								<a href="{{ $program->waitlist_url }}" target="_blank" class="btn btn_kwk">대기자 신청</a>
							@elseif($program->individual_action_type === 'scheduled')
								<span class="btn btn_kwk disabled">접수예정</span>
							@elseif($program->reception_type === 'naver_form' && $program->naver_form_url)
								<a href="{{ $program->naver_form_url }}" target="_blank" class="btn btn_wkk">신청하기</a>
							@elseif($program->individual_action_type === 'apply')
                                @php
                                    $applicationStatus = $program->application_status ?? 'available';
                                @endphp
                                @if($applicationStatus === 'applied')
                                    <button type="button"
                                        class="btn btn_kwk disabled"
                                        data-layer-open="pop_restriction"
                                        style="pointer-events: auto;">
                                        신청 완료
                                    </button>
                                @elseif($applicationStatus === 'blocked')
                                    <button type="button"
                                        class="btn btn_kwk disabled"
                                        data-layer-open="pop_restriction"
                                        style="pointer-events: auto;">
                                        신청 불가
                                    </button>
                                @else
                                    @php
                                        // 신청기간 전인지 체크
                                        $now = now();
                                        $isBeforeApplicationPeriod = $program->application_start_date && $program->application_start_date->greaterThan($now);
                                        
                                        // 선착순에서 정원 마감 여부 체크
                                        $isFirstComeFull = $program->reception_type === 'first_come' 
                                            && !$program->is_unlimited_capacity 
                                            && $program->capacity 
                                            && $program->applied_count_display >= $program->capacity;
                                    @endphp
                                    @if($isBeforeApplicationPeriod)
                                        <a href="javascript:void(0);" class="btn btn_gray">접수예정</a>
                                    @elseif($isFirstComeFull)
                                        {{-- 선착순 정원 마감 시 대기자 신청 --}}
                                        @if($program->waitlist_url)
                                            <a href="{{ $program->waitlist_url }}" target="_blank" class="btn btn_kwk">대기자 신청</a>
                                        @else
                                            <form method="POST"
                                                action="{{ route('program.apply.individual.submit', $type) }}"
                                                class="inline-form"
                                                style="display:inline">
                                                @csrf
                                                <input type="hidden" name="program_reservation_id" value="{{ $program->id }}">
                                                <input type="hidden" name="participation_date" value="{{ optional($program->education_start_date)->format('Y-m-d') }}">
                                                <button type="submit" class="btn btn_kwk">대기자 신청</button>
                                            </form>
                                        @endif
                                    @else
                                        {{-- 정원 마감 전 또는 추첨/네이버폼 --}}
                                        <form method="POST"
                                            action="{{ route('program.apply.individual.submit', $type) }}"
                                            class="inline-form"
                                            style="display:inline">
                                            @csrf
                                            <input type="hidden" name="program_reservation_id" value="{{ $program->id }}">
                                            <input type="hidden" name="participation_date" value="{{ optional($program->education_start_date)->format('Y-m-d') }}">
                                            <button type="submit" class="btn btn_wkk">신청하기</button>
                                        </form>
                                    @endif
                                @endif
                            @else
                                <span class="btn btn_kwk disabled">마감</span>
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

		<div class="board_bottom">
			<div class="paging">
				@php
					$currentPage = $programs->currentPage();
					$lastPage = $programs->lastPage();
					$start = max(1, $currentPage - 2);
					$end = min($lastPage, $start + 4);
					$start = max(1, $end - 4);
				@endphp
				<a href="{{ $programs->url(1) }}" class="arrow two first">맨끝</a>
				<a href="{{ $programs->previousPageUrl() ?? $programs->url(1) }}" class="arrow one prev">이전</a>
				@for($page = $start; $page <= $end; $page++)
				<a href="{{ $programs->url($page) }}" @if($page === $currentPage) class="on" @endif>{{ $page }}</a>
				@endfor
				<a href="{{ $programs->nextPageUrl() ?? $programs->url($lastPage) }}" class="arrow one next">다음</a>
				<a href="{{ $programs->url($lastPage) }}" class="arrow two last">맨끝</a>
			</div>
		</div> <!-- //board_bottom -->
		
	</div>

</main>

<div class="popup pop_info" id="pop_restriction">
	<div class="dm" data-layer-close="pop_restriction"></div>
	<div class="inbox">
		<button type="button" class="btn_close" data-layer-close="pop_restriction"></button>
		<div class="tit mb">신청 제한 안내</div>
        <p class="">이미 신청하신 내역이 확인되었습니다.<br>프로그램 변경을 원할 경우 마이페이지에서 취소 후 재신청 바랍니다.</p>
        <div class="gbox flex_center colm">
            <p>신청은 학기 1회, 방학 1회만 가능하며<br>(1학기/2학기/여름방학/겨울방학 각 1회씩 가능),<br>추가 참여를 원할 경우 전화 문의 바랍니다.</p>
            <p class="tel">02-888-0932~3, 02-880-4948</p>
        </div>
		<button type="button" class="btn_check" data-layer-close="pop_restriction">확인하기</button>
	</div>
</div>

@once
	@push('scripts')
		<script src="{{ asset('js/program/program.js') }}"></script>
	@endpush
@endonce

@endsection

