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
					@forelse($contacts as $contact)
						@php
							$fields = is_array($contact->custom_fields ?? []) ? $contact->custom_fields : [];
							$team = $fields['team'] ?? $contact->title ?? '';
							$position = $fields['position'] ?? '';
							$phone = $fields['contact'] ?? '';
							$responsibilities = $fields['responsibilities'] ?? '';
							$email = $fields['email'] ?? '';
						@endphp
						<tr>
							<td class="contact0 bdr">{{ $team }}</td>
							<td class="contact1">{{ $position }}</td>
							<td class="contact2">{{ $phone }}</td>
							<td class="contact3 tal dots_list">
								<p>{{ $responsibilities }}</p>
							</td>
							<td class="contact4">
								@if(!empty($email))
									<a href="mailto:{{ $email }}" class="btn_email">이메일</a>
								@else
									-
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="text-center">등록된 연락처가 없습니다.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

</main>
@endsection