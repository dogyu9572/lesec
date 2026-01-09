(function() {
    'use strict';

    const SmsVerification = {
        sendUrl: '/member/sms-verification/send',
        verifyUrl: '/member/sms-verification/verify',
        resendCooldown: 60, // 1분
        timerInterval: null,
        remainingSeconds: 0,

        init: function() {
            this.bindEvents();
            this.bindPhoneFormatting();
        },

        bindEvents: function() {
            const self = this;

            // SMS 인증하기 버튼 클릭
            const btnStart = document.getElementById('btnStartSmsVerification');
            if (btnStart) {
                btnStart.addEventListener('click', function() {
                    self.showVerificationForm();
                });
            }

            // 인증번호 발송 버튼
            const btnSend = document.getElementById('btnSendVerificationCode');
            if (btnSend) {
                btnSend.addEventListener('click', function() {
                    self.sendVerificationCode();
                });
            }

            // 인증번호 확인 버튼
            const btnVerify = document.getElementById('btnVerifyCode');
            if (btnVerify) {
                btnVerify.addEventListener('click', function() {
                    self.verifyCode();
                });
            }

            // 재발송 버튼
            const btnResend = document.getElementById('btnResendCode');
            if (btnResend) {
                btnResend.addEventListener('click', function() {
                    self.sendVerificationCode(true);
                });
            }

            // 인증번호 입력 필드에서 Enter 키 처리 및 숫자만 입력
            const codeInput = document.getElementById('verification_code');
            if (codeInput) {
                codeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        self.verifyCode();
                    }
                });
                
                // 숫자만 입력 가능하도록 제한
                codeInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^0-9]/g, '');
                    if (value.length > 6) {
                        value = value.slice(0, 6);
                    }
                    e.target.value = value;
                });
            }

            // 휴대폰 번호 입력 필드에서 Enter 키 처리
            const phoneInput = document.getElementById('phone_number');
            if (phoneInput) {
                phoneInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        self.sendVerificationCode();
                    }
                });
            }
        },

        bindPhoneFormatting: function() {
            const phoneInput = document.getElementById('phone_number');
            if (!phoneInput) return;

            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                
                if (value.length <= 3) {
                    e.target.value = value;
                } else if (value.length <= 7) {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3);
                } else if (value.length <= 11) {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7);
                } else {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
                }
            });
        },

        showVerificationForm: function() {
            const form = document.getElementById('smsVerificationForm');
            const btnStart = document.getElementById('btnStartSmsVerification');
            
            if (form) {
                form.style.display = 'block';
            }
            if (btnStart) {
                btnStart.style.display = 'none';
            }
        },

        sendVerificationCode: function(isResend) {
            const self = this;
            const phoneInput = document.getElementById('phone_number');
            const phoneError = document.getElementById('phoneError');
            const codeInputGroup = document.getElementById('codeInputGroup');
            const btnSend = document.getElementById('btnSendVerificationCode');
            
            if (!phoneInput) return;

            const phone = phoneInput.value.trim();
            
            // 휴대폰 번호 검증
            if (!phone) {
                this.showError('phoneError', '휴대폰 번호를 입력해주세요.');
                return;
            }

            const phoneDigits = phone.replace(/[^0-9]/g, '');
            if (phoneDigits.length !== 11 || !phoneDigits.startsWith('01')) {
                this.showError('phoneError', '올바른 휴대폰 번호 형식이 아닙니다.');
                return;
            }

            this.hideError('phoneError');

            // 버튼 비활성화
            if (btnSend) {
                btnSend.disabled = true;
                btnSend.textContent = '발송 중...';
            }

            // CSRF 토큰 가져오기
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(this.sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 시 인증번호 입력 필드 표시
                    if (codeInputGroup) {
                        codeInputGroup.style.display = 'block';
                    }
                    
                    // 인증번호 입력 필드 포커스
                    const codeInput = document.getElementById('verification_code');
                    if (codeInput) {
                        codeInput.focus();
                    }

                    // 재발송 타이머 시작
                    if (!isResend) {
                        this.startResendTimer();
                    } else {
                        this.startResendTimer(); // 재발송 시에도 타이머 재시작
                    }

                    // 에러 메시지 숨기기
                    this.hideError('codeError');
                    this.hideSuccess('codeSuccess');
                } else {
                    this.showError('phoneError', data.message || '인증번호 발송에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showError('phoneError', '인증번호 발송 중 오류가 발생했습니다.');
            })
            .finally(() => {
                if (btnSend) {
                    btnSend.disabled = false;
                    btnSend.textContent = '인증번호 발송';
                }
            });
        },

        verifyCode: function() {
            const self = this;
            const phoneInput = document.getElementById('phone_number');
            const codeInput = document.getElementById('verification_code');
            const codeError = document.getElementById('codeError');
            const codeSuccess = document.getElementById('codeSuccess');
            const btnVerify = document.getElementById('btnVerifyCode');

            if (!phoneInput || !codeInput) return;

            const phone = phoneInput.value.trim();
            const code = codeInput.value.trim();

            // 입력값 검증
            if (!phone) {
                this.showError('codeError', '휴대폰 번호를 입력해주세요.');
                return;
            }

            if (!code) {
                this.showError('codeError', '인증번호를 입력해주세요.');
                codeInput.focus();
                return;
            }

            if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                this.showError('codeError', '인증번호는 6자리 숫자입니다.');
                codeInput.focus();
                return;
            }

            this.hideError('codeError');
            this.hideSuccess('codeSuccess');

            // 버튼 비활성화
            if (btnVerify) {
                btnVerify.disabled = true;
                btnVerify.textContent = '확인 중...';
            }

            // CSRF 토큰 가져오기
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(this.verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone,
                    code: code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 인증 성공 - 바로 다음 페이지로 이동
                    window.location.href = '/member/register3_b';
                } else {
                    this.showError('codeError', data.message || '인증번호가 일치하지 않습니다.');
                    codeInput.focus();
                    codeInput.select();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showError('codeError', '인증 확인 중 오류가 발생했습니다.');
            })
            .finally(() => {
                if (btnVerify) {
                    btnVerify.disabled = false;
                    btnVerify.textContent = '인증 확인';
                }
            });
        },

        showVerificationComplete: function() {
            const form = document.getElementById('smsVerificationForm');
            const complete = document.getElementById('verificationComplete');
            const codeInputGroup = document.getElementById('codeInputGroup');

            if (form) {
                form.style.display = 'none';
            }
            if (complete) {
                complete.style.display = 'block';
            }
            if (codeInputGroup) {
                codeInputGroup.style.display = 'none';
            }
        },

        startResendTimer: function() {
            const self = this;
            const timerInfo = document.getElementById('resendTimer');
            const btnResend = document.getElementById('btnResendCode');

            this.remainingSeconds = this.resendCooldown;

            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }

            if (timerInfo) {
                timerInfo.style.display = 'block';
            }
            if (btnResend) {
                btnResend.style.display = 'none';
            }

            this.updateTimer();

            this.timerInterval = setInterval(function() {
                self.remainingSeconds--;
                self.updateTimer();

                if (self.remainingSeconds <= 0) {
                    self.stopResendTimer();
                }
            }, 1000);
        },

        updateTimer: function() {
            const timerInfo = document.getElementById('resendTimer');
            if (timerInfo) {
                const minutes = Math.floor(this.remainingSeconds / 60);
                const seconds = this.remainingSeconds % 60;
                timerInfo.textContent = `인증번호 재발송까지 ${minutes}:${String(seconds).padStart(2, '0')} 남았습니다.`;
            }
        },

        stopResendTimer: function() {
            const timerInfo = document.getElementById('resendTimer');
            const btnResend = document.getElementById('btnResendCode');

            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }

            if (timerInfo) {
                timerInfo.style.display = 'none';
            }
            if (btnResend) {
                btnResend.style.display = 'block';
            }

            this.remainingSeconds = 0;
        },

        showError: function(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = message;
                element.style.display = 'block';
            }
        },

        hideError: function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.style.display = 'none';
                element.textContent = '';
            }
        },

        showSuccess: function(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = message;
                element.style.display = 'block';
            }
        },

        hideSuccess: function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.style.display = 'none';
                element.textContent = '';
            }
        }
    };

    // DOM 로드 완료 시 초기화
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SmsVerification.init();
        });
    } else {
        SmsVerification.init();
    }

})();
