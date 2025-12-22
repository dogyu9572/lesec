@extends('layouts.app')
@section('content')
<main>

	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="ctit mb32">회원가입</div>
			<ol class="join_step">
				<li class="i1 onf"><i></i><p>회원 구분</p></li>
				<li class="i2 onf"><i></i><p>본인 인증</p></li>
				<li class="i3 on"><i></i><p>회원정보 입력</p></li>
				<li class="i4"><i></i><p>회원가입 완료</p></li>
			</ol>
			@if ($errors->has('process'))
			<p class="error_alert">{{ $errors->first('process') }}</p>
			@endif

			<form method="POST"
				action="{{ route('member.register3_a.submit') }}"
				class="js-member-register"
				data-duplicate-url="{{ route('member.register.check.duplicate') }}"
				data-school-search-url="{{ route('member.schools.search') }}"
				data-school-modal="#pop_school"
				@if ($errors->any())
					data-errors='@json($errors->messages())'
				@endif>
				@csrf

				<div class="stit num mb0 nbd_b"><span>1</span>기본 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
				<div class="inputs">
					<dl>
						<dt>아이디<span>*</span></dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" name="login_id" value="{{ old('login_id') }}" placeholder="아이디를 입력해주세요." data-original-login-id="{{ old('login_id') }}">
								<button type="button"
									class="btn btn_wkk btn_error js-duplicate-check"
									data-field="login_id"
									data-input="[name='login_id']">중복 확인</button>
							</div>
							<input type="hidden" name="login_id_verified" value="0">
							@error('login_id')
							<p class="error_alert">{{ $message }}</p>
							@enderror
							@error('login_id_verified')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>비밀번호<span>*</span></dt>
						<dd class="password-wrap">
							<input type="password" name="password" class="text w100p password-input" placeholder="영문/숫자/특수문자를 포함하여 9~12자리로 입력해주세요.">
							<button type="button" class="btn-eye toggle-password r16">
								<img src="/images/icon_eye.svg" alt="보기">
							</button>
							@error('password')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>비밀번호 확인<span>*</span></dt>
						<dd>
							<input type="password" name="password_confirmation" class="text w100p" placeholder="비밀번호를 다시 입력해주세요.">
						</dd>
					</dl>
					<dl>
						<dt>이름<span>*</span></dt>
						<dd>
							<input type="text" name="name" class="text w100p" value="{{ old('name') }}" placeholder="이름을 입력해주세요.">
							@error('name')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>생년월일<span>*</span></dt>
						<dd>
							<input type="text" name="birth_date" class="text w100p" value="{{ old('birth_date') }}" placeholder="20010101">
							@error('birth_date')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>성별<span>*</span></dt>
						<dd>
							<div class="flex radios">
								<label class="radio"><input type="radio" name="gender" value="male" @checked(old('gender', 'male') === 'male')><i></i>남자</label>
								<label class="radio"><input type="radio" name="gender" value="female" @checked(old('gender') === 'female')><i></i>여자</label>
							</div>
							@error('gender')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>연락처<span>*</span></dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" name="contact" value="{{ old('contact') }}" placeholder="휴대폰번호를 입력해주세요." data-phone-input inputmode="tel" autocomplete="tel" data-original-contact="{{ preg_replace('/[^0-9]/', '', old('contact') ?? '') }}">
								<button type="button"
									class="btn btn_wkk btn_error js-duplicate-check"
									data-field="contact"
									data-input="[name='contact']">중복 확인</button>
							</div>
							<input type="hidden" name="contact_verified" value="0">
							@error('contact')
							<p class="error_alert">{{ $message }}</p>
							@enderror
							@error('contact_verified')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>이메일<span>*</span></dt>
						<dd>
							<div class="flex email">
								<input type="text" name="email_id" value="{{ old('email_id') }}" placeholder="이메일을 입력해주세요.">
								<span>@</span>
								<select name="email_domain" class="email-domain-select">
									<option value="">이메일 주소 선택</option>
									<option value="naver.com" @selected(old('email_domain') === 'naver.com')>naver.com</option>
									<option value="gmail.com" @selected(old('email_domain') === 'gmail.com')>gmail.com</option>
									<option value="daum.net" @selected(old('email_domain') === 'daum.net')>daum.net</option>
									<option value="nate.com" @selected(old('email_domain') === 'nate.com')>nate.com</option>
									<option value="custom" @selected(old('email_domain') === 'custom')>직접 입력</option>
								</select>
							</div>
						<input type="text" name="email_domain_custom" value="{{ old('email_domain_custom') }}" class="text w100p mt8 email-domain-custom" placeholder="직접 입력 시 도메인을 입력해 주세요." @if(old('email_domain') !== 'custom') style="display:none;" @endif>
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
									@if (old('city'))
									<option value="{{ old('city') }}" selected>{{ old('city') }}</option>
									@endif
								</select>
								<select name="district">
									<option value="">선택</option>
									@if (old('district'))
									<option value="{{ old('district') }}" selected>{{ old('district') }}</option>
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
								<input type="text" name="school_name" class="input_school" value="{{ old('school_name') }}" placeholder="학교명을 검색해주세요.">
								<button type="button" class="btn btn_wkk" onclick="layerShow('pop_school')">학교 검색</button>
							</div>
							<input type="hidden" name="school_id" class="input_school_id" value="{{ old('school_id') }}">
							@error('school_name')
							<p class="error_alert">{{ $message }}</p>
							@enderror
							@error('school_id')
							<p class="error_alert">{{ $message }}</p>
							@enderror
						</dd>
					</dl>
					<dl>
						<dt>학년/반<span>*</span></dt>
						<dd>
							<div class="flex city">
								<select name="grade">
									<option value="">학년 선택</option>
									@for ($i = 1; $i <= 3; $i++)
									<option value="{{ $i }}" @selected(old('grade') == $i)>{{ $i }}학년</option>
									@endfor
								</select>
								<select name="class_number">
									<option value="">반 선택</option>
									@for ($i = 1; $i <= 20; $i++)
									<option value="{{ $i }}" @selected(old('class_number') == $i)>{{ $i }}반</option>
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
				</div>
			
				<div class="stit num mb0 nbd_b"><span>3</span>약관 동의</div>
				<div class="term_area">
					<div class="textarea">
						<div class="scroll">
							<strong>개인정보의 수집이용 목적</strong>
							체험학습 신청, 회원관리, 수료증 발급
							<strong>수집하려는 개인정보의 항목</strong>
							이름, 아이디, 비밀번호, 사용자구분, 생년월일, 성별, 소속(지역, 학교명, 학력선택, 학년, 반), 휴대폰번호, 이메일 주소
							<strong>개인정보의 보유 및 이용기간</strong>
							1년 (1년 후 파기)
							<strong>거부권 및 거부시의 불이익</strong>
							동의를 거부할 수 있으며, 동의 거부시 체험학습 신청이 제한될 수 있습니다. 
						</div>
					</div>
					<div class="check_area">
						<label class="check"><input type="checkbox" name="privacy_agree" value="1" @checked(old('privacy_agree'))><i></i><strong>(필수)</strong>개인정보 처리방침에 동의합니다.</label>
						@error('privacy_agree')
						<p class="error_alert">{{ $message }}</p>
						@enderror
					</div>
				</div>
			
				<div class="stit num mb0 nbd_b"><span>4</span>수신 동의</div>
				<div class="term_area">
					<div class="check_area">
						<label class="check"><input type="checkbox" name="notification_agree" value="1" @checked(old('notification_agree'))><i></i><strong>(필수)</strong>이메일 / SMS / 카카오 알림톡</label>
						@error('notification_agree')
						<p class="error_alert">{{ $message }}</p>
						@enderror
					</div>
				</div>

				<button type="submit" class="btn_submit btn_wbb">가입하기</button>
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

@push('scripts')
<script src="{{ asset('js/member/register.js') }}?v={{ time() }}"></script>
@endpush

@endsection
