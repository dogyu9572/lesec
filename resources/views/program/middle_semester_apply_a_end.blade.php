@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<div class="mem_wrap login_wrap find_end pb0 pt0">
			<div class="ctit mb0">신청 완료</div>
			<p class="tac mb0">예약 신청이 완료되었습니다.</p>
		</div>
		<div class="apply_end_notice tac">관리자의 확인 후 예약이 승인되며, 승인 이후 변경을 원할 경우 전화 문의 바랍니다.<br/>참가자 명단은 마이페이지에서 입력 및 수정이 가능하며, 참가일 기준 3일 전까지만 변경할 수 있으니 유의해 주시기 바랍니다.</div>
		<div class="mem_wrap login_wrap find_end pb0">
			<div class="gbox">
				<dl>
					<dt>신청 구분</dt>
					<dd>단체 신청</dd>
				</dl>
				<dl>
					<dt>프로그램명</dt>
					<dd>P1: 동・식물 DNA 추출 (생명과학)</dd>
				</dl>
				<dl>
					<dt>교육일</dt>
					<dd>YYYY.MM.DD</dd>
				</dl>
				<dl>
					<dt>신청 번호</dt>
					<dd>신청번호 표시</dd>
				</dl>
				<dl>
					<dt>신청자 이름</dt>
					<dd>홍길동</dd>
				</dl>
				<dl>
					<dt>신청 인원 </dt>
					<dd>10명</dd>
				</dl>
				<dl>
					<dt>신청 일시</dt>
					<dd>2025-09-10 09:00</dd>
				</dl>
			</div>
			<div class="btns flex">
				<a href="/mypage/application_list" class="btn btn_kwk">나의 신청내역 확인</a>
			</div>
		</div>
	</div>

</main>
@endsection