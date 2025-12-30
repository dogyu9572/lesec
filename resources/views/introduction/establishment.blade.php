@extends('layouts.app')
@section('content')
<main class="pb">

	<div class="inner">
		<ul class="establishment_wrap">
			@if(isset($blocks) && count($blocks) > 0)
				@foreach($blocks as $index => $block)
					<li class="c{{ $index + 1 }}">
						<i></i>
						<div class="tit">{{ $block['title'] }}</div>
						<p>{!! $block['content'] !!}</p>
					</li>
				@endforeach
			@else
				<li class="c1"><i></i><div class="tit">인재 양성 및 <br class="pc_vw">국가 경쟁력 강화</div><p>첨단기기를 이용한 생명·환경과학 <br class="pc_vw">교육의 기회를 제공하여 과학기술의 <br class="pc_vw">인재 양성 및 진로 지도에 기여하고 <br class="pc_vw">국가 경쟁력을 함양시킬 수 있습니다.</p></li>
				<li class="c2"><i></i><div class="tit">실험·실습 통한 흥미 유발</div><p>이론은 물론 실험, 실습을 통해 어렵게만 느낀 생명공학 · 환경과학이 친근하게 다가오는 계기를 마련하고 참가 학생들에게 이공계에 대한 관심을 고취시킬 수 있습니다.</p></li>
				<li class="c3"><i></i><div class="tit">기관의 긍정적 이미지 구축</div><p>생명·환경과학교육센터의 참가를 통해 서울대학교와 농업생명과학대학 <br class="pc_vw">NICEM의 긍정적인 이미지를 <br class="pc_vw">구축할 수 있습니다.</p></li>
				<li class="c4"><i></i><div class="tit">과학기술의 이해 증진 및 <br class="pc_vw">대중화</div><p>과학기술에 대한 이해를 높이고 <br class="pc_vw">대중화를 촉진하여 <br class="pc_vw">과학적 소양을 확산시킵니다.</p></li>
			@endif
		</ul>
	</div>

</main>
@endsection