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
		
			<div class="stit mb0">본인 인증</div>
			<p class="stb">본인 확인을 위해 <strong class="c_blue">휴대전화 또는 문자 인증</strong>을 선택해 진행해 주세요.<br/>안전한 회원가입을 위해 인증 완료 후 다음 단계로 이동합니다.</p>
			<div class="mem_half mem_half_center">				
				<div class="phone_certification sms" id="smsVerificationBox">
					<strong>SMS 인증</strong>
					<p>휴대전화 번호로 발송된 인증번호를 입력하여 <br/> 본인 확인을 진행할 수 있습니다.</p>
					
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
@endpush

@endsection