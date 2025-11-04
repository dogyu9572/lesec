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
		
			<div class="stit num mb0 nbd_b"><span>1</span>기본 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
			<div class="inputs">
				<dl>
					<dt>아이디<span>*</span></dt>
					<dd>
						<div class="flex inbtn">
							<input type="text" placeholder="아이디를 입력해주세요.">
							<button type="button" class="btn btn_wkk btn_error">중복 확인</button>
						</div>
						<p class="error_alert">* 입력하신 아이디로 이미 계정이 존재합니다.</p>
					</dd>
				</dl>
				<dl>
					<dt>비밀번호<span>*</span></dt>
					<dd><input type="password" class="text w100p" placeholder="비밀번호를 입력해주세요."></dd>
				</dl>
				<dl>
					<dt>비밀번호 확인<span>*</span></dt>
					<dd><input type="password" class="text w100p" placeholder="비밀번호를 다시 입력해주세요."></dd>
				</dl>
				<dl>
					<dt>이름<span>*</span></dt>
					<dd><input type="text" class="text w100p" placeholder="이름을 입력해주세요."></dd>
				</dl>
				<dl>
					<dt>생년월일<span>*</span></dt>
					<dd><input type="text" class="text w100p" placeholder="20010101"></dd>
				</dl>
				<dl>
					<dt>성별<span>*</span></dt>
					<dd>
						<div class="flex radios">
							<label class="radio"><input type="radio" name="gnd"><i></i>남자</label>
							<label class="radio"><input type="radio" name="gnd"><i></i>여자</label>
						</div>
					</dd>
				</dl>
				<dl>
					<dt>연락처<span>*</span></dt>
					<dd>
						<div class="flex inbtn">
							<input type="text" placeholder="휴대폰번호를 입력해주세요.">
							<button type="button" class="btn btn_wkk btn_error">중복 확인</button>
						</div>
						<p class="error_alert">* 입력하신 연락처로 이미 계정이 존재합니다.</p>
					</dd>
				</dl>
				<dl>
					<dt>이메일<span>*</span></dt>
					<dd>
						<div class="flex email">
							<input type="text" placeholder="이메일을 입력해주세요.">
							<span>@</span>
							<select name="" id="">
								<option value="">이메일 주소 선택</option>
							</select>
						</div>
					</dd>
				</dl>
			</div>
		
			<div class="stit num mb0 nbd_b"><span>2</span>소속 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
			<div class="inputs">
				<dl>
					<dt>시/도<span>*</span></dt>
					<dd>
						<div class="flex city">
							<select name="" id="">
								<option value="">선택</option>
							</select>
							<select name="" id="">
								<option value="">선택</option>
							</select>
						</div>
					</dd>
				</dl>
				<dl>
					<dt>학교명<span>*</span></dt>
					<dd>
						<div class="flex inbtn">
							<input type="text" placeholder="학교명을 검색해주세요.">
							<button type="button" class="btn btn_wkk" onclick="layerShow('pop_school')">학교 검색</button>
						</div>
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
						아이디, 비밀번호, 이름, 사용자구분, 소속(지역, 학교명, 학년, 반, 생년월일, 성별), 전화/핸드폰, 이메일
						<strong>개인정보의 보유 및 이용기간</strong>
						1년 (1년 후 파기)
						<strong>거부권 및 거부시의 불이익</strong>
						동의를 거부할 수 있으며, 동의 거부시 체험학습 신청이 제한될 수 있습니다. 
					</div>
				</div>
				<div class="check_area">
					<label class="check"><input type="checkbox"><i></i><strong>(필수)</strong>개인정보 처리방침에 동의합니다.</label>
				</div>
			</div>
		
			<div class="stit num mb0 nbd_b"><span>4</span>수신 동의</div>
			<div class="term_area">
				<div class="check_area">
					<label class="check"><input type="checkbox"><i></i><strong>(필수)</strong>이메일 / SMS / 카카오 알림톡</label>
				</div>
			</div>

			<button type="submit" class="btn_submit btn_wbb" onclick="location.href='/member/register4'">가입하기</button>
			
		</div>
	</div>
</main>

<div class="popup" id="pop_school">
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
								<select name="" id="">
									<option value="">선택</option>
								</select>
								<select name="" id="">
									<option value="">선택</option>
								</select>
							</div>
						</dd>
					</dl>
					<dl>
						<dt>학교급</dt>
						<dd>
							<select name="" id="" class="w100p">
								<option value="">고등학교</option>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>학교명</dt>
						<dd>
							<div class="flex inbtn">
								<input type="text" placeholder="학교명을 검색해주세요.">
								<button type="button" class="btn btn_wkk">학교 검색</button>
							</div>
							<input type="text" class="w100p mt" placeholder="학교명이 목록에 없을 경우, 직접 입력하여 등록해주세요.">
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
						<tbody>
							<tr>
								<td>제주시</td>
								<td>제주중앙고등학교</td>
								<td><label class="check solo"><input type="checkbox"><i></i></label></td>
							</tr>
							<tr>
								<td>제주시</td>
								<td>제주중앙고등학교</td>
								<td><label class="check solo"><input type="checkbox"><i></i></label></td>
							</tr>
							<tr>
								<td>제주시</td>
								<td>제주중앙고등학교</td>
								<td><label class="check solo"><input type="checkbox"><i></i></label></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<button type="submit" class="btn_submit btn_wbb mt4">가입하기</button>
		</div>
	</div>
</div>

<script>
$(".btn_error").click(function(){
	$(this).parent().next(".error_alert").show();
});

//팝업
function layerShow(id) {
	$("#" + id).fadeIn(300);
}
function layerHide(id) {
	$("#" + id).fadeOut(300);
}
</script>

@endsection