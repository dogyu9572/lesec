@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="end">
			<i class="check"></i>
			<div class="tit">신청이 완료되었습니다.</div>
			<p>
				관리자의 승인 절차를 거쳐 최종 확정됩니다.<br/>
				마이페이지에서 신청 내역을 확인하실 수 있습니다.
			</p>
			<button type="button" class="btn_wkk" onclick="location.href='{{ route('program.show', $type) }}'">확인</button>
		</div>
		
	</div>

</main>
@endsection

