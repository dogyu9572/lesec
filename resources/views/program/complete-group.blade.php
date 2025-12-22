@extends('layouts.app')
@section('content')
<main class="pb">
	<div class="inner">
		<div class="mem_wrap login_wrap find_end pb0 pt0">
			<div class="ctit mb0">신청 완료</div>
			<p class="tac mb0">예약 신청이 완료되었습니다.</p>
		</div>
		<div class="apply_end_notice tac">
			관리자의 확인 후 예약이 승인되며, 승인 이후 변경을 원할 경우 전화 문의 바랍니다.<br/>
			참가자 명단은 마이페이지에서 입력 및 수정이 가능하며, 참가일 기준 3일 전까지만 변경할 수 있으니 유의해 주시기 바랍니다.
		</div>
		<div class="mem_wrap login_wrap find_end pb0">
			<div class="gbox">
				<dl>
					<dt>신청 구분</dt>
					<dd>단체 신청</dd>
				</dl>
				<dl>
					<dt>프로그램명</dt>
					<dd>{{ $completionContext['program_name'] !== '' ? $completionContext['program_name'] : '프로그램 정보가 아직 등록되지 않았습니다.' }}</dd>
				</dl>
				<dl>
					<dt>참가일</dt>
					<dd>{{ $completionContext['education_date'] }}</dd>
				</dl>
				<dl>
					<dt>신청 번호</dt>
					<dd>{{ $completionContext['application_number'] }}</dd>
				</dl>
				<dl>
					<dt>신청자 이름</dt>
					<dd>{{ $completionContext['applicant_name'] }}</dd>
				</dl>
				<dl>
					<dt>신청 인원 </dt>
					<dd>{{ $completionContext['applicant_count'] }}</dd>
				</dl>
				<dl>
					<dt>신청 일시</dt>
					<dd>{{ $completionContext['submitted_at'] }}</dd>
				</dl>
			</div>
			<div class="btns flex">
				<a href="{{ route('mypage.application_list') }}" class="btn btn_kwk">나의 신청내역 확인</a>
			</div>
		</div>
	</div>

</main>
@endsection