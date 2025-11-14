@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="board_top vab">
			<div class="total">TOTAL<strong>{{ number_format($totalCount ?? 0) }}</strong></div>
			<form class="search_wrap" method="get" action="{{ route('board.notice') }}">
				<select name="search_type" id="search_type">
					<option value="title" @if(($filters['search_type'] ?? 'title') === 'title') selected @endif>제목</option>
					<option value="content" @if(($filters['search_type'] ?? '') === 'content') selected @endif>내용</option>
				</select>
				<div class="search_area">
					<input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="검색어를 입력해주세요.">
					<button type="submit" class="btn">검색</button>
				</div>
			</form>
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
					@forelse($posts as $post)
					<tr class="{{ $post->is_notice ? 'notice' : '' }}">
						<td class="num">
							@if($post->is_notice)
							<i></i>
							@else
							{{ $posts->total() - ($posts->currentPage() - 1) * $posts->perPage() - $loop->index }}
							@endif
						</td>
						<td class="tal">
							<a href="{{ route('board.notice.view', $post->id) }}">
								{{ $post->title }}
							</a>
						</td>
						<td class="date">{{ optional($post->created_at)->format('Y.m.d') }}</td>
						<td class="hit">{{ number_format($post->view_count ?? 0) }}</td>
					</tr>
					@empty
					<tr>
						<td colspan="4" class="tal">등록된 게시글이 없습니다.</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="board_bottom">
			<div class="paging">
				@php
					$currentPage = $posts->currentPage();
					$lastPage = $posts->lastPage();
					$start = max(1, $currentPage - 2);
					$end = min($lastPage, $start + 4);
					$start = max(1, $end - 4);
				@endphp
				<a href="{{ $posts->url(1) }}" class="arrow two first">맨끝</a>
				<a href="{{ $posts->previousPageUrl() ?? $posts->url(1) }}" class="arrow one prev">이전</a>
				@for($page = $start; $page <= $end; $page++)
				<a href="{{ $posts->url($page) }}" @if($page === $currentPage) class="on" @endif>{{ $page }}</a>
				@endfor
				<a href="{{ $posts->nextPageUrl() ?? $posts->url($lastPage) }}" class="arrow one next">다음</a>
				<a href="{{ $posts->url($lastPage) }}" class="arrow two last">맨끝</a>
			</div>
		</div> <!-- //board_bottom -->

	</div>

</main>
@endsection