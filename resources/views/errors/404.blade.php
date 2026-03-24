@extends('layouts.app')
@section('content')
<main class="error_wrap wrap_pb">
    
	<div class="inner">
		<div class="tit">요청하신 페이지를 찾을 수 없습니다</div>
		<div class="tt">주소가 잘못되었거나 페이지가 이동 또는 삭제되었습니다.</div>
		<p>존재하지 않는 주소를 입력하셨거나,<br/>요청하신 페이지의 주소가 변경 · 삭제되어 찾을 수 없습니다.</p>
		<div class="btns_tac flex_center">
			<a href="{{ url()->previous() }}" class="btn btn_kwy">이전으로</a>
			<a href="/" class="btn btn_wkk">메인으로</a>
		</div>
	</div>

</main>
@endsection