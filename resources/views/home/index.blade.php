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
					<a href="#this" class="i1"><span class="tt">중등학기 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
					<a href="#this" class="i2"><span class="tt">중등방학 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
				</dd>
			</dl>
			<dl class="c2">
				<dt>고등<button type="button" class="mo_vw">열기</button></dt>
				<dd>
					<a href="#this" class="i1"><span class="tt">고등학기 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
					<a href="#this" class="i2"><span class="tt">고등방학 신청<i></i></span><p>클릭 후 해당 페이지에서 <br/>신청할 수 있습니다.</p></a>
				</dd>
			</dl>
		</div>
	</div>

	<div class="main_board">
		<div class="inner">
			<div class="box">
				<div class="tit">공지사항 <a href="" class="more">View more</a></div>
				<div class="list">
					<a href="">[중학생 학기 프로그램] 인증서 받는 방법<span>2025.07.19</span></a>
					<a href="">4월 응급처치교육/워크숍 안내<span>2025.07.19</span></a>
					<a href="">10월 15일 개교기념일 휴무<span>2025.07.19</span></a>
					<a href="">생명환경과학교육센터 이전 및 문의처 안내<span>2025.07.19</span></a>
				</div>
			</div>
			<div class="box">
				<div class="tit">자료실  <a href="" class="more">View more</a></div>
				<div class="list">
					<a href="">2025.8.15.-8.16. 참가 / Mid2. CSI 속 과학이야기(스누베어)<span>2025.07.19</span></a>
					<a href="">2025.8.15.-8.16. 참가 / Mid2. CSI 속 과학이야기<span>2025.07.19</span></a>
					<a href="">2025.8.15.-8.16. 참가 / M3. Blood 관찰 및 DNA 추출<span>2025.07.19</span></a>
					<a href="">2025.8.15.-8.16. 참가 / P2. Plasmid DNA와 제한효소(608호)<span>2025.07.19</span></a>
				</div>
			</div>
			<div class="box main_faq">
				<div class="tit">자주 묻는 질문  <a href="" class="more">View more</a></div>
				<div class="list">
					<a href=""><i>신청/입금</i>신청/입금 확인은 어떻게 하나요?<span>2025.07.19</span></a>
					<a href=""><i>신청/입금</i>입금자명을 "학생이름+학교"(6자)로 하지 않았어...<span>2025.07.19</span></a>
					<a href=""><i>수료증</i>재발급<span>2025.07.19</span></a>
					<a href=""><i>신청/입금</i>단체 신청의 기준인원은 몇명인가요?<span>2025.07.19</span></a>
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