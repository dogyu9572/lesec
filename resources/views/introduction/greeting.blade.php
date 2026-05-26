@extends('layouts.app')
@section('content')
<main>

	<div class="greeting_wrap wrap_pb">
		<div class="inner editor-content">
			@if(isset($post))
				{!! $post->content ?? '' !!}				
			@endif
		</div>
	</div>

</main>
@endsection
