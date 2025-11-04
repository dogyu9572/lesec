@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">

		<div class="btit"><strong>나의 신청내역(단체)</strong></div>
		
		<div class="board_top vab">
			<div class="total">TOTAL<strong>100</strong></div>
		</div>

		<div class="board_list tablet_break_tbl">
			<table>
				<colgroup>
					<col class="w5_6"/>
					<col class="w7_6"/>
					<col class="w7_6"/>
					<col/>
					<col class="w19"/>
					<col class="w10"/>
					<col class="w6_7"/>
					<col class="w6_7"/>
					<col class="w7"/>
					<col class="w7"/>
				</colgroup>
				<thead>
					<tr>
						<th>NO.</th>
						<th>신청번호</th>
						<th>교육유형</th>
						<th>프로그램명</th>
						<th>교육일</th>
						<th>결제방법</th>
						<th>결제상태</th>
						<th>신청상태</th>
						<th>명단입력</th>
						<th>신청취소</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="num">5</td>
						<td class="appli02">신청번호</td>
						<td class="appli03">고등방학</td>
						<td class="appli04 tal"><a href="/mypage/application_view">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="appli05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="appli06">방문 카드결제</td>
						<td class="appli07"><strong class="c_red">미입금</strong></td>
						<td class="appli08"><div class="statebox wait">승인대기</div></td>
						<td class="appli09"><a href="/mypage/application_write" class="btn btn_wkk">입력</a></td>
						<td class="appli10"><button type="button" class="btn btn_kwk">취소</button></td>
					</tr>
					<tr>
						<td class="num">4</td>
						<td class="appli02">신청번호</td>
						<td class="appli03">고등방학</td>
						<td class="appli04 tal"><a href="/mypage/application_view">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="appli05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="appli06">방문 카드결제</td>
						<td class="appli07"><strong class="c_red">미입금</strong></td>
						<td class="appli08"><div class="statebox wait">승인대기</div></td>
						<td class="appli09"><a href="/mypage/application_write" class="btn btn_wkk">입력</a></td>
						<td class="appli10"><button type="button" class="btn btn_kwk">취소</button></td>
					</tr>
					<tr>
						<td class="num">3</td>
						<td class="appli02">신청번호</td>
						<td class="appli03">고등방학</td>
						<td class="appli04 tal"><a href="/mypage/application_view">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="appli05">YYYY.MM.DD ~ YYYY.MM.DD</td>
						<td class="appli06">방문 카드결제</td>
						<td class="appli07"></td>
						<td class="appli08"><div class="statebox wait">승인대기</div></td>
						<td class="appli09"><a href="javascript:void(0);" class="btn btn_gray">불가</a></td>
						<td class="appli10"><a href="javascript:void(0);" class="btn btn_gray">취소완료</a></td>
					</tr>
					<tr>
						<td class="num">2</td>
						<td class="appli02">신청번호</td>
						<td class="appli03">중등방학</td>
						<td class="appli04 tal"><a href="/mypage/application_view">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="appli05">YYYY.MM.DD</td>
						<td class="appli06"></td>
						<td class="appli07"><strong>입금완료</strong></td>
						<td class="appli08"><div class="statebox complet">승인완료</div></td>
						<td class="appli09"><a href="/mypage/application_write" class="btn btn_kwk">수정</a></td>
						<td class="appli10"><a href="javascript:void(0);" class="btn btn_gray">불가</a></td>
					</tr>
					<tr>
						<td class="num">1</td>
						<td class="appli02">신청번호</td>
						<td class="appli03">중등방학</td>
						<td class="appli04 tal"><a href="/mypage/application_view">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</a></td>
						<td class="appli05">YYYY.MM.DD</td>
						<td class="appli06"></td>
						<td class="appli07"><strong>입금완료</strong></td>
						<td class="appli08"><div class="statebox complet">승인완료</div></td>
						<td class="appli09"><a href="javascript:void(0);" class="btn btn_kwk" onclick="layerShow('pop_cannot')">수정</a></td>
						<td class="appli10"><a href="javascript:void(0);" class="btn btn_gray">불가</a></td>
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

<div class="popup pop_info" id="pop_cannot">
	<div class="dm" onclick="layerHide('pop_cannot')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_cannot')"></button>
		<div class="tit mb0">수정 불가</div>
		<p>참가자 명단은 참가일 기준 <strong class="c_blue">3일 전</strong>까지만 변경가능합니다.</p>
		<div class="gbox flex_center colm">
			<p>수정을 원할 경우 연락주세요.</p>
			<p class="tel">02-888-0932~3, 02-880-4948</p>
		</div>
		<button type="button" class="btn_check btn btn_wkk" onclick="layerHide('pop_cannot')">확인</button>
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