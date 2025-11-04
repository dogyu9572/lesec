@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		<div class="dlbox">
			<div class="tit">주차 요금</div>
			<div class="con tbl">
				<table>
					<thead>
						<tr>
							<th>시간</th>
							<th>요금</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>최초 30분 (이후 500원/10분)</td>
							<td>2,000원</td>
						</tr>
						<tr>
							<td>1시간</td>
							<td>3,500원</td>
						</tr>
						<tr>
							<td>2시간</td>
							<td>6,500원</td>
						</tr>
						<tr>
							<td>3시간</td>
							<td>9,500원</td>
						</tr>
						<tr>
							<td>4시간</td>
							<td>12,500원</td>
						</tr>
						<tr>
							<td>8시간</td>
							<td>24,500원</td>
						</tr>
						<tr>
							<td>1일 주차</td>
							<td>40,000원</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="dlbox">
			<div class="tit">안내 </div>
			<div class="con gbox dots_list">
				<p><strong>단체 버스로 참석하시는 경우에 한하여 주차권을 발급해 드립니다.</strong> (단체 버스는 교내 순환도로 주차장을 이용해 주세요.)</p>
				<p class="c_red">개인 차량을 이용하시는 분은 주차권을 구입하실 수 없습니다.</p>
				<p>교내 주차시설이 협소하여 일반 차량은 주차가 어려우니 가급적 대중교통수단을 이용하시기 바랍니다.</p>
				<p>부모님 또는 인솔자를 위한 공간이 따로 마련되어있지 않습니다. 교내 카페 혹은 중앙도서관(신분증 지참시 출입 가능)을 이용하여 주시기 바랍니다.</p>
			</div>
		</div>
	</div>

</main>
@endsection