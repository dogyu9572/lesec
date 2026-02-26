@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap join_wrap">
			@if ($errors->has('process'))
			<p class="error_alert">{{ $errors->first('process') }}</p>
			@endif
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 onf"><i></i><p>회원 구분</p></li>
				<li class="i2 on"><i></i><p>본인 인증</p></li>
				<li class="i3"><i></i><p>회원정보 입력</p></li>
				<li class="i4"><i></i><p>회원가입 완료</p></li>
			</ol>
		
			<div class="stit mb0">보호자 인증</div>
			<p class="stb">보호자 확인을 위해 <strong class="c_blue">SMS 인증</strong>이 진행됩니다.<br/>안전한 회원가입을 위해 인증 완료 후 다음 단계로 이동합니다.</p>
			<div class="mem_half mem_half_center">				
				<div class="phone_certification sms" id="smsVerificationBox">
					<strong>SMS 인증</strong>
					<p>인증번호 수신까지<br/>30초 정도 시간이 걸릴 수 있습니다.</p>
					
					<button type="button" class="btn btn_wkk" id="btnStartSmsVerification">SMS 인증하기</button>
					
					<div class="sms-verification-form" id="smsVerificationForm" style="display: none;">
							<div class="verification-input-group">
								<label for="phone_number" class="verification-label">휴대폰 번호</label>
								<div class="flex inbtn">
									<input type="text" id="phone_number" name="phone" class="text w100p" placeholder="010-1234-5678" data-phone-input inputmode="tel" autocomplete="tel">
									<button type="button" class="btn btn_wkk" id="btnSendVerificationCode">인증번호 발송</button>
								</div>
								<p class="error-message" id="phoneError" style="display: none; color: #dc3545; font-size: 14px; margin-top: 5px;"></p>
							</div>
							
							<div class="verification-input-group" id="codeInputGroup" style="display: none;">
								<label for="verification_code" class="verification-label">인증번호</label>
								<div class="flex inbtn">
									<input type="text" id="verification_code" name="code" class="text w100p" placeholder="6자리 숫자 입력" maxlength="6" inputmode="numeric" pattern="[0-9]*">
									<button type="button" class="btn btn_wkk" id="btnVerifyCode">인증 확인</button>
								</div>
								<div class="verification-info">
									<p class="error-message" id="codeError" style="display: none; color: #dc3545; font-size: 14px; margin-top: 5px;"></p>
									<p class="success-message" id="codeSuccess" style="display: none; color: #28a745; font-size: 14px; margin-top: 5px;"></p>
									<p class="timer-info" id="resendTimer" style="display: none; color: #666; font-size: 13px; margin-top: 5px;"></p>
									<button type="button" class="btn-link" id="btnResendCode" style="display: none; color: #007bff; background: none; border: none; cursor: pointer; font-size: 13px; margin-top: 5px; text-decoration: underline;">인증번호 재발송</button>
								</div>
							</div>
							
						</div>
				</div>
			</div>
		</div>
	</div>

</main>

@push('scripts')
<script src="{{ asset('js/member/sms-verification.js') }}"></script>
<script>
// 14세 미만은 register3_a로 이동
(function() {
    const originalVerifyCode = window.SmsVerification?.verifyCode;
    if (originalVerifyCode) {
        window.SmsVerification.verifyCode = function() {
            const self = this;
            const phoneInput = document.getElementById('phone_number');
            const codeInput = document.getElementById('verification_code');
            const codeError = document.getElementById('codeError');
            const codeSuccess = document.getElementById('codeSuccess');
            const btnVerify = document.getElementById('btnVerifyCode');

            if (!phoneInput || !codeInput) return;

            const phone = phoneInput.value.trim();
            const code = codeInput.value.trim();

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

            if (btnVerify) {
                btnVerify.disabled = true;
                btnVerify.textContent = '확인 중...';
            }

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
                    // 인증 성공 - 14세 미만은 register3_a로 이동
                    window.location.href = '/member/register3_a';
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
        };
    }
})();
</script>
@endpush

@endsection