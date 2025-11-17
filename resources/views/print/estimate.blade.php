@extends('layouts.app')
@section('content')
@php
    // 기존 호환성: $estimate가 있으면 배열로 변환
    if (isset($estimate) && !isset($estimates)) {
        $estimates = [$estimate];
    } elseif (!isset($estimates)) {
        $estimates = [];
    }
@endphp
<main>
    @foreach($estimates as $index => $estimate)
	<div class="estimate_wrap" @if($index > 0) style="page-break-before: always;" @endif>

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
								<td>{{ $estimate['number'] ?? '-' }}</td>
							</tr>
							<tr>
								<th>견적일자</th>
								<td>{{ $estimate['date'] ?? 'YYYY.MM.DD' }}</td>
							</tr>
							<tr>
								<th>수신</th>
								<td>{{ $estimate['recipient_name'] ?? '-' }}</td>
							</tr>
							<tr>
								<th>학교명</th>
								<td>{{ $estimate['school_name'] ?? '-' }}</td>
							</tr>
							<tr>
								<th>전화번호</th>
								<td>{{ $estimate['phone'] ?? '-' }}</td>
							</tr>
							<tr>
								<th>이메일</th>
								<td>{{ $estimate['email'] ?? '-' }}</td>
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
						@forelse(($estimate['items'] ?? []) as $row)
						<tr>
							<td>{{ $row['no'] }}</td>
							<td>{{ $row['education_date'] }}</td>
							<td class="tal">{{ $row['program_name'] }}</td>
							<td>{{ number_format($row['count']) }}</td>
							<td>{{ number_format($row['unit_price']) }}원</td>
							<td>{{ number_format($row['amount']) }}원</td>
						</tr>
						@empty
						<tr>
							<td colspan="6">표시할 내역이 없습니다.</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="total_area mt">
				<table>
					<tbody>
						<tr>
							<th>소계</th>
							<td>{{ isset($estimate['subtotal']) ? number_format($estimate['subtotal']) . '원' : '0원' }}</td>
						</tr>
						<tr>
							<th>부가세</th>
							<td>{{ isset($estimate['vat']) ? number_format($estimate['vat']) . '원' : '0원' }}</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th>합계</th>
							<td>{{ isset($estimate['total']) ? number_format($estimate['total']) . '원' : '0원' }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="print_btm">
				<p>상기 견적의 유효기간은 견적일로 부터 1개월 입니다.</p>
				<div class="date">{{ $estimate['print_date'] ?? '' }}</div>
				<div class="copy">서울대학교 농생명과학공동기기원 생명·환경과학교육센터<div class="stamp"><img src="/images/img_stamp.png" alt=""></div></div>
			</div>
			<div class="tbl row etc">
				<table>
					<tbody>
						<tr>
							<th>비고</th>
							<td>{{ $estimate['note'] ?? '' }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
    @endforeach

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