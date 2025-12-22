@extends('layouts.app')
@section('content')
<main class="pb">
	<div class="inner">
		<div class="mem_wrap login_wrap find_end pt0 pb0">
			<div class="ctit mb0">신청 완료</div>
			<p class="tac mb0">예약 신청이 완료되었습니다.</p>
			<div class="gbox">
				<dl>
					<dt>신청 구분</dt>
					<dd>개인 신청</dd>
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
					<dt>신청자 연락처</dt>
					<dd>{{ $completionContext['applicant_phone'] }}</dd>
				</dl>
				<dl>
					<dt>신청 일시</dt>
					<dd>{{ $completionContext['submitted_at'] }}</dd>
				</dl>
			</div>
			<div class="btns flex">
				<a href="{{ route('mypage.application_indi_list') }}" class="btn btn_kwk">나의 신청내역 확인</a>
			</div>
		</div>
	</div>

</main>
@endsection