@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="board_top vab">
			<div class="board_tab">
				<a href="{{ route('board.faq', array_merge(request()->except('page', 'category'), ['category' => null])) }}" class="{{ empty($filters['category']) ? 'on' : '' }}">전체</a>
				@foreach($categories as $category)
				<a href="{{ route('board.faq', array_merge(request()->except('page', 'category'), ['category' => $category])) }}" class="{{ ($filters['category'] ?? '') === $category ? 'on' : '' }}">{{ $category }}</a>
				@endforeach
			</div>
			<form class="search_wrap" method="get" action="{{ route('board.faq') }}">
				<select name="search_type" id="search_type">
					<option value="title" @if(($filters['search_type'] ?? 'title') === 'title') selected @endif>제목</option>
					<option value="content" @if(($filters['search_type'] ?? '') === 'content') selected @endif>내용</option>
				</select>
				<div class="search_area">
					<input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="검색어를 입력해주세요.">
					<button type="submit" class="btn">검색</button>
				</div>
				@if(!empty($filters['category']))
					<input type="hidden" name="category" value="{{ $filters['category'] }}">
				@endif
			</form>
		</div>

		<div class="faq_wrap">
			@forelse($posts as $post)
			<dl>
				<dt>
					<button type="button">
						@if(!empty($post->category))
						[{{ $post->category }}]
						@endif
						{{ $post->title }}
						<i></i>
					</button>
				</dt>
				<dd>
					{!! $post->content !!}
					@if(!empty($post->attachments))
					<div class="btns">
						@foreach($post->attachments as $index => $attachment)
						<a href="{{ route('board.faq.attachment', ['postId' => $post->id, 'attachmentIndex' => $index]) }}" class="btn btn_download flex_center">
							{{ $attachment['name'] ?? basename($attachment['path']) }}
						</a>
						@endforeach
					</div>
					@endif
				</dd>
			</dl>
			@empty
			<dl>
				<dt><button type="button">등록된 FAQ가 없습니다.<i></i></button></dt>
				<dd>등록된 FAQ가 없습니다.</dd>
			</dl>
			@endforelse
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

<script>
$(".faq_wrap dt button").click(function(){
	$(this).parent().next("dd").stop(false,true).slideToggle("fast").parent().stop(false,true).toggleClass("on").siblings().removeClass("on").children("dd").slideUp("fast");
});
</script>

@endsection