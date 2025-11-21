@extends('layouts.app')

@section('content')
<main>
    
	<div class="main_visual">
		<div class="inner">
			<div class="tit"><span><i>청</i><i>소</i><i>년</i>을 위한</span> <strong>생명·환경과학 체험학습</strong></div>
			<p>생명환경과학교육센터는 농업·환경·생명과학 분야의 전문 지식과 연구 장비 체험을 제공하며 <br/>청소년의 과학 이해와 진로 탐색을 돕고 연구지원 체계 향상을 위해 노력합니다.</p>
		</div>
	</div>

	<div class="main_type_link">
		<div class="inner">
			<dl class="c1 on">
				<dt>중등<button type="button" class="mo_vw">열기</button></dt>
				<dd>
					<a href="{{ route('program.show', 'middle_semester') }}" class="i1"><span class="tt">중등학기 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
					<a href="{{ route('program.show', 'middle_vacation') }}" class="i2"><span class="tt">중등방학 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
				</dd>
			</dl>
			<dl class="c2">
				<dt>고등<button type="button" class="mo_vw">열기</button></dt>
				<dd>
					<a href="{{ route('program.show', 'high_semester') }}" class="i1"><span class="tt">고등학기 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
					<a href="{{ route('program.show', 'high_vacation') }}" class="i2"><span class="tt">고등방학 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
				</dd>
			</dl>
		</div>
	</div>

	<div class="main_board">
		<div class="inner">
			<div class="box">
				<div class="tit">공지사항 <a href="{{ route('board.notice') }}" class="more">View more</a></div>
				<div class="list">
					@forelse($noticePosts as $post)
						<a href="{{ $post->url }}">
							{{ $post->title }}
							<span>{{ $post->created_at?->format('Y.m.d') }}</span>
						</a>
					@empty
						<div class="text-muted">등록된 공지사항이 없습니다.</div>
					@endforelse
				</div>
			</div>
			<div class="box">
				<div class="tit">자료실  <a href="{{ route('board.dataroom') }}" class="more">View more</a></div>
				<div class="list">
					@forelse($dataRoomPosts as $post)
						<a href="{{ $post->url }}">
							{{ $post->title }}
							<span>{{ $post->created_at?->format('Y.m.d') }}</span>
						</a>
					@empty
						<div class="text-muted">등록된 자료가 없습니다.</div>
					@endforelse
				</div>
			</div>
			<div class="box main_faq">
				<div class="tit">자주 묻는 질문  <a href="{{ route('board.faq') }}" class="more">View more</a></div>
				<div class="list">
					@forelse($faqPosts as $post)
						<a href="{{ $post->url }}">
							@if($post->category)
								<i>{{ $post->category }}</i>
							@endif
							{{ $post->title }}
							<span>{{ $post->created_at?->format('Y.m.d') }}</span>
						</a>
					@empty
						<div class="text-muted">등록된 FAQ가 없습니다.</div>
					@endforelse
				</div>
			</div>
		</div>
	</div>

</main>

<script>
$(".main_type_link dt button").click(function(){
	$(this).parent().parent().addClass("on").siblings().removeClass("on");
});
</script>

@endsection

@section('popups')
@if($popups->count() > 0)
    @foreach($popups as $popup)
        @if($popup->popup_display_type === 'normal')
            {{-- 일반팝업 (새창) --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const popupUrl = '{{ route("popup.show", $popup->id) }}';
                    const popupFeatures = 'width={{ $popup->width }},height={{ $popup->height }},left={{ $popup->position_left ?? 100 }},top={{ $popup->position_top ?? 100 }},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no';
                    window.open(popupUrl, 'popup_{{ $popup->id }}', popupFeatures);
                });
            </script>
        @else
            {{-- 레이어팝업 (오버레이) --}}
            <div class="popup-layer popup-fixed" 
                 id="popup-{{ $popup->id }}"
                 data-popup-id="{{ $popup->id }}"
                 data-display-type="layer"
                 style="position: absolute !important; width: {{ $popup->width }}px; height: auto; top: {{ $popup->position_top }}px; left: {{ $popup->position_left }}px; z-index: 99999;">
                
                <div class="popup-body">
                    @if($popup->popup_type === 'image' && $popup->popup_image)
                        @if($popup->url)
                            <a href="{{ $popup->url }}" target="{{ $popup->url_target }}">
                                <img src="{{ asset('storage/' . $popup->popup_image) }}" alt="{{ $popup->title }}">
                            </a>
                        @else
                            <img src="{{ asset('storage/' . $popup->popup_image) }}" alt="{{ $popup->title }}">
                        @endif
                    @elseif($popup->popup_type === 'html' && $popup->popup_content)
                        {!! $popup->popup_content !!}
                    @endif
                </div>
                
                <div class="popup-footer">
                    <label class="popup-today-label" data-popup-id="{{ $popup->id }}">
                        <input type="checkbox" class="popup-today-close" data-popup-id="{{ $popup->id }}">
                        1일 동안 보지 않음
                    </label>
                    <button type="button" class="popup-footer-close-btn" data-popup-id="{{ $popup->id }}">닫기</button>
                </div>
            </div>
        @endif
    @endforeach
@endif
@endsection