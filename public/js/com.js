$(document).ready(function(){
//헤더
	$(window).scroll(function() {
		if ($(window).scrollTop() > 100) {
			$(".header").addClass("fixed");
		} else {
			$(".header").removeClass("fixed");
		}
	});
	$(".header .gnb .menu").mouseover(function(){
		$(".header").stop(false,true).addClass("hover")
	});
	$(".header .gnb .menu").mouseleave(function(){
		$(".header").stop(false,true).removeClass("hover")
	});
	$(".btn_menu").click(function(){
		$("html,body").stop(false,true).toggleClass("over_h");
		$(".header").stop(false,true).toggleClass("on");
		$(".sitemap").stop(false,true).fadeToggle("fast");
	});
	$(".header .gnb .menu button").click(function(){
		$(this).next(".snb").stop(false,true).slideToggle("fast").parent().stop(false,true).toggleClass("open").siblings().removeClass("open").removeClass("on").children(".snb").slideUp("fast");
	});
//footer
	var speed = 500; // 스크롤속도
	$(".gotop").css("cursor", "pointer").click(function(){
		$('body, html').animate({scrollTop:0}, speed);
	});

	$(window).on("scroll resize", function() {
		let windowBottom = $(window).scrollTop() + $(window).height(); // 브라우저 하단 좌표
		let pointTop = $(".footer .point").offset().top; // .point의 상단 좌표

		if (windowBottom >= pointTop) {
			$(".footer").addClass("unfixed");
		} else {
			$(".footer").removeClass("unfixed");
		}
	});

	$(".footer .family dt button").click(function(event){
		$(this).parent().next("dd").stop(false,true).slideToggle("fast").parent().stop(false,true).toggleClass("on").siblings().removeClass("on").children("dd").slideUp("fast");
		event.stopPropagation(); // 이벤트 전파를 막음
	});
	$(document).click(function(event){
		if(!$(event.target).closest('.footer .family').length) {
			$(".footer .family").removeClass("on").children("dd").slideUp("fast");
		}
	});
//aside
	function asideToggle() {
		if ($(window).width() <= 767) {
			// 767 이하: 아코디언 기능 활성화
			$(".aside .btn_aside").off("click").on("click", function(event) {
				$(this).next(".list").stop(false,true).slideToggle("fast")
					.parent().stop(false,true).toggleClass("on")
					.siblings().removeClass("on").children(".list").slideUp("fast");
				event.stopPropagation();
			});

			$(document).off("click.aside").on("click.aside", function(event) {
				if (!$(event.target).closest('.aside').length) {
					$(".aside").removeClass("on").children(".list").slideUp("fast");
				}
			});

		} else {
			// 768 이상: 아코디언 기능 해제
			$(".aside .btn_aside").off("click");
			$(document).off("click.aside");
			$(".aside").removeClass("on").children(".list").removeAttr("style");
		}
	}

	// 페이지 로드 시 실행
	asideToggle();

	// 브라우저 크기 변경 시 자동 실행
	$(window).on("resize", function() {
		asideToggle();
	});

//브라우저 사이즈
	let vh = window.innerHeight * 0.01; 
	document.documentElement.style.setProperty('--vh', `${vh}px`);
//화면 리사이즈시 변경 
	window.addEventListener('resize', () => {
		let vh = window.innerHeight * 0.01; 
		document.documentElement.style.setProperty('--vh', `${vh}px`);
	});
	window.addEventListener('touchend', () => {
		let vh = window.innerHeight * 0.01;
		document.documentElement.style.setProperty('--vh', `${vh}px`);
	});
});

//비밀번호 암호화 해제
document.addEventListener('DOMContentLoaded', function () {
    const toggleEyeBtn = document.querySelector('.toggle-password');
    const passwordInput = document.querySelector('.password-input');

    if (toggleEyeBtn && passwordInput) {
        toggleEyeBtn.addEventListener('click', function () {
            passwordInput.type = (passwordInput.type === 'password') ? 'text' : 'password';
        });
    }

    document.querySelectorAll('.clear-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const parent = btn.closest('.password-wrap');
            const input = parent.querySelector('input[type="text"], input[type="password"]');

            if (input) {
                input.value = '';
                input.focus();
            }
        });
    });
});