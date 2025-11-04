@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="stit nbd_b">신청내역</div>
		<div class="tbl board_write board_row">
			<table>
				<tbody>
					<tr>
						<th>신청번호</th>
						<td>신청번호</td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>2025.09.10 09:00</td>
					</tr>
					<tr>
						<th>신청유형</th>
						<td>선착순</td>
					</tr>
					<tr>
						<th>신청자명</th>
						<td>홍길동</td>
					</tr>
					<tr>
						<th>학교</th>
						<td>제주중앙고등학교</td>
					</tr>
					<tr>
						<th>학년</th>
						<td>1학년</td>
					</tr>
					<tr>
						<th>반</th>
						<td>1반</td>
					</tr>
					<tr>
						<th>교육유형</th>
						<td>고등학기</td>
					</tr>
					<tr>
						<th>프로그램명</th>
						<td>프로그램명입니다. 프로그램명입니다.</td>
					</tr>
					<tr>
						<th>교육일</th>
						<td>2025.09.10(수)</td>
					</tr>
					<tr>
						<th>교육료</th>
						<td>60,000원</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>무통장 입금</td>
					</tr>
					<tr>
						<th>결제상태</th>
						<td><span class="statebox complet">입금완료</span></td>
					</tr>
					<tr>
						<th>추첨결과</th>
						<td>-</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="btns_tac">
			<a href="/mypage/application_indi_list" class="btn btn_wbb">목록</a>
			<button type="button" class="btn btn_bwb" onclick="layerShow('pop_cancel')">신청취소</button>
		</div>

	</div>

</main>

<div class="popup pop_info" id="pop_cancel">
	<div class="dm" onclick="layerHide('pop_cancel')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_cancel')"></button>
		<div class="tit mb0">신청 취소</div>
		<p>참여가 어려울 경우 빠른 취소 부탁드리며, <br/>부득이하게 취소수수료가 발생하는 점 양해 바랍니다.<br/>취소를 원하실 경우 전화<strong class="c_blue">(02-880-4948)</strong><br/>혹은 <strong class="c_blue">이메일(nicemedu@snu.ac.kr)</strong> 로 연락주시기 바랍니다.</p>
		<div class="tbl mt">
			<table>
				<colgroup>
					<col width="59%">
					<col width="41%">
				</colgroup>
				<thead>
					<tr>
						<th>취소일</th>
						<th>취소 수수료</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>체험학습 참여 8일 전까지</td>
						<td>없음</td>
					</tr>
					<tr>
						<td>체험학습 참여 7일 전 ~ 3일 전까지</td>
						<td>체험학습비의 50%</td>
					</tr>
					<tr>
						<td>체험학습 참여 2일 전 ~ 당일</td>
						<td>체험학습비의 100%</td>
					</tr>
				</tbody>
			</table>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_cancel')">확인</button>
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