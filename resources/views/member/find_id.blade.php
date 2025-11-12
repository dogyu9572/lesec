@extends('layouts.app')
@section('content')
<main>
    
	<div class="inner">
		<div class="mem_wrap login_wrap">
			<div class="ctit">아이디 찾기</div>
			<div class="tabs">
				<a href="/member/find_id" class="on">아이디 찾기</a>
				<a href="/member/find_pw">비밀번호 변경</a>
			</div>
			<div class="inputs">
				@if ($errors->has('find_id_failed'))
				<p class="error_alert mb16">{{ $errors->first('find_id_failed') }}</p>
				@endif
				@if ($errors->has('find_id_flow'))
				<p class="error_alert mb16">{{ $errors->first('find_id_flow') }}</p>
				@endif
				<form method="POST" action="{{ route('member.find_id.submit') }}" class="js-member-form">
					@csrf
					<input type="text" name="name" class="text" value="{{ old('name') }}" placeholder="이름을 입력해 주세요.">
					@error('name')
					<p class="error_alert">{{ $message }}</p>
					@enderror
					<input type="text" name="contact" class="text mt16" value="{{ old('contact') }}" placeholder="휴대폰번호를 입력해 주세요." data-phone-input inputmode="tel" autocomplete="tel">
					@error('contact')
					<p class="error_alert">{{ $message }}</p>
					@enderror
					<button type="submit" class="btn btn_wbb mt4">확인</button>
				</form>
			</div>
		</div>
	</div>

</main>
@endsection

@push('scripts')
<script src="{{ asset('js/member/form-common.js') }}?v={{ time() }}"></script>
@endpush