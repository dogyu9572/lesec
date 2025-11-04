@extends('layouts.app')
@section('content')
<main>

	<div class="inner">
		<div class="mem_wrap join_wrap">
			<div class="stit num mb0 nbd_b"><span>1</span>기본 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
			<div class="inputs">
				<dl>
					<dt>아이디<span>*</span></dt>
					<dd>
						<div class="flex inbtn">
							<input type="text" value="homepagekorea@naver.com" readonly>
							<button type="button" class="btn btn_wkk btn_error">중복 확인</button>
						</div>
						<p class="error_alert">* 입력하신 아이디로 이미 계정이 존재합니다.</p>
					</dd>
				</dl>
				<dl>
					<dt>현재 비밀번호</dt>
					<dd><input type="password" class="text w100p" placeholder="안전한 정보 수정을 위해 현재 사용 중인 비밀번호를 입력해주세요."></dd>
				</dl>
				<dl>
					<dt>새 비밀번호</dt>
					<dd><input type="password" class="text w100p now_pw" placeholder="변경할 비밀번호를 입력해주세요."></dd>
				</dl>
				<dl>
					<dt>새 비밀번호 확인</dt>
					<dd><input type="password" class="text w100p now_pw_check" placeholder="입력한 비밀번호를 다시 한번 입력해 주세요.">
						<p class="error_alert">* 동일한 비밀번호가 아닙니다.</p>
					</dd>
				</dl>
				<dl>
					<dt>이름<span>*</span></dt>
					<dd><input type="text" class="text w100p" value="홍길동 " readonly></dd>
				</dl>
				<dl>
					<dt>생년월일<span>*</span></dt>
					<dd><input type="text" class="text w100p" value="2008.01.05" readonly></dd>
				</dl>
				<dl>
					<dt>성별<span>*</span></dt>
					<dd>
						<div class="flex radios">
							<label class="radio"><input type="radio" name="gnd" checked><i></i>남자</label>
							<label class="radio"><input type="radio" name="gnd"><i></i>여자</label>
						</div>
					</dd>
				</dl>
				<dl>
					<dt>학생 연락처<span>*</span></dt>
					<dd><input type="text" class="text w100p" value="010-1234-5678" readonly></dd>
				</dl>
				<dl>
					<dt>보호자 연락처<span>*</span></dt>
					<dd><input type="text" class="text w100p" value="010-0000-5678"></dd>
				</dl>
				<dl>
					<dt>이메일<span>*</span></dt>
					<dd><input type="text" class="text w100p" value="hong1234@naver.com"></dd>
				</dl>
			</div>
		
			<div class="stit num mb0 nbd_b"><span>2</span>소속 정보 <p class="abso">* 는 필수 입력 사항입니다.</p></div>
			<div class="inputs">
				<dl>
					<dt>시/도<span>*</span></dt>
					<dd>
						<div class="flex city">
							<select name="" id="" readonly>
								<option value="">제주</option>
							</select>
							<select name="" id="" readonly>
								<option value="">제주시</option>
							</select>
						</div>
					</dd>
				</dl>
				<dl>
					<dt>학교명<span>*</span></dt>
					<dd>
						<div class="flex inbtn">
							<input type="text" placeholder="학교명을 검색해주세요." value="제주중앙고등학교" readonly>
							<button type="button" class="btn btn_wkk" onclick="layerShow('pop_school')">학교 검색</button>
						</div>
					</dd>
				</dl>
				<dl>
					<dt>학년/반<span>*</span></dt>
					<dd>
						<div class="flex city">
							<select name="" id="">
								<option value="">1학년</option>
							</select>
							<select name="" id="">
								<option value="">3반</option>
							</select>
						</div>
					</dd>
				</dl>
			</div>
		
			<div class="stit num mb0 nbd_b"><span>3</span>수신 동의</div>
			<div class="term_area">
				<div class="check_area">
					<label class="check"><input type="checkbox"><i></i><strong>(필수)</strong>이메일 / SMS / 카카오 알림톡</label>
				</div>
			</div>

			<div class="btns_tac">
				<button type="submit" class="btn_submit btn_wbb">정보 수정</button>
				<button type="submit" class="btn btn_kwy" onclick="layerShow('pop_secession')">회원 탈퇴</button>
			</div>
			
		</div>
	</div>
</main>

<div class="popup" id="pop_school">
	<div class="dm" onclick="layerHide('pop_school')"></div>
	<div class="inbox">
		<button type="button" class="btn_close" onclick="layerHide('pop_school')"></button>
		<div class="tit">학교 검색</div>
		<div class="join_wrap scroll">
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
			<button type="submit" class="btn_submit btn_wbb mt4">가입하기</button>
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
			<label class="check"><input type="checkbox"><i></i>위의 내용을 모두 읽었으며, 내용에 동의합니다.</label>
		</div>
		<button type="button" class="btn_check btn btn_kwy">탈퇴하기</button>
	</div>
</div>

<script>
$(".btn_error").click(function(){
	$(this).parent().next(".error_alert").show();
});

//새 비밀번호 확인
$(function(){
	var $pw = $(".now_pw");
	var $pwCheck = $(".now_pw_check");
	var $err = $pwCheck.closest("dd").find(".error_alert");

	// 초기에는 에러 숨김
	$err.hide();

	function checkPasswordMatch(){
		var pw = $pw.val().trim();
		var pwCheck = $pwCheck.val().trim();

		if (pwCheck === "") {
			$err.hide();
			return;
		}

		if (pw !== pwCheck) $err.show();
		else $err.hide();
	}

	// 입력 시 실시간 비교
	$pw.on("input", checkPasswordMatch);
	$pwCheck.on("input", checkPasswordMatch);

	// 폼 제출 시 검사
	$("form").on("submit", function(e){
		if ($pw.val().trim() !== $pwCheck.val().trim()) {
			e.preventDefault();
			$err.show();
			$pwCheck.focus();
			return false;
		}
	});
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