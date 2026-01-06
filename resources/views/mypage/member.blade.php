@extends('layouts.app')
@section('content')
<main>

	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="stit num mb0 nbd_b"><span>1</span>기본 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
			
			@if ($errors->has('auth'))
			<div class="alert alert-error mb16">
				{{ $errors->first('auth') }}
			</div>
			@endif

			<form method="POST" action="{{ route('mypage.member.update') }}" class="js-member-register" data-duplicate-url="{{ route('member.register.check.duplicate') }}" data-school-search-url="{{ route('member.schools.search') }}" data-school-modal="#pop_school">
				@csrf

				<div class="inputs">
					<dl>
						<dt>아이디<span>*</span></dt>
						<dd>
							<input type="text" value="{{ $member->login_id }}" readonly class="text w100p">
						</dd>
					</dl>
					<dl class="password-fields" style="display:none;">
						<dt>현재 비밀번호<span>*</span></dt>
						<dd>
							<input type="password" name="current_password" class="text w100p" placeholder="안전한 정보 수정을 위해 현재 사용 중인 비밀번호를 입력해주세요.">
							@error('current_password')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl class="password-fields" style="display:none;">
						<dt>새 비밀번호</dt>
						<dd>
							<input type="password" name="password" class="text w100p now_pw" placeholder="변경할 비밀번호를 입력해주세요. (변경하지 않으려면 비워두세요)">
							@error('password')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl class="password-fields" style="display:none;">
						<dt>새 비밀번호 확인</dt>
						<dd>
							<input type="password" name="password_confirmation" class="text w100p now_pw_check" placeholder="입력한 비밀번호를 다시 한번 입력해 주세요.">
							<p class="error_alert" style="display:none;">* 동일한 비밀번호가 아닙니다.</p>
						</dd>
					</dl>
					<dl>
						<dt>이름<span>*</span></dt>
						<dd>
							<input type="text" value="{{ $member->name }}" readonly class="text w100p">
						</dd>
					</dl>
					<dl>
						<dt>생년월일<span>*</span></dt>
						<dd>
							<input type="text" value="{{ $member->birth_date ? $member->birth_date->format('Y.m.d') : '' }}" readonly class="text w100p">
						</dd>
					</dl>
					<dl>
						<dt>성별<span>*</span></dt>
						<dd>
							<div class="flex radios">
								<label class="radio"><input type="radio" name="gender" value="male" @checked($member->gender === 'male') disabled><i></i>남자</label>
								<label class="radio"><input type="radio" name="gender" value="female" @checked($member->gender === 'female') disabled><i></i>여자</label>
							</div>
						</dd>
					</dl>
					@if ($member->member_type === 'student' && $member->parent_contact)
					<dl>
						<dt>학생 연락처<span>*</span></dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" name="contact" value="{{ old('contact', $member->formatted_contact ?? $member->contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel" class="js-contact-input" data-original-contact="{{ preg_replace('/[^0-9]/', '', $member->contact ?? '') }}">
								<button type="button" class="btn btn_wkk btn_error js-duplicate-check" data-field="contact" data-input="[name='contact']">중복 확인</button>
							</div>
							<input type="hidden" name="contact_verified" value="1" class="js-contact-verified" data-original-value="1">
							@error('contact')
							<p class="error_alert">{{ $message }}</p>
							@enderror
							@error('contact_verified')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>보호자 연락처<span>*</span></dt>
						<dd>
							<input type="text" name="parent_contact" value="{{ old('parent_contact', $member->formatted_parent_contact ?? $member->parent_contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel">
							@error('parent_contact')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					@else
					<dl>
						<dt>연락처<span>*</span></dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" name="contact" value="{{ old('contact', $member->formatted_contact ?? $member->contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel">
								<button type="button" class="btn btn_wkk btn_error js-duplicate-check" data-field="contact" data-input="[name='contact']">중복 확인</button>
							</div>
							@error('contact')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					@endif
					<dl>
						<dt>이메일<span>*</span></dt>
						<dd>
							<div class="flex email">
								<input type="text" name="email_id" value="{{ old('email_id', $emailParts['id'] ?? '') }}" placeholder="이메일을 입력해주세요.">
								<span>@</span>
								<select name="email_domain" class="email-domain-select">
									<option value="">이메일 주소 선택</option>
									<option value="naver.com" @selected(old('email_domain', $emailParts['domain'] ?? '') === 'naver.com')>naver.com</option>
									<option value="gmail.com" @selected(old('email_domain', $emailParts['domain'] ?? '') === 'gmail.com')>gmail.com</option>
									<option value="daum.net" @selected(old('email_domain', $emailParts['domain'] ?? '') === 'daum.net')>daum.net</option>
									<option value="nate.com" @selected(old('email_domain', $emailParts['domain'] ?? '') === 'nate.com')>nate.com</option>
									<option value="custom" @selected(old('email_domain', $emailParts['domain'] ?? '') === 'custom')>직접 입력</option>
								</select>
							</div>
							<input type="text" name="email_domain_custom" value="{{ old('email_domain_custom', $emailParts['custom_domain'] ?? '') }}" class="text w100p mt8 email-domain-custom" placeholder="직접 입력 시 도메인을 입력해 주세요." @if(old('email_domain', $emailParts['domain'] ?? '') !== 'custom') style="display:none;" @endif>
							@error('email')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
				</div>
			
				<div class="stit num mb0 nbd_b"><span>2</span>소속 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
				<div class="inputs">
					<dl>
						<dt>지역<span>*</span></dt>
						<dd>
							<div class="flex city">
								<select name="city">
									<option value="">선택</option>
									@if (old('city', $member->city))
									<option value="{{ old('city', $member->city) }}" selected>{{ old('city', $member->city) }}</option>
									@endif
								</select>
								<select name="district">
									<option value="">선택</option>
									@if (old('district', $member->district))
									<option value="{{ old('district', $member->district) }}" selected>{{ old('district', $member->district) }}</option>
									@endif
								</select>
							</div>
							@php
								$regionError = $errors->first('city') ?: $errors->first('district');
							@endphp
							@if ($regionError)
							<p class="error_alert">{{ $regionError }}</p>
							@endif
						</dd>
					</dl>
					<dl>
						<dt>학교명<span>*</span></dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" name="school_name" class="input_school" value="{{ old('school_name', $member->school_name) }}" placeholder="학교명을 검색해주세요.">
								<button type="button" class="btn btn_wkk" onclick="layerShow('pop_school')">학교 검색</button>
							</div>
							<input type="hidden" name="school_id" class="input_school_id" value="{{ old('school_id', $member->school_id) }}">
							@error('school_name')
							<p class="error_alert">{{ $message }}</p>
							@enderror
							@error('school_id')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					@if ($member->member_type === 'student')
					<dl>
						<dt>학년/반</dt>
						<dd>
							<div class="flex city">
								<select name="grade">
									<option value="">학년 선택</option>
									@for ($i = 1; $i <= 12; $i++)
									<option value="{{ $i }}" @selected(old('grade', $member->grade) == $i)>{{ $i }}학년</option>
									@endfor
								</select>
								<select name="class_number">
									<option value="">반 선택</option>
									@for ($i = 1; $i <= 30; $i++)
									<option value="{{ $i }}" @selected(old('class_number', $member->class_number) == $i)>{{ $i }}반</option>
									@endfor
								</select>
							</div>
							@php
								$classError = $errors->first('grade') ?: $errors->first('class_number');
							@endphp
							@if ($classError)
							<p class="error_alert">{{ $classError }}</p>
							@endif
						</dd>
					</dl>
					@endif
				</div>
			
				<div class="stit num mb0 nbd_b"><span>3</span>수신 동의</div>
				<div class="term_area">
					<div class="check_area">
						<label class="check"><input type="checkbox" name="notification_agree" value="1" @checked(old('notification_agree', $member->email_consent || $member->sms_consent))><i></i><strong>(선택)</strong>이메일 / SMS / 카카오 알림톡</label>
						@error('notification_agree')
						<p class="error_alert">{{ $message }}</p>
						@enderror
					</div>
				</div>

				<div class="btns_tac">
					<button type="button" class="btn_submit btn_wbb js-show-password-btn">정보 수정</button>
					<button type="submit" class="btn_submit btn_wbb js-submit-btn" style="display:none;">정보 수정</button>
					<button type="button" class="btn btn_kwy" onclick="layerShow('pop_secession')">회원 탈퇴</button>
				</div>
			</form>
			
		</div>
	</div>
</main>

<div class="popup" id="pop_school" data-search-url="{{ route('member.schools.search') }}">
	<div class="dm" onclick="layerHide('pop_school')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_school')"></button>
		<div class="tit">학교 검색</div>
		<div class="join_wrap">
			<div class="scroll">
				<div class="inputs">
					<dl>
						<dt>시/도</dt>
						<dd>
							<div class="flex city">
								<select class="search_city">
									<option value="">전체</option>
								</select>
								<select class="search_district">
									<option value="">전체</option>
								</select>
							</div>
						</dd>
					</dl>
					<dl>
						<dt>학교급</dt>
						<dd>
							<select class="search_level w100p">
								<option value="">전체</option>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>학교명</dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" class="search_keyword" placeholder="학교명을 검색해주세요.">
								<button type="button" class="btn btn_wkk btn_search_school">학교 검색</button>
							</div>
							<p class="search_result_message c_green mt8" style="display:none;"></p>
						</dd>
					</dl>
				</div>
				<div class="tbl">
					<table>
						<colgroup>
							<col class="w28_6"/>
							<col/>
							<col class="w28_6"/>
						</colgroup>
						<thead>
							<tr>
								<th>시/도</th>
								<th>학교명</th>
								<th>선택</th>
							</tr>
						</thead>
						<tbody class="school_results">
							<tr class="empty">
								<td colspan="3">검색 결과가 없습니다.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<button type="button" class="btn_submit btn_wbb mt4 btn_select_school">확인</button>
		</div>
	</div>
</div>

<div class="popup pop_info" id="pop_secession">
	<div class="dm" onclick="layerHide('pop_secession')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_secession')"></button>
		<div class="tit mb0">회원 탈퇴</div>
		<p><strong class="c_blue">회원 탈퇴</strong>를 진행하시겠습니까?</p>
		<div class="gbox flex_center colm">
			<p>탈퇴 시 회원의 모든 정보가 삭제되며 복구가 불가합니다.<br/>다시 이용하려면 신규가입이 필요합니다.</p>
		</div>
		<div class="flex_center check_area">
			<label class="check"><input type="checkbox" id="secession_agree"><i></i>위의 내용을 모두 읽었으며, 내용에 동의합니다.</label>
		</div>
		<button type="button" class="btn_check btn btn_kwy" onclick="confirmSecession()">탈퇴하기</button>
	</div>
</div>

@push('scripts')
<script src="{{ asset('js/member/register.js') }}?v={{ time() }}"></script>
<script>
$(function() {
	// 저장 완료 알럿 표시
	@if (session('success'))
	alert('{{ session('success') }}');
	@endif

	// validation 에러 알럿 표시
	@if ($errors->any())
	var errorMessages = [];
	@foreach ($errors->all() as $error)
		errorMessages.push('{{ $error }}');
	@endforeach
	if (errorMessages.length > 0) {
		alert('다음 오류가 발생했습니다:\n\n' + errorMessages.join('\n'));
	}
	@endif

	// 이메일 도메인 직접 입력 토글
	$('.email-domain-select').on('change', function() {
		var $customInput = $(this).closest('dd').find('.email-domain-custom');
		if ($(this).val() === 'custom') {
			$customInput.show();
		} else {
			$customInput.hide();
		}
	});

	// 연락처 중복 확인 성공 시 플래그 설정
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			mutation.addedNodes.forEach(function(node) {
				if (node.nodeType === 1 && $(node).hasClass('success_alert')) {
					var $dd = $(node).closest('dd');
					if ($dd.find('.js-contact-input').length) {
						$dd.find('.js-contact-verified').val('1');
					}
				}
			});
		});
	});
	
	if ($('.js-contact-input').length) {
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}

	// 연락처 입력값 변경 시 중복 확인 플래그 초기화
	$('.js-contact-input').on('input', function() {
		var $input = $(this);
		var $verified = $input.closest('dd').find('.js-contact-verified');
		var originalContact = $input.data('original-contact') || '';
		var currentValue = $input.val().replace(/[^0-9]/g, '');
		
		// 원래 연락처와 다르면 중복 확인 필요
		if (originalContact !== currentValue) {
			$verified.val('0');
			$input.closest('dd').find('.success_alert').remove();
		} else {
			// 원래 연락처와 같으면 중복 확인 불필요
			$verified.val('1');
		}
	});

	// 폼 제출 시 연락처 중복 확인 체크
	$('.js-member-register').on('submit', function(e) {
		var $contactInput = $('.js-contact-input');
		if ($contactInput.length) {
			var $verified = $contactInput.closest('dd').find('.js-contact-verified');
			if ($verified.val() !== '1') {
				e.preventDefault();
				alert('학생 연락처 중복 확인을 해주세요.');
				$contactInput.focus();
				return false;
			}
		}
	});
});

// 비밀번호 입력란 노출
$(function(){
	var $showBtn = $(".js-show-password-btn");
	var $submitBtn = $(".js-submit-btn");
	var $passwordFields = $(".password-fields");

	// 정보 수정 버튼 클릭 시 비밀번호 입력란 노출
	$showBtn.on("click", function(e){
		e.preventDefault();
		alert('비밀번호를 입력해야 수정이 완료됩니다.');
		$passwordFields.slideDown(300);
		$showBtn.hide();
		$submitBtn.show();
	});
});

// 새 비밀번호 확인
$(function(){
	var $pw = $(".now_pw");
	var $pwCheck = $(".now_pw_check");
	var $err = $pwCheck.closest("dd").find(".error_alert");

	$err.hide();

	function checkPasswordMatch(){
		var pw = $pw.val().trim();
		var pwCheck = $pwCheck.val().trim();

		if (pwCheck === "" || pw === "") {
			$err.hide();
			return;
		}

		if (pw !== pwCheck) {
			$err.show().text('* 동일한 비밀번호가 아닙니다.');
		} else {
			$err.hide();
		}
	}

	$pw.on("input", checkPasswordMatch);
	$pwCheck.on("input", checkPasswordMatch);

	$("form").on("submit", function(e){
		if ($pw.val().trim() !== "" && $pw.val().trim() !== $pwCheck.val().trim()) {
			e.preventDefault();
			$err.show().text('* 동일한 비밀번호가 아닙니다.');
			$pwCheck.focus();
			return false;
		}
	});
});

// 팝업
function layerShow(id) {
	$("#" + id).fadeIn(300);
}
function layerHide(id) {
	$("#" + id).fadeOut(300);
}

// 회원 탈퇴 확인
function confirmSecession() {
	if (!$('#secession_agree').is(':checked')) {
		alert('탈퇴 동의에 체크해주세요.');
		return;
	}
	
	if (confirm('정말 탈퇴하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
		// TODO: 회원 탈퇴 API 호출
		alert('회원 탈퇴 기능은 준비 중입니다.');
	}
}
</script>
@endpush

@endsection
