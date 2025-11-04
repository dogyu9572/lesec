@extends('layouts.app')
@section('content')
<main class="pb">

	<div class="inner">
		<div class="btit"><strong>구성원</strong></div>
		<div class="tbl mo_break_tbl">
			<table>
				<colgroup>
					<col class="w13_8">
					<col class="w13_8">
					<col class="w16_6">
					<col>
					<col class="w8_3">
				</colgroup>
				<thead>
					<tr>
						<th colspan="2">팀 / 직책</th>
						<th>전화번호</th>
						<th>담당업무</th>
						<th>이메일</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="4" class="contact0 bdr">교육센터</td>
						<td class="contact1">책임연구원</td>
						<td class="contact2">02-880-4948</td>
						<td class="contact3 tal dots_list"><p>담당업무 내용입니다.담당업무 내용입니다.담당업무 내용입니다.</p></td>
						<td class="contact4"><a href="#this" class="btn_email">이메일</a></td>
					</tr>
					<tr>
						<td class="contact1">선임분석원</td>
						<td class="contact2">02-888-0932</td>
						<td class="contact3 tal dots_list"><p>담당업무 내용입니다.담당업무 내용입니다.담당업무 내용입니다.</p></td>
						<td class="contact4"><a href="#this" class="btn_email">이메일</a></td>
					</tr>
					<tr>
						<td class="contact1">연구원</td>
						<td class="contact2">02-888-0933</td>
						<td class="contact3 tal dots_list"><p>담당업무 내용입니다.담당업무 내용입니다.담당업무 내용입니다.</p></td>
						<td class="contact4"><a href="#this" class="btn_email">이메일</a></td>
					</tr>
					<tr>
						<td class="contact1">연구원</td>
						<td class="contact2">02-888-4948</td>
						<td class="contact3 tal dots_list"><p>담당업무 내용입니다.담당업무 내용입니다.담당업무 내용입니다.</p></td>
						<td class="contact4"><a href="#this" class="btn_email">이메일</a></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

</main>
@endsection