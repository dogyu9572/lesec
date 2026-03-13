@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner" data-program-type="{{ $type }}" data-member-type="{{ $member ? $member->member_type : '' }}" data-school-level="{{ $member ? ($member->school?->school_level ?? '') : '' }}">
		<div class="btit"><strong>유형 선택</strong><p>프로그램 신청 유형을 선택해 주세요.</p></div>
		<div class="program_type">
			<a href="{{ route('program.apply.group', $type) }}" class="c1" data-check-school-level="group">
				<span class="tit">단체</span>
				<p>학교 또는 기관 단위로 신청하는 경우 선택해 주세요.</p>
				<p class="s">
					@if(in_array($type, ['middle_vacation', 'high_vacation']))
						{{-- 방학 --}}
						단체 신청 가능 인원은 최소 20명입니다.<br>
						잔여석이 있을 경우 4명 이상 단체도 신청 가능합니다.
					@else
						{{-- 학기 (중등학기, 고등학기, 특별) --}}
						단체 신청 가능 인원은 최소 10명입니다.<br>
						잔여석이 있을 경우 4명 이상 단체도 신청 가능합니다.
					@endif
				</p>
			</a>
			<a href="{{ route('program.apply.individual', $type) }}" class="c2" data-check-school-level="individual">
				<span class="tit">개인</span>
				<p>개인 신청 시 선택해 주세요.</p>
			</a>
		</div>
	</div>

</main>

@once
@push('scripts')
<script>
$(function() {
	// 개인 신청 링크 클릭 시 학교급 체크
	$(document).on('click', '[data-check-school-level]', function(e) {
		e.preventDefault();
		const $link = $(this);
		const $inner = $link.closest('.inner');
		const memberType = $inner.data('memberType');
		const schoolLevel = $inner.data('schoolLevel');
		const programType = $inner.data('programType');
		const targetType = $link.data('checkSchoolLevel');
		const url = $link.attr('href');
		
		// 1) 학교급(중등/고등) 우선 검사: 불일치 시 항상 이 메시지 우선 표시
		if (schoolLevel) {
			const lowerType = String(programType || '').toLowerCase();
			const programLevel = lowerType.includes('middle') ? 'middle' : (lowerType.includes('high') ? 'high' : null);
			if (programLevel && schoolLevel !== programLevel) {
				// 초등 교사가 중등/고등 프로그램 신청 시
				if (schoolLevel === 'elementary') {
					alert('특별 프로그램만 신청 가능합니다.');
					return;
				}
				// 중등/고등 교사가 다른 학교급 프로그램 신청 시
				const levelName = schoolLevel === 'middle' ? '중등' : '고등';
				alert(`회원님은 ${levelName} 프로그램만 신청 가능합니다.`);
				return;
			}
		}

		// 2) 회원 유형에 따른 신청 유형 차단
		if (memberType === 'student' && targetType === 'group') {
			alert('단체 신청은 교사만 가능합니다. 개인 신청을 이용해주세요.');
			return;
		}
		if (memberType === 'teacher' && targetType === 'individual') {
			alert('개인 신청은 학생만 가능합니다. 단체 신청을 이용해주세요.');
			return;
		}
		
		// 체크 통과 시 페이지 이동
		window.location.href = url;
	});
});
</script>
@endpush
@endonce

@endsection