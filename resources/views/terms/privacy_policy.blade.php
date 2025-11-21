@extends('layouts.app')
@section('content')
<main class="pb">
    
    <div class="inner terms_area">
        @if(isset($post) && $post->content)
            {!! $post->content !!}
        @else
            
        @endif
    </div>

</main>
@endsection
