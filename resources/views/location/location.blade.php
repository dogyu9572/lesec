@extends('layouts.app')
@section('content')
<main class="pb">

	<div class="location_wrap">
		<div class="map_wrap">
			<div id="daumRoughmapContainer1761096711855" class="root_daum_roughmap root_daum_roughmap_landing"></div>
			<script charset="UTF-8" class="daum_roughmap_loader_script" src="https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js"></script>
			<script charset="UTF-8">
				new daum.roughmap.Lander({
					"timestamp" : "1761096711855",
					"key" : "yewgtasywjg",
					"mapWidth" : "1920",
					"mapHeight" : "400"
				}).render();
			</script>
		</div>
		<div class="inner map_text_wrap">
			<div class="map_text flex">
				<div class="logo"><img src="/images/img_logo.svg" alt=""><span>생명∙환경과학교육센터</span></div>
				<div class="address"><strong>주소</strong><p>서울특별시 관악구 관악로 1 서울대학교 농생명과학공동기기원</p></div>
			</div>

			<div class="moving_guide">
				<div class="box type_car">
					<div class="txt">
						<div class="tit">개인차량 이용안내</div>
						<ul>
							<li class="i1">
								<div class="tt"><i></i>내비게이션 검색</div>
								<p>서울시 관악구 관악로1 서울대학교 75동 ‘서울대학교 75동’, ‘두레미담 서울대1호점’<br/>
									(75동 건물 검색이 안될 경우, 75동 옆건물인 ‘두레미담 서울대1호점’으로 검색해주세요.)
								</p>
							</li>
							<li class="i2">
								<div class="tt"><i></i>문의</div>
								<p>서울대학교 농생명과학공동기기원(02-888-0932)</p>
							</li>
						</ul>
					</div>
					<div class="img"><img src="/images/img_moving_guide01.jpg" alt=""></div>
				</div>
				<div class="box type_public_transport">
					<div class="txt">
						<div class="tit">대중교통 이용안내</div>
						<ul>
							<li class="i1">
								<div class="tt"><i>2</i>서울대입구역</div>
								<p>3번출구에서 5513번 버스 이용 → 농생대 정류장 하차 → 75동 도보이동</p>
							</li>
							<li class="i1">
								<div class="tt"><i>2</i>낙성대입구역</div>
								<p> 4번출구에서 관악02번 버스 이용 → 후사삼거리에서 하차하여 5516, 5511번 버스 이용 <br class="pc_vw">→ 농생대 정류장 하차 → 75동 도보이동</p>
							</li>
							<li class="i1">
								<div class="tt"><i>2</i>신림역</div>
								<p>지하철 2호선 신림역 3번 출구에서 5516번 버스 이용 → 농생대 정류장 하차 → 75동 도보이동</p>
							</li>
						</ul>
					</div>
					<div class="img"><img src="/images/img_moving_guide02.jpg" alt=""></div>
				</div>
			</div>
		</div>
	</div>

</main>
@endsection