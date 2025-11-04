@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="board_top vab">
			<div class="board_tab">
				<a href="#this" class="on">전체</a>
				<a href="#this">신청/입금/환불</a>
				<a href="#this">수료증</a>
				<a href="#this">대기자</a>
				<a href="#this">회원정보</a>
				<a href="#this">일반</a>
			</div>
			<div class="search_wrap">
				<select name="" id="">
					<option value="">제목</option>
					<option value="">내용</option>
				</select>
				<div class="search_area">
					<input type="text" placeholder="검색어를 입력해주세요.">
					<button type="button" class="btn">검색</button>
				</div>
			</div>
		</div>

		<div class="faq_wrap">
			<dl>
				<dt><button type="button">신청/입금 확인은 어떻게 하나요?<i></i></button></dt>
				<dd>신청한 프로그램, 참가날짜, 입금 확인은 홈페이지 로그인 후 MYPAGE에서 가능합니다.<br/>
					<br/>
					<img src="/images/img_faq_sample.jpg" alt="">
					<div class="btns">
						<a href="#this" class="btn btn_download flex_center">다운로드</a>
					</div>
				</dd>
			</dl>
			<dl>
				<dt><button type="button">입금자명을 "학생이름+학교"(6자)로 하지 않았어요. 어떻게 하죠?<i></i></button></dt>
				<dd>내용</dd>
			</dl>
			<dl>
				<dt><button type="button">재발급<i></i></button></dt>
				<dd>내용</dd>
			</dl>
			<dl>
				<dt><button type="button">체험학습은 어디에서 진행되나요?<i></i></button></dt>
				<dd>내용</dd>
			</dl>
			<dl>
				<dt><button type="button">돌아가는 교통편은 몇시로 예약해야 하나요?<i></i></button></dt>
				<dd>내용</dd>
			</dl>
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

<script>
$(".faq_wrap dt button").click(function(){
	$(this).parent().next("dd").stop(false,true).slideToggle("fast").parent().stop(false,true).toggleClass("on").siblings().removeClass("on").children("dd").slideUp("fast");
});
</script>

@endsection