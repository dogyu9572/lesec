@extends('layouts.app')
@section('content')
<main>
    
	<div class="estimate_wrap">

		<div class="top">
			<div class="tit">견적서</div>
			<div class="logo"><img src="/images/logo_print.png" alt=""></div>
		</div>

		<div class="con">
			<div class="flex half">
				<div class="box">
					<div class="tit">견적 정보</div>
					<div class="tbl row">
						<table>
							<tr>
								<th>견적번호</th>
								<td>2024-EM-A-01302</td>
							</tr>
							<tr>
								<th>견적일자</th>
								<td>YYYY.MM.DD</td>
							</tr>
							<tr>
								<th>수신</th>
								<td>홍길동</td>
							</tr>
							<tr>
								<th>학교명</th>
								<td>제주중앙고등학교</td>
							</tr>
							<tr>
								<th>전화번호</th>
								<td>010-1234-5678</td>
							</tr>
							<tr>
								<th>이메일</th>
								<td>test1234@naver.com</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="box">
					<div class="tit">공급자 정보</div>
					<div class="tbl row">
						<table>
							<tr>
								<th>등록번호</th>
								<td>119-82-10526</td>
							</tr>
							<tr>
								<th>기관명</th>
								<td>서울대학교 농업생명과학대학 농생명과학공동기기원</td>
							</tr>
							<tr>
								<th>주소</th>
								<td>서울특별시 관악구 관악로1</td>
							</tr>
							<tr>
								<th>담당자</th>
								<td>김학진</td>
							</tr>
							<tr>
								<th>TEL</th>
								<td>02-888-0932</td>
							</tr>
							<tr>
								<th>EMAIL</th>
								<td>test1234@naver.com</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="tit mt">상세내용</div>
			<div class="tbl colm">
				<table>
					<colgroup>
						<col width="60">
						<col width="100">
						<col width="*">
						<col width="80">
						<col width="100">
						<col width="100">
					</colgroup>
					<thead>
						<tr>
							<th>NO.</th>
							<th>교육일</th>
							<th>프로그램명</th>
							<th>교육인원</th>
							<th>단가</th>
							<th>금액</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1</td>
							<td>YYYY.MM.DD</td>
							<td class="tal">프로그램명입니다. 프로그램명입니다. 프로그램명입니다.</td>
							<td>10</td>
							<td>60,000원</td>
							<td>600,000원</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="total_area mt">
				<table>
					<tbody>
						<tr>
							<th>소계</th>
							<td>3,000,000원</td>
						</tr>
						<tr>
							<th>부가세</th>
							<td>300,000원</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th>합계</th>
							<td>3,300,000원</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="print_btm">
				<p>상기 견적의 유효기간은 견적일로 부터 1개월 입니다.</p>
				<div class="date">YYYY년 MM월 DD일</div>
				<div class="copy">서울대학교 농생명과학공동기기원 생명·환경과학교육센터<div class="stamp"><img src="/images/img_stamp.png" alt=""></div></div>
			</div>
			<div class="tbl row etc">
				<table>
					<tbody>
						<tr>
							<th>비고</th>
							<td>비고입니다.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>

</main>

<script>
$(function() {
	setTimeout(function() {
		try {
			var printOpened = true;
			function onFocusAfterPrint() {
				$(window).off('focus', onFocusAfterPrint);
				setTimeout(function() { tryCloseWindow(); }, 100);
			}
			$(window).on('focus', onFocusAfterPrint);
			window.print();
			var fallbackTimer = setTimeout(function() {
				$(window).off('focus', onFocusAfterPrint);
				tryCloseWindow();
			}, 500);
			function tryCloseWindow() {
				clearTimeout(fallbackTimer);
				try {
					window.close();
				} catch (e) {
				}
				try {
					window.open('','_self');
					window.close();
				} catch (e) {
				}
				setTimeout(function() {
					try {
						location.href = 'about:blank';
					} catch (e) {
						document.documentElement.style.display = 'none';
					}
				}, 300);
			}

		} catch (err) {
			console.error('인쇄 또는 창 닫기 시도 중 오류:', err);
		}
	}, 200);
});
</script>


@endsection