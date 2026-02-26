{{-- 토스페이먼츠 결제 위젯 모달 (개인/단체 신청 공통) --}}
<div class="popup pop_info" id="pop_toss_payment" style="display:none;">
	<div class="dm" data-layer-close="pop_toss_payment"></div>
	<div class="inbox">
		<button type="button" class="btn_close" data-layer-close="pop_toss_payment"></button>
		<div class="tit mb">결제하기</div>
		<div id="toss-payment-widget"></div>
		<div id="toss-agreement"></div>
		<div class="gbox flex_center colm" style="margin-top:16px;">
			<button type="button" class="btn btn_wkk" id="toss-payment-submit-btn">결제하기</button>
			<button type="button" class="btn btn_gray" data-layer-close="pop_toss_payment">취소</button>
		</div>
	</div>
</div>
