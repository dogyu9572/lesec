@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="board_top vab">
			<div class="total">TOTAL<strong>100</strong></div>
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

		<div class="board_list mo_break_tbl">
			<table>
				<colgroup>
					<col class="w8_3"/>
					<col/>
					<col class="w11_1"/>
					<col class="w11_1"/>
				</colgroup>
				<thead>
					<tr>
						<th>NO.</th>
						<th>제목</th>
						<th>작성일</th>
						<th>조회수</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="num">5</td>
						<td class="tal"><a href="/board/dataroom_view">제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.</a></td>
						<td class="date">2025.03.10</td>
						<td class="hit">1234</td>
					</tr>
					<tr>
						<td class="num">4</td>
						<td class="tal"><a href="/board/dataroom_view">제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.</a></td>
						<td class="date">2025.03.10</td>
						<td class="hit">1234</td>
					</tr>
					<tr>
						<td class="num">3</td>
						<td class="tal"><a href="/board/dataroom_view">제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.</a></td>
						<td class="date">2025.03.10</td>
						<td class="hit">1234</td>
					</tr>
					<tr>
						<td class="num">2</td>
						<td class="tal"><a href="/board/dataroom_view">제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.</a></td>
						<td class="date">2025.03.10</td>
						<td class="hit">1234</td>
					</tr>
					<tr>
						<td class="num">1</td>
						<td class="tal"><a href="/board/dataroom_view">제목입니다. 제목입니다. 제목입니다. 제목입니다. 제목입니다.</a></td>
						<td class="date">2025.03.10</td>
						<td class="hit">1234</td>
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
@endsection