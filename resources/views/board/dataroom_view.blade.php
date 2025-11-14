@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="board_view">
			<div class="tit"><strong>{{ $post->title }}</strong>
				<div class="info flex">
					<dl class="day">
						<dt>등록일</dt>
						<dd>{{ optional($post->created_at)->format('Y.m.d') }}</dd>
					</dl>
				</div>
			</div>
			<div class="con">
				{!! $post->content !!}
			</div>
			@if(!empty($post->attachments))
			<div class="files">
				@foreach($post->attachments as $index => $attachment)
				<a href="{{ route('board.dataroom.attachment', ['postId' => $post->id, 'attachmentIndex' => $index]) }}">
					{{ $attachment['name'] ?? basename($attachment['path']) }}
				</a>
				@endforeach
			</div>
			@endif
			<div class="btm">
				<a href="{{ $previousPost ? route('board.dataroom.view', $previousPost->id) : '#this' }}" class="arrow prev">
					<strong>이전글</strong>{{ $previousPost->title ?? '이전 글이 없습니다.' }}
				</a>
				<a href="{{ $nextPost ? route('board.dataroom.view', $nextPost->id) : '#this' }}" class="arrow next">
					<strong>다음글</strong>{{ $nextPost->title ?? '다음 글이 없습니다.' }}
				</a>
				<a href="{{ route('board.dataroom') }}" class="btn_list">목록</a>
			</div>
		</div>
		
	</div>

</main>
@endsection