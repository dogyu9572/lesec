<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<title>@yield('title', '서울대학교 농생명과학공동기기원 생명·환경과학교육센터')</title>
	<meta charset="utf-8">
	<meta name="robots" content="follow">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="subject" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta name="title" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta name="description" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta name="keywords" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta name="copyright" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta property="og:title" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta property="og:subject" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta property="og:description" content="서울대학교 농생명과학공동기기원 생명·환경과학교육센터" />
	<meta property="og:image" content="/images/og_image.jpg" />
	<meta name="author" content="http://">
	<link rel="canonical" href="http://" />
	<meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes">

	<link rel="icon" href="/images/favicon.ico" type="image/x-icon"/>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('/css/popup.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/font.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/styles.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/reactive.css') }}">
    @yield('styles')
    
    <!-- jQuery -->
    <script src="//code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="/js/com.js"></script>
</head>
<body>
	@if(isset($gNum) && $gNum !== 'print')
    <header class="header">
		<div class="bg"><div class="inner"></div></div>
		<div class="inner">
			<a href="/" class="logo"><img src="/images/logo.svg" alt="logo"><h1>서울대학교 농생명과학공동기기원 생명·환경과학교육센터</h1></a>
			<a href="javascript:void(0);" class="btn_menu">
				<p class="t"></p>
				<p class="m"></p>
				<p class="b"></p>
			</a>
			@php
				$isMemberLoggedIn = Auth::guard('member')->check();
			@endphp
			<nav class="gnb">
				<div class="mo_vw member flex_center">
					@if ($isMemberLoggedIn)
					<a href="{{ route('member.logout') }}" class="login" onclick="event.preventDefault();document.getElementById('member-logout-form').submit();">로그아웃</a>
					@else
					<a href="{{ route('member.login') }}" class="login">로그인</a>
					<a href="{{ route('member.register') }}" class="register">회원가입</a>
					@endif
				</div>
				<div class="flex">
					<div class="menu {{ $gNum == '01' ? 'on' : '' }}">
						<a href="/program/middle_semester" class="pc_vw"><span>프로그램</span></a>
						<button type="button" class="mo_vw">프로그램<i></i></button>
						<div class="snb">
							<a href="/program/middle_semester" class="{{ ($gNum == '01' && $sNum == '01') ? 'on' : '' }}">중등학기</a>
							<a href="/program/middle_vacation" class="{{ ($gNum == '01' && $sNum == '02') ? 'on' : '' }}">중등방학</a>
							<a href="/program/high_semester" class="{{ ($gNum == '01' && $sNum == '03') ? 'on' : '' }}">고등학기</a>
							<a href="/program/high_vacation" class="{{ ($gNum == '01' && $sNum == '04') ? 'on' : '' }}">고등방학</a>
							<a href="/program/special" class="{{ ($gNum == '01' && $sNum == '05') ? 'on' : '' }}">특별프로그램</a>
						</div>
					</div>
					<div class="menu {{ $gNum == '02' ? 'on' : '' }}">
						<a href="/board/notice" class="pc_vw"><span>게시판</span></a>
						<button type="button" class="mo_vw">게시판<i></i></button>
						<div class="snb">
							<a href="/board/notice" class="{{ ($gNum == '02' && $sNum == '01') ? 'on' : '' }}">공지사항</a>
							<a href="/board/faq" class="{{ ($gNum == '02' && $sNum == '02') ? 'on' : '' }}">FAQ</a>
							<a href="/board/dataroom" class="{{ ($gNum == '02' && $sNum == '03') ? 'on' : '' }}">자료실</a>
						</div>
					</div>
					<div class="menu {{ $gNum == '03' ? 'on' : '' }}">
						<a href="/mypage/member" class="pc_vw"><span>마이페이지</span></a>
						<button type="button" class="mo_vw">마이페이지<i></i></button>
						<div class="snb">
							<a href="/mypage/member" class="{{ ($gNum == '03' && $sNum == '01') ? 'on' : '' }}">회원정보</a>
							@php
								$currentMember = Auth::guard('member')->user();
								$isTeacher = $currentMember && $currentMember->member_type === 'teacher';
								$applicationListUrl = $isTeacher ? '/mypage/application_list' : '/mypage/application_indi_list';
								$isApplicationPage = ($gNum == '03' && $sNum == '02') && (
									request()->routeIs('mypage.application_list')
									|| request()->routeIs('mypage.application_indi_list')
									|| request()->routeIs('mypage.application_indi_view')
									|| request()->routeIs('mypage.application_view')
									|| request()->routeIs('mypage.application_write')
								);
							@endphp
							<a href="{{ $applicationListUrl }}" class="{{ $isApplicationPage ? 'on' : '' }}">신청내역</a>
						</div>
					</div>
					<div class="menu {{ $gNum == '04' ? 'on' : '' }}">
						<a href="/introduction/greeting" class="pc_vw"><span>센터소개</span></a>
						<button type="button" class="mo_vw">센터소개<i></i></button>
						<div class="snb">
							<a href="/introduction/greeting" class="{{ ($gNum == '04' && $sNum == '01') ? 'on' : '' }}">인사말</a>
							<a href="/introduction/establishment" class="{{ ($gNum == '04' && $sNum == '02') ? 'on' : '' }}">설립목적</a>
							<a href="/introduction/contact" class="{{ ($gNum == '04' && $sNum == '03') ? 'on' : '' }}">연락처</a>
						</div>
					</div>
					<div class="menu {{ $gNum == '05' ? 'on' : '' }}">
						<a href="/location/location" class="pc_vw"><span>위치안내</span></a>
						<button type="button" class="mo_vw">위치안내<i></i></button>
						<div class="snb">
							<a href="/location/location" class="{{ ($gNum == '05' && $sNum == '01') ? 'on' : '' }}">오시는 길</a>
							<a href="/location/classroom" class="{{ ($gNum == '05' && $sNum == '02') ? 'on' : '' }}">강의실 안내</a>
							<a href="/location/parking" class="{{ ($gNum == '05' && $sNum == '03') ? 'on' : '' }}">주차 안내</a>
						</div>
					</div>
				</div>
			</nav>
			<nav class="member flex_center pc_vw">
				@if ($isMemberLoggedIn)
				<a href="{{ route('member.logout') }}" class="login" onclick="event.preventDefault();document.getElementById('member-logout-form').submit();">로그아웃</a>
				@else
				<a href="{{ route('member.login') }}" class="login">로그인</a>
				<a href="{{ route('member.register') }}" class="register">회원가입</a>
				@endif
			</nav>
			@if ($isMemberLoggedIn)
			<form id="member-logout-form" action="{{ route('member.logout') }}" method="POST" style="display:none;">
				@csrf
			</form>
			@endif
		</div>
    </header>
	@endif

	<div class="container @if(isset($gNum) && $gNum == 'print') print_wrap pt0 @endif @if(isset($gNum) && $gNum == 'terms') terms_wrap @endif">
		@if(isset($gNum) && $gNum !== 'main' && $gNum !== '00' && $gNum !== 'print')
		<div class="svisual g{{$gNum}}"><div class="inner"><strong>{{ !empty($sName) ? $sName : $gName }}</strong><div class="location"><i></i><em></em><span>{{$gName}}</span>@if(!empty($sName))<em></em><span>{{$sName}}</span>@endif</div></div></div>

		@if(isset($gNum) && $gNum !== 'terms')
		<aside class="aside inner">
			<button type="button" class="btn_aside">{{ $sName }}</button>
			<div class="list">
			@if($gNum == "01")
				<a href="/program/middle_semester" class="{{ ($sNum == '01') ? 'on' : '' }}">중등학기</a>
				<a href="/program/middle_vacation" class="{{ ($sNum == '02') ? 'on' : '' }}">중등방학</a>
				<a href="/program/high_semester" class="{{ ($sNum == '03') ? 'on' : '' }}">고등학기</a>
				<a href="/program/high_vacation" class="{{ ($sNum == '04') ? 'on' : '' }}">고등방학</a>
				<a href="/program/special" class="{{ ($sNum == '05') ? 'on' : '' }}">특별프로그램</a>
			@elseif($gNum == "02")
				<a href="/board/notice" class="{{ ($sNum == '01') ? 'on' : '' }}">공지사항</a>
				<a href="/board/faq" class="{{ ($sNum == '02') ? 'on' : '' }}">FAQ</a>
				<a href="/board/dataroom" class="{{ ($sNum == '03') ? 'on' : '' }}">자료실</a>
			@elseif($gNum == "03")
				<a href="/mypage/member" class="{{ ($sNum == '01') ? 'on' : '' }}">회원정보</a>
				@php
					$currentMember = Auth::guard('member')->user();
					$isTeacher = $currentMember && $currentMember->member_type === 'teacher';
					$applicationListUrl = $isTeacher ? '/mypage/application_list' : '/mypage/application_indi_list';
					$isApplicationPage = ($sNum == '02') && (
						request()->routeIs('mypage.application_list')
						|| request()->routeIs('mypage.application_indi_list')
						|| request()->routeIs('mypage.application_indi_view')
						|| request()->routeIs('mypage.application_view')
						|| request()->routeIs('mypage.application_write')
					);
				@endphp
				<a href="{{ $applicationListUrl }}" class="{{ $isApplicationPage ? 'on' : '' }}">신청내역</a>
			@elseif($gNum == "04")
				<a href="/introduction/greeting" class="{{ ($sNum == '01') ? 'on' : '' }}">인사말</a>
				<a href="/introduction/establishment" class="{{ ($sNum == '02') ? 'on' : '' }}">설립목적</a>
				<a href="/introduction/contact" class="{{ ($sNum == '03') ? 'on' : '' }}">연락처</a>
			@elseif($gNum == "05")
				<a href="/location/location" class="{{ ($sNum == '01') ? 'on' : '' }}">오시는 길</a>
				<a href="/location/classroom" class="{{ ($sNum == '02') ? 'on' : '' }}">강의실 안내</a>
				<a href="/location/parking" class="{{ ($sNum == '03') ? 'on' : '' }}">주차 안내</a>
			@endif
			</div>
		</aside>
		@endif
		@endif

        @yield('content')
    </div>

	@if(isset($gNum) && $gNum !== 'print')
	<footer class="footer">
		<div class="point" id="foot"></div>
		<div class="quick main">
			<a href="/board/faq.php" class="qna">Q&amp;A</a>
			<button type="button" class="gotop">TOP</button>
		</div>
		<div class="inner">
			<div class="info">
				<div class="logo"></div>
				<div class="txt">
					<ul class="contact">
						<li><strong>Tel</strong>02-888-0932/0933, 02-880-4948</li>
						<li><strong>E-mail</strong>nicemedu@snu.ac.kr</li>
					</ul>
					<ul class="address">
						<li class="w100p">농생명과학공동기기원 생명•환경과학교육센터 75동 606호</li>
						<li class="impt"><strong>원장</strong>김학진</li>
						<li class="impt"><strong>고유번호</strong>119-82-10526</li>
						<li class="copy">COPYRIGHT (C) 2025, 농생명과학공동기기원. ALL RIGHTS RESERVED.</li>
					</ul>
					<div class="links">
						<a href="{{ route('terms.privacy_policy') }}" class="link">개인정보처리방침</a>
						<a href="{{ route('terms.no_email_collection') }}" class="link">이메일무단수집거부</a>
					</div>
				</div>
			</div>
			<a href="https://blog.naver.com/nicemedu" target="_blank" class="outlink">교육센터 블로그</a>
		</div>
	</footer>

    <!-- 팝업 영역 -->
    @yield('popups')
    
    <!-- 스크립트 -->
    <script src="{{ asset('js/popup.js') }}"></script>
    @stack('scripts')
	@endif
</body>
</html>
