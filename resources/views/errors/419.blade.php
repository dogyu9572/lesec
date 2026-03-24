@extends('layouts.app')

@section('content')
<main class="error_wrap wrap_pb">
	<div class="inner">
		<div class="tit">세션이 만료되었습니다</div>
		<div class="tt">보안을 위해 입력 시간이 길어지면 이 화면이 표시될 수 있습니다.</div>
		<p>이전 페이지로 돌아가 새로고침한 뒤 다시 시도해 주세요.<br/>작성 중이던 내용은 복구되지 않을 수 있습니다.</p>
		<div class="btns_tac flex_center">
			<a href="{{ url()->previous() }}" class="btn btn_kwy">이전으로</a>
			<a href="/" class="btn btn_wkk">메인으로</a>
		</div>
	</div>
</main>
@endsection
