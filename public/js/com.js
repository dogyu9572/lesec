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
	$(".btn_menu").click(function(e){
		e.preventDefault();
		$("html,body").stop(false,true).toggleClass("over_h");
		$(".header").stop(false,true).toggleClass("on");
		$(".sitemap").stop(false,true).fadeToggle("fast");
	});
	$(document).on('click', 'a.btn_gray[href="#"]', function(e) {
		e.preventDefault();
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

	$(document).on("click", "[data-layer-open]", function (event) {
		event.preventDefault();
		var targetId = $(this).data("layerOpen");
		if (targetId) {
			window.layerShow(targetId);
		}
	});

	$(document).on("click", "[data-layer-close]", function (event) {
		event.preventDefault();
		var targetId = $(this).data("layerClose");
		if (targetId) {
			window.layerHide(targetId);
		}
	});

	$(document).on("submit", "form.js-confirm-submit", function (event) {
		var message = $(this).data("confirmMessage") || "정말 진행하시겠습니까?";
		if (!window.confirm(message)) {
			event.preventDefault();
		}
	});

	$(document).on("click", ".js-member-logout", function (event) {
		event.preventDefault();
		var formId = $(this).data("logoutForm") || "member-logout-form";
		var form = document.getElementById(formId);
		if (form) {
			form.submit();
		}
	});
});

if (typeof window.layerShow !== "function") {
	window.layerShow = function (id) {
		var target = document.getElementById(id);
		if (!target) {
			return;
		}
		$(target).fadeIn(300, function () {
			$(target).trigger("popup:show");
		});
	};
}

if (typeof window.layerHide !== "function") {
	window.layerHide = function (id) {
		var target = document.getElementById(id);
		if (!target) {
			return;
		}
		$(target).fadeOut(300, function () {
			$(target).trigger("popup:hide");
		});
	};
}

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