@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner ">
		<div class="btit"><strong>교육 선택</strong><p>원하는 교육 프로그램을 선택 후 신청하기 버튼을 눌러주세요.</p></div>

		<div class="board_top">
			<div class="total">TOTAL <strong>100</strong></div>
			<div class="selects">
				<select name="" id="">
					<option value="">월 전체</option>
				</select>
				<select name="" id="">
					<option value="">프로그램 전체</option>
				</select>
				<select name="" id="">
					<option value="">신청 가능</option>
				</select>
			</div>
		</div>

		<div class="board_list tablet_break_tbl">
			<table>
				<colgroup>
					<col class="w6_9">
					<col class="w8_3">
					<col class="w8_3">
					<col>
					<col class="w19_4">
					<col class="w11_1">
					<col class="w8_3">
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
					<tr>
						<td class="num">5</td>
						<td class="edu02"><div class="statebox_tb first_come">선착순</div></td>
						<td class="edu03">2025.09.10</td>
						<td class="edu04 tal"><a href="">프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="edu05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="edu06">10/24</td>
						<td class="edu07"><a href="javascript:void(0);" class="btn btn_wkk" onclick="layerShow('pop_restriction')">신청하기</a></td>
					</tr>
					<tr>
						<td class="num">4</td>
						<td class="edu02"><div class="statebox_tb raffle">추첨</div></td>
						<td class="edu03">2025.09.10</td>
						<td class="edu04 tal"><a href="">프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="edu05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="edu06"></td>
						<td class="edu07"><a href="/program/middle_semester_apply_b_end" class="btn btn_wkk">신청하기</a></td>
					</tr>
					<tr>
						<td class="num">3</td>
						<td class="edu02"><div class="statebox_tb naver_form">네이버폼</div></td>
						<td class="edu03">2025.09.10</td>
						<td class="edu04 tal"><a href="">프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="edu05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="edu06"></td>
						<td class="edu07"><a href="/program/middle_semester_apply_b_end" class="btn btn_wkk">신청하기</a></td>
					</tr>
					<tr>
						<td class="num">2</td>
						<td class="edu02"><div class="statebox_tb first_come">선착순</div></td>
						<td class="edu03">2025.09.10</td>
						<td class="edu04 tal"><a href="">프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="edu05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="edu06">24/24</td>
						<td class="edu07"><a href="" class="btn btn_kwk">대기자 신청</a></td>
					</tr>
					<tr>
						<td class="num">1</td>
						<td class="edu02"><div class="statebox_tb first_come">선착순</div></td>
						<td class="edu03">2025.09.10</td>
						<td class="edu04 tal"><a href="">프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="edu05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="edu06">0/24</td>
						<td class="edu07"><a href="javascript:void(0);" class="btn btn_gray">접수예정</a></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="board_bottom">
			<div class="paging">
				<a href="#this" class="arrow two first">맨끝</a>
				<a href="#this" class="arrow one prev">이전</a>
				<a href="#this" class="on">1</a>
				<a href="#this">2</a>
				<a href="#this">3</a>
				<a href="#this">4</a>
				<a href="#this">5</a>
				<a href="#this" class="arrow one next">다음</a>
				<a href="#this" class="arrow two last">맨끝</a>
			</div>
		</div> <!-- //board_bottom -->
	</div>

</main>

<div class="popup pop_info" id="pop_restriction">
	<div class="dm" onclick="layerHide('pop_restriction')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_restriction')"></button>
		<div class="tit mb">신청 제한 안내</div>
		<p class="">이미 신청하신 내역이 확인되었습니다.<br/>프로그램 변경을 원할 경우 마이페이지에서 취소 후 재신청 바랍니다.</p>
		<div class="gbox flex_center colm">
			<p>신청은 학기 1회, 방학 1회만 가능하며 <br/>(1학기/2학기/여름방학/겨울방학 각 1회씩 가능), <br/>추가 참여를 원할 경우 전화 문의 바랍니다.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check" onclick="layerHide('pop_restriction')">확인하기</button>
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