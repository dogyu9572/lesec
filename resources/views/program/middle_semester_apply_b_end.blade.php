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
					<dd>M3. 분류학으로 보는 생물의 세계</dd>
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
					<dt>신청자 연락처</dt>
					<dd>010-0000-0000</dd>
				</dl>
				<dl>
					<dt>신청 일시</dt>
					<dd>2025-09-10 09:00</dd>
				</dl>
			</div>
			<div class="btns flex">
				<a href="/mypage/application_indi_list" class="btn btn_kwk">나의 신청내역 확인</a>
			</div>
		</div>
	</div>

</main>
@endsection