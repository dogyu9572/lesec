@extends('layouts.app')
@section('content')
<main class="error_wrap wrap_pb">
	<div class="inner">
		<div class="tit">일시적인 오류가 발생했습니다</div>
		<div class="tt">잠시 후 다시 시도해 주세요.</div>
		<p>문제가 계속되면 관리자에게 문의해 주세요.</p>
		<div class="btns_tac flex_center">
			<a href="{{ url()->previous() }}" class="btn btn_kwy">이전으로</a>
			<a href="/" class="btn btn_wkk">메인으로</a>
		</div>
	</div>
</main>
@endsection
