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
						<th>신청상태</th>
						<td><span class="statebox complet">승인완료</span></td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>2025.09.10 09:00</td>
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
						<th>신청인원</th>
						<td>10명</td>
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
						<td>150,000원</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>방문 카드결제</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="stit nbd_b mt">명단</div>
		<div class="tbl board_write board_apply_list">
			<table>
				<colgroup>
					<col class="appli_view01">
					<col class="appli_view02">
					<col class="appli_view03">
					<col>
				</colgroup>
				<thead>
					<tr>
						<th>이름</th>
						<th>학년</th>
						<th>반</th>
						<th>생년월일</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
					<tr>
						<td>홍길동</td>
						<td>1학년</td>
						<td>1반</td>
						<td>YYYY.MM.DD</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="btns_tac">
			<button type="submit" class="btn_submit btn_wbb">수정하기</button>
			<a href="/print/estimate" target="_blank" class="btn btn_bwb btn_print">견적서</a>
		</div>

	</div>

</main>
@endsection