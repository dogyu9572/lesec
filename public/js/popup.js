/**
 * 팝업 레이어 제어 스크립트
 */
(function($) {
    'use strict';

    const PopupManager = {
        init: function() {
            this.applyLayerLayout();
            this.checkCookies();
            this.bindEvents();
        },

        // CSP(style-src) 강화를 위해 인라인 style 대신 JS로 레이어 위치/크기 적용
        applyLayerLayout: function() {
            $('.popup-layer[data-display-type="layer"]').each(function() {
                const width = parseInt($(this).attr('data-popup-width'), 10);
                const top = parseInt($(this).attr('data-popup-top'), 10);
                const left = parseInt($(this).attr('data-popup-left'), 10);

                this.style.setProperty('position', 'absolute', 'important');
                this.style.setProperty('height', 'auto');
                this.style.setProperty('z-index', '99999');

                if (!Number.isNaN(width)) {
                    this.style.setProperty('width', width + 'px');
                }
                if (!Number.isNaN(top)) {
                    this.style.setProperty('top', top + 'px');
                }
                if (!Number.isNaN(left)) {
                    this.style.setProperty('left', left + 'px');
                }
            });
        },

        // 이벤트 바인딩 (이벤트 위임 패턴)
        bindEvents: function() {
            $(document).on('click', '.popup-footer-close-btn', function() {
                const popupId = $(this).data('popup-id');
                PopupManager.closePopup(popupId);
            });

            $(document).on('click', '.popup-today-label', function() {
                const popupId = $(this).data('popup-id');
                PopupManager.setTodayCookie(popupId);
                PopupManager.closePopup(popupId);
            });
        },

        // 팝업 닫기
        closePopup: function(popupId) {
            $('#popup-' + popupId).addClass('hidden');
        },

        // 쿠키 확인 및 숨김 처리 (레이어팝업만)
        checkCookies: function() {
            $('.popup-layer[data-display-type="layer"]').each(function() {
                const popupId = $(this).data('popup-id');
                const cookieName = 'popup_hide_' + popupId;
                
                if (PopupManager.getCookie(cookieName)) {
                    $(this).addClass('hidden');
                }
            });
        },

        // 오늘 하루 보지 않기 쿠키 설정 (자정까지)
        setTodayCookie: function(popupId) {
            const cookieName = 'popup_hide_' + popupId;
            const expires = new Date();
            expires.setHours(23, 59, 59, 999);
            
            document.cookie = cookieName + '=true; expires=' + expires.toUTCString() + '; path=/';
        },

        // 쿠키 가져오기
        getCookie: function(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');
            
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }
    };

    // DOM 로드 완료 후 초기화
    $(document).ready(function() {
        PopupManager.init();
    });

})(jQuery);
