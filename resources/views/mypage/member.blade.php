@extends('layouts.app')
@section('content')
<main>

	<div class="inner">
		<div class="mem_wrap join_wrap">
			<p class="error_alert password-notice password-notice-hidden mb tac">* 안전한 정보 수정을 위해 현재 사용 중인 비밀번호를 입력해주세요.</p>

			<p class="edit-notice notice-muted-sm mb tac">정보 수정을 위해 '수정' 버튼을 클릭해 주세요.</p>

			@if ($errors->has('auth'))
			<div class="alert alert-error mb16">
				{{ $errors->first('auth') }}
			</div>
			@endif

			<form method="POST" action="{{ route('mypage.member.update') }}" class="js-member-register" data-duplicate-url="{{ route('member.register.check.duplicate') }}" data-school-search-url="{{ route('member.schools.search') }}" data-school-modal="#pop_school">
				<div class="stit num mb0 nbd_b"><span>1</span>기본 정보 <p class="abso">* 는 필수 입력 사항입니다.</p>
				</div>
				@csrf

				<div class="inputs">
					<dl>
						<dt>회원유형</dt>
						<dd>
							<input type="text" value="{{ $member->member_type === 'teacher' ? '교사' : '학생회원' }}" readonly class="text w100p">
						</dd>
					</dl>
					<dl>
						<dt>아이디<span>*</span></dt>
						<dd>
							<input type="text" value="{{ $member->login_id }}" readonly class="text w100p">
						</dd>
					</dl>
					<dl class="password-fields password-field-hidden">
						<dt>현재 비밀번호<span>*</span></dt>
						<dd>
							<div class="password-wrap">
								<input type="password" name="current_password" class="text w100p password-input" placeholder="안전한 정보 수정을 위해 현재 사용 중인 비밀번호를 입력해주세요." autocomplete="off">
								<button type="button" class="btn-eye toggle-password r16" aria-label="비밀번호 보기"><img src="/images/icon_eye.svg" alt="보기"></button>
							</div>
						</dd>
					</dl>
					<dl class="password-fields password-field-hidden">
						<dt>새 비밀번호</dt>
						<dd>
							<div class="password-wrap password-wrap_add">
								<input type="password" name="password" class="text w100p now_pw password-input password-input_add" placeholder="변경할 비밀번호를 입력해주세요. (변경하지 않으려면 비워두세요)" autocomplete="off">
								<button type="button" class="btn-eye toggle-password toggle-password_add r16" aria-label="비밀번호 보기"><img src="/images/icon_eye.svg" alt="보기"></button>
							</div>
							@error('password')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl class="password-fields password-field-hidden">
						<dt>새 비밀번호 확인</dt>
						<dd>
							<input type="password" name="password_confirmation" class="text w100p now_pw_check" placeholder="입력한 비밀번호를 다시 한번 입력해 주세요." autocomplete="off">
							<p class="error_alert is-hidden">* 동일한 비밀번호가 아닙니다.</p>
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
					@if ($member->member_type === 'student')
					<dl>
						<dt>학생 연락처<span>*</span></dt>
						<dd>
							<input type="text" value="{{ old('contact', $member->formatted_contact ?? $member->contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel" disabled>
						</dd>
					</dl>
					@if ($member->member_type === 'student')
					<dl>
						<dt>보호자 연락처<span>*</span></dt>
						<dd>
							<input type="text" name="parent_contact" value="{{ old('parent_contact', $member->formatted_parent_contact ?? $member->parent_contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel" disabled>
						</dd>
					</dl>
					@endif
					@else
					<dl>
						<dt>연락처<span>*</span></dt>
						<dd>
							<input type="text" value="{{ old('contact', $member->formatted_contact ?? $member->contact) }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel" disabled>
						</dd>
					</dl>
					@endif
					<dl>
						<dt>이메일<span>*</span></dt>
						<dd>
							<div class="flex email">
								<input type="text" name="email_id" value="{{ old('email_id', $emailParts['id'] ?? '') }}" placeholder="이메일을 입력해주세요." disabled class="js-editable-field">
								<span>@</span>
								<select name="email_domain" class="email-domain-select js-editable-field" disabled>
									<option value="">이메일 주소 선택</option>
									<option value="naver.com" @selected(old('email_domain', $emailParts['domain'] ?? '' )==='naver.com' )>naver.com</option>
									<option value="gmail.com" @selected(old('email_domain', $emailParts['domain'] ?? '' )==='gmail.com' )>gmail.com</option>
									<option value="daum.net" @selected(old('email_domain', $emailParts['domain'] ?? '' )==='daum.net' )>daum.net</option>
									<option value="nate.com" @selected(old('email_domain', $emailParts['domain'] ?? '' )==='nate.com' )>nate.com</option>
									<option value="custom" @selected(old('email_domain', $emailParts['domain'] ?? '' )==='custom' )>직접 입력</option>
								</select>
							</div>
							<input type="text" name="email_domain_custom" value="{{ old('email_domain_custom', $emailParts['custom_domain'] ?? '') }}" class="text w100p mt8 email-domain-custom js-editable-field @if(old('email_domain', $emailParts['domain'] ?? '' ) !=='custom' ) is-hidden @endif" placeholder="직접 입력 시 도메인을 입력해 주세요." disabled>
							@error('email')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
				</div>

				<div class="stit num mb0 nbd_b"><span>2</span>소속 정보 <p class="abso">* 는 필수 입력 사항입니다.</p>
				</div>
				<div class="inputs">
					<dl>
						<dt>지역<span>*</span></dt>
						<dd>
							<div class="flex city">
								<select class="city_select" disabled>
									<option value="">선택</option>
									@if (old('city', $member->city))
									<option value="{{ old('city', $member->city) }}" selected>{{ old('city', $member->city) }}</option>
									@endif
								</select>
								<input type="hidden" name="city" class="city_hidden" value="{{ old('city', $member->city) }}">
								<select class="district_select" disabled>
									<option value="">선택</option>
									@if (old('district', $member->district))
									<option value="{{ old('district', $member->district) }}" selected>{{ old('district', $member->district) }}</option>
									@endif
								</select>
								<input type="hidden" name="district" class="district_hidden" value="{{ old('district', $member->district) }}">
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
								<input type="text" name="school_name" class="input_school js-editable-field" value="{{ old('school_name', $member->school_name) }}" placeholder="학교명을 검색해주세요." readonly>
								<button type="button" class="btn btn_wkk js-school-search-btn" data-layer-open="pop_school" disabled>학교 검색</button>
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
								<select name="grade" class="js-editable-field" disabled>
									<option value="">학년 선택</option>
									@for ($i = 1; $i <= 12; $i++)
										<option value="{{ $i }}" @selected(old('grade', $member->grade) == $i)>{{ $i }}학년</option>
										@endfor
								</select>
								<select name="class_number" class="js-editable-field" disabled>
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
						<label class="check"><input type="checkbox" name="notification_agree" value="1" @checked(old('notification_agree', $member->email_consent || $member->sms_consent || $member->kakao_consent)) class="js-editable-field" disabled><i></i><strong>(필수)</strong>이메일 / SMS / 카카오 알림톡</label>
						@error('notification_agree')
						<p class="error_alert">{{ $message }}</p>
						@enderror
					</div>
				</div>

				<div class="btns_tac">
					<button type="button" class="btn_submit btn_wbb js-edit-btn">수정</button>
					<button type="submit" class="btn_submit btn_wbb js-submit-btn submit-button-hidden">수정 완료</button>
					<button type="button" class="btn btn_kwy" data-layer-open="pop_secession">회원 탈퇴</button>
				</div>
			</form>

		</div>
	</div>
</main>

<div class="popup" id="pop_school" data-search-url="{{ route('member.schools.search') }}">
	<div class="dm" data-layer-close="pop_school"></div>
	<div class="inbox">
		<button type="button" class="btn_close" data-layer-close="pop_school"></button>
		<div class="tit">학교 검색</div>
		<div class="join_wrap">
			<div class="scroll">
				<div class="inputs">
					<dl>
						<dt>지역</dt>
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
							<p class="search_result_message c_green mt8 is-hidden"></p>
						</dd>
					</dl>
				</div>
				<div class="tbl word_break_tbl">
					<table>
						<colgroup>
							<col class="w28_6" />
							<col />
							<col class="w28_6" />
						</colgroup>
						<thead>
							<tr>
								<th>지역</th>
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
				<div class="board_bottom">
					<div class="paging school_pagination school-pagination-hidden">
						<!-- 페이지네이션은 JavaScript로 동적 생성 -->
					</div>
				</div>
			</div>
			<div class="btn_group mt4 flex-gap-8">
				<button type="button" class="btn_submit btn_wbb btn_select_school flex-1">확인</button>
				<button type="button" class="btn_submit btn_wkk btn_input_school flex-1" disabled>입력</button>
			</div>
		</div>
	</div>
</div>

<div class="popup pop_info" id="pop_secession">
	<div class="dm" data-layer-close="pop_secession"></div>
	<div class="inbox">
		<button type="button" class="btn_close" data-layer-close="pop_secession"></button>
		<div class="tit mb0">회원 탈퇴</div>
		<p><strong class="c_blue">회원 탈퇴</strong>를 진행하시겠습니까?</p>
		<div class="gbox flex_center colm">
			<p>탈퇴 시 회원의 모든 정보가 삭제되며 복구가 불가합니다.<br />다시 이용하려면 신규가입이 필요합니다.</p>
		</div>
		<form method="POST" action="{{ route('mypage.member.secession') }}" id="secessionForm">
			@csrf
			<div class="flex_center check_area">
				<label class="check">
					<input type="checkbox" id="secession_agree" name="secession_agree" value="1">
					<i></i>위의 내용을 모두 읽었으며, 내용에 동의합니다.
				</label>
			</div>
			<button type="button" class="btn_check btn btn_kwy js-confirm-secession">탈퇴하기</button>
		</form>
	</div>
</div>

@push('scripts')
<script src="{{ asset('js/member/register.js') }}?v={{ time() }}"></script>
<script>
	$(function() {
		// 저장 완료 알럿 표시 (성공 시에만 필드 비활성화)
		@if(session('success'))
		alert('정보 수정이 완료되었습니다.');
		// 모든 필드 비활성화
		$('.js-editable-field').prop('disabled', true);
		$('.input_school').prop('readonly', true);
		$('.js-school-search-btn').prop('disabled', true);
		$('.password-fields input, .password-fields select').prop('disabled', true);
		$('.password-fields').slideUp(300);
		$('.password-notice').slideUp(300);
		$('.edit-notice').show();
		$('.js-submit-btn').hide();
		$('.js-edit-btn').show();
		$('input[name="current_password"]').val('');
		$('input[name="password"]').val('');
		$('input[name="password_confirmation"]').val('');
		@endif

		// validation 에러 알럿 표시 (검증 오류 시 수정 모드 유지)
		@if($errors->any() && !session('success'))
		var errorMessages = [];
		var passwordErrors = ['password', 'password.password', 'password.letters', 'password.numbers', 'password.symbols', 'password.min', 'password.max', 'password.confirmed'];
		var hasPasswordError = false;

		@foreach($errors->all() as $key => $error)
		@php
		$errorKey = $errors->keys()[$key] ?? '';
		$isPasswordError = in_array($errorKey, ['password', 'password.password', 'password.letters', 'password.numbers', 'password.symbols', 'password.min', 'password.max', 'password.confirmed']);
		$isCurrentPasswordError = $errorKey === 'current_password';
		@endphp
		@if($isCurrentPasswordError)
		// current_password 에러는 알럿으로만 표시
		errorMessages.push('{{ $error }}');
		@elseif(!$isPasswordError)
		errorMessages.push('{{ $error }}');
		@else
		hasPasswordError = true;
		@endif
		@endforeach

		if (errorMessages.length > 0) {
			alert('다음 오류가 발생했습니다:\n\n' + errorMessages.join('\n'));
			// 검증 오류 시 수정 모드로 전환 (유지)
			$('.password-fields').show();
			$('.password-notice').show();
			$('.js-editable-field').prop('disabled', false);
			// school_name 필드는 readonly 유지 (직접 입력 불가, 검색만 가능)
			$('.input_school').prop('readonly', true);
			$('.js-school-search-btn').prop('disabled', false);
			$('.password-fields input, .password-fields select').prop('disabled', false);
			$('.edit-notice').hide();
			$('.js-submit-btn').show();
			$('.js-edit-btn').hide();
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

	});

	// 페이지 로드 시 처리
	$(function() {
		// 검증 오류가 있을 때는 수정 모드로 시작
		@if($errors->any() && !session('success'))
		// 검증 오류 시 수정 모드로 전환
		$('.password-fields').show();
		$('.password-notice').show();
		$('.js-editable-field').prop('disabled', false);
		// school_name 필드는 readonly 유지 (직접 입력 불가, 검색만 가능)
		$('.input_school').prop('readonly', true);
		$('.js-school-search-btn').prop('disabled', false);
		$('.password-fields input, .password-fields select').prop('disabled', false);
		$('.edit-notice').hide();
		$('.js-submit-btn').show();
		$('.js-edit-btn').hide();
		@else
		// 검증 오류가 없을 때만 모든 입력 필드 비활성화
		$('.js-editable-field').prop('disabled', true);
		$('.input_school').prop('readonly', true);
		$('.js-school-search-btn').prop('disabled', true);
		@endif
	});

	// 수정 모드 전환
	$(function() {
		var $editBtn = $(".js-edit-btn");
		var $submitBtn = $(".js-submit-btn");
		var $passwordFields = $(".password-fields");
		var $passwordNotice = $(".password-notice");
		var $editNotice = $(".edit-notice");

		// 수정 버튼 클릭 시 수정 모드로 전환
		$editBtn.on("click", function(e) {
			e.preventDefault();
			$("html, body").animate({
				scrollTop: 0
			}, 250);

			// 비밀번호 필드 노출
			$passwordFields.slideDown(300);
			$passwordNotice.slideDown(300);

			// 수정 가능한 필드만 활성화: 비밀번호, 이메일, 소속 정보
			$('.password-fields input, .password-fields select').prop('disabled', false);
			$('.js-editable-field').prop('disabled', false);
			// school_name 필드는 readonly 유지 (직접 입력 불가, 검색만 가능)
			$('.input_school').prop('readonly', true);
			$('.js-school-search-btn').prop('disabled', false);

			// 안내 문구 숨기기
			$editNotice.hide();

			// 버튼 텍스트 변경
			$editBtn.hide();
			$submitBtn.show();
		});

	});

	// 새 비밀번호 확인
	$(function() {
		var $pw = $(".now_pw");
		var $pwCheck = $(".now_pw_check");
		var $err = $pwCheck.closest("dd").find(".error_alert");

		$err.hide();

		function checkPasswordMatch() {
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

		var $form = $("form.js-member-register");
		var isSubmitting = false;

		$form.on("submit", function(e) {
			// 새 비밀번호 확인 체크
			if ($pw.val().trim() !== "" && $pw.val().trim() !== $pwCheck.val().trim()) {
				e.preventDefault();
				$err.show().text('* 동일한 비밀번호가 아닙니다.');
				$pwCheck.focus();
				return false;
			}

			// 현재 비밀번호 검증 (새 비밀번호를 입력한 경우)
			var newPassword = $pw.val().trim();
			var currentPassword = $('input[name="current_password"]').val().trim();

			if (newPassword !== "") {
				if (currentPassword === "") {
					e.preventDefault();
					alert('비밀번호를 변경하려면 현재 비밀번호를 입력해주세요.');
					$('input[name="current_password"]').focus();
					return false;
				}

				// 현재 비밀번호를 먼저 검증 (AJAX)
				if (isSubmitting) {
					return false;
				}

				e.preventDefault();
				isSubmitting = true;

				// 현재 비밀번호만 먼저 검증
				$.ajax({
					url: '{{ route("mypage.member.update") }}',
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json'
					},
					data: {
						current_password: currentPassword,
						_current_password_check: true,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						// 검증 성공 시 폼 제출
						isSubmitting = false;
						$form.off('submit').submit();
					},
					error: function(xhr) {
						isSubmitting = false;
						if (xhr.status === 422) {
							var errors = xhr.responseJSON?.errors || {};
							if (errors.current_password && errors.current_password.length > 0) {
								// 현재 비밀번호 에러만 표시
								alert('현재 비밀번호가 올바르지 않습니다.');
								$('input[name="current_password"]').focus();
							} else {
								// 다른 에러가 있으면 폼 제출하여 서버에서 처리
								$form.off('submit').submit();
							}
						} else {
							// 다른 에러는 폼 제출하여 서버에서 처리
							$form.off('submit').submit();
						}
					}
				});

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

		if (confirm('정말 탈퇴하시겠습니까?')) {
			$('#secessionForm').trigger('submit');
		}
	}

	$(document).on('click', '.js-confirm-secession', function () {
		confirmSecession();
	});
</script>
@endpush

@endsection
