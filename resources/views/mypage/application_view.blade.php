@extends('layouts.app')
@section('content')
<main class="pb">
    
	<div class="inner">
		
		<div class="stit nbd_b">신청내역</div>
		<div class="tbl board_write board_row">
			<table>
				<tbody>
					<tr>
						<th>신청번호</th>
						<td>{{ $application->application_number ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청상태</th>
						<td>
							@if($application->application_status === 'pending')
								<span class="statebox wait">승인대기</span>
							@elseif($application->application_status === 'approved')
								<span class="statebox complet">승인완료</span>
							@elseif($application->application_status === 'cancelled')
								<span class="statebox">취소/반려</span>
							@else
								<span class="statebox complet">승인완료</span>
							@endif
						</td>
					</tr>
					<tr>
						<th>신청일시</th>
						<td>{{ optional($application->applied_at)->format('Y.m.d H:i') ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청자명</th>
						<td>{{ $application->applicant_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>학교</th>
						<td>{{ $application->school_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>신청인원</th>
						<td>{{ $application->applicant_count ? $application->applicant_count . '명' : '-' }}</td>
					</tr>
					<tr>
						<th>교육유형</th>
						<td>{{ $application->education_type_label }}</td>
					</tr>
					<tr>
						<th>프로그램명</th>
						<td>{{ $application->reservation->program_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>교육일</th>
						<td>
							@if($application->participation_date)
								{{ $application->participation_date->format('Y.m.d') }}
								@php
									$dayNames = ['일', '월', '화', '수', '목', '금', '토'];
									$dayName = $dayNames[$application->participation_date->dayOfWeek];
								@endphp
								({{ $dayName }})
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>교육료</th>
						<td>{{ $application->participation_fee ? number_format($application->participation_fee) . '원' : '-' }}</td>
					</tr>
					<tr>
						<th>결제방법</th>
						<td>{{ $application->payment_method_label }}</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="stit nbd_b mt">명단</div>
		<div class="tbl board_write board_apply_list">
			<table>
				<colgroup>
					<col class="appli_view01">
					<col class="appli_view02">
					<col class="appli_view03">
					<col>
				</colgroup>
				<thead>
					<tr>
						<th>이름</th>
						<th>학년</th>
						<th>반</th>
						<th>생년월일</th>
					</tr>
				</thead>
				<tbody>
					@forelse($application->participants as $participant)
					<tr>
						<td>{{ $participant->name ?? '-' }}</td>
						<td>{{ $participant->grade ? $participant->grade . '학년' : '-' }}</td>
						<td>{{ $participant->class ?? '-' }}</td>
						<td>{{ $participant->birthday ? $participant->birthday->format('Y.m.d') : '-' }}</td>
					</tr>
					@empty
					<tr>
						<td colspan="4" class="empty_message">등록된 명단이 없습니다.</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="btns_tac">
			<a href="{{ route('mypage.application_write', $application->id) }}" class="btn_submit btn_wbb">수정하기</a>
			<a href="{{ route('print.estimate', ['id' => $application->id]) }}" target="_blank" class="btn btn_bwb btn_print">견적서</a>
		</div>

	</div>

</main>
@endsection
