@extends('layouts.app')
@section('content')
<main class="error_wrap wrap_pb">
    
	<div class="inner">
		<div class="tit">404 ERROR</div>
		<div class="tt">죄송합니다. 현재 찾을 수 없는 페이지를 요청 하셨습니다.</div>
		<p>존재하지 않는 주소를 입력하셨거나,<br/>요청하신 페이지의 주소가 변경 · 삭제되어 찾을 수 없습니다.</p>
		<div class="btns_tac flex_center">
			<a href="javascript:history.back();" class="btn btn_kwy">이전으로</a>
			<a href="/" class="btn btn_wkk">메인으로</a>
		</div>
	</div>

</main>
@endsection