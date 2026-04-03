@extends('backoffice.layouts.app')

@section('title', '회원 상세')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
<div class="admin-detail-container">
    <div class="detail-header">
        <h1>회원 정보</h1>
        <div class="detail-actions">
            <a href="{{ route('backoffice.members.edit', $member) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> <span class="btn-text">수정</span>
            </a>
            <form action="{{ route('backoffice.members.destroy', $member) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('정말 이 회원을 삭제하시겠습니까?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> <span class="btn-text">삭제</span>
                </button>
            </form>
            <a href="{{ route('backoffice.members.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <span class="btn-text">목록으로</span>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="detail-grid">
                        <div class="detail-item_flexrow">
                            <label>회원구분 : </label>
                            <span>{{ $member->member_type === 'teacher' ? '교사' : '학생' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>회원그룹 : </label>
                            <span>{{ $member->memberGroup ? $member->memberGroup->name : '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>ID : </label>
                            <span>{{ $member->login_id }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>이름 : </label>
                            <span>{{ $member->name }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>성별 : </label>
                            <span>{{ $member->gender === 'male' ? '남' : ($member->gender === 'female' ? '여' : '-') }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>생년월일 : </label>
                            <span>{{ $member->birth_date ? $member->birth_date->format('Y-m-d') : '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>이메일 : </label>
                            <span>{{ $member->email }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>연락처 : </label>
                            <span>{{ $member->formatted_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>보호자 연락처 : </label>
                            <span>{{ $member->formatted_parent_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>비상 연락처 : </label>
                            <span>{{ $member->formatted_emergency_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>비상 연락처 관계 : </label>
                            <span>{{ $member->emergency_contact_relation ?? '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>지역 : </label>
                            @php
                                $cityValue = $member->city ?? '';
                                $districtValue = $member->district ?? '';
                                $cityDisplay = is_string($cityValue) && trim($cityValue) !== '' ? $cityValue : '선택';
                                $districtDisplay = is_string($districtValue) && trim($districtValue) !== '' ? $districtValue : '선택';
                            @endphp
                            <span>{{ $cityDisplay }} / {{ $districtDisplay }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>소속학교 : </label>
                            <span>{{ $member->school_name ?? '-' }}</span>
                        </div>
                        @if($member->member_type === 'student')
                        <div class="detail-item_flexrow">
                            <label>학년 : </label>
                            <span>{{ $member->grade ?? '-' }}</span>
                        </div>
                        <div class="detail-item_flexrow">
                            <label>반 : </label>
                            <span>{{ $member->class_number ?? '-' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>이메일 수신 동의</label>
                            <span class="status-badge {{ $member->email_consent ? 'status-active' : 'status-inactive' }}">
                                {{ $member->email_consent ? '동의' : '비동의' }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>SMS 수신 동의</label>
                            <span class="status-badge {{ $member->sms_consent ? 'status-active' : 'status-inactive' }}">
                                {{ $member->sms_consent ? '동의' : '비동의' }}
                            </span>
                        </div>
                        @if(isset($member->kakao_consent))
                        <div class="detail-item">
                            <label>카카오 알림톡 수신 동의</label>
                            <span class="status-badge {{ $member->kakao_consent ? 'status-active' : 'status-inactive' }}">
                                {{ $member->kakao_consent ? '동의' : '비동의' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="program-section">
                        <div class="section-title">신청 현황</div>
                        <div class="table-responsive">
                            <table class="board-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>신청 번호</th>
                                        <th>신청 상태</th>
                                        <th>결제상태</th>
                                        <th>신청자명</th>
                                        <th>학교명</th>
                                        <th>교육 유형</th>
                                        <th>프로그램명</th>
                                        <th>신청 인원</th>
                                        <th>접수 상태</th>
                                        <th>결제 방법</th>
                                        <th>교육료</th>
                                        <th>참가일</th>
                                        <th>신청일시</th>
                                        <th>출석 여부</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allApplications = collect();
                                        
                                        // 개인 신청 내역 추가
                                        foreach ($individualApplications as $app) {
                                            // 참가일 범위 계산 (시작일~종료일)
                                            $startDate = $app->reservation?->education_start_date ?? null;
                                            $endDate = $app->reservation?->education_end_date ?? null;
                                            if ($startDate && $endDate) {
                                                $participationDate = $startDate->format('Y.m.d') . ' ~ ' . $endDate->format('Y.m.d');
                                            } elseif ($startDate) {
                                                $participationDate = $startDate->format('Y.m.d') . ' ~';
                                            } elseif ($endDate) {
                                                $participationDate = '~ ' . $endDate->format('Y.m.d');
                                            } else {
                                                $participationDate = '-';
                                            }
                                            
                                            $allApplications->push([
                                                'type' => 'individual',
                                                'id' => $app->id,
                                                'application_number' => $app->application_number,
                                                'application_status' => $app->draw_result_label,
                                                'payment_status' => data_get($app, 'payment_status_label', '-'),
                                                'applicant_name' => ($member->name ?? '-'),
                                                'school_name' => ($member->school_name ?? '-'),
                                                'education_type' => $app->education_type_label,
                                                'program_name' => $app->program_name,
                                                'applicant_count' => 1,
                                                'reception_status' => $app->reception_type_label,
                                                'payment_method' => $app->payment_method ? (\App\Models\GroupApplication::PAYMENT_METHOD_LABELS[$app->payment_method] ?? $app->payment_method) : '-',
                                                'participation_fee' => $app->participation_fee ?? 0,
                                                'participation_date' => $participationDate,
                                                'applied_at' => $app->applied_at ? $app->applied_at->format('Y.m.d H:i') : '-',
                                                'attendance' => '-',
                                            ]);
                                        }
                                        
                                        // 단체 신청 내역 추가
                                        foreach ($groupApplications as $app) {
                                            // 참가일 범위 계산 (시작일~종료일)
                                            $startDate = $app->reservation?->education_start_date ?? null;
                                            $endDate = $app->reservation?->education_end_date ?? null;
                                            if ($startDate && $endDate) {
                                                $participationDate = $startDate->format('Y.m.d') . ' ~ ' . $endDate->format('Y.m.d');
                                            } elseif ($startDate) {
                                                $participationDate = $startDate->format('Y.m.d') . ' ~';
                                            } elseif ($endDate) {
                                                $participationDate = '~ ' . $endDate->format('Y.m.d');
                                            } else {
                                                $participationDate = '-';
                                            }
                                            
                                            $allApplications->push([
                                                'type' => 'group',
                                                'id' => $app->id,
                                                'application_number' => $app->application_number,
                                                'application_status' => $app->application_status_label,
                                                'payment_status' => data_get($app, 'payment_status_label', '-'),
                                                'applicant_name' => ($member->name ?? '-'),
                                                'school_name' => ($member->school_name ?? '-'),
                                                'education_type' => $app->education_type_label,
                                                'program_name' => $app->program_name_label,
                                                'applicant_count' => $app->applicant_count ?? 0,
                                                'reception_status' => $app->reception_status_label,
                                                'payment_method' => $app->payment_method_label,
                                                'participation_fee' => $app->participation_fee ?? 0,
                                                'participation_date' => $participationDate,
                                                'applied_at' => $app->applied_at_formatted ?? '-',
                                                'attendance' => '-',
                                            ]);
                                        }
                                        
                                        // 신청일시 기준 내림차순 정렬
                                        $allApplications = $allApplications->sortByDesc(function($app) {
                                            return $app['applied_at'];
                                        })->values();
                                    @endphp
                                    
                                    @if($allApplications->count() > 0)
                                        @foreach($allApplications as $index => $app)
                                            <tr>
                                                <td>{{ $allApplications->count() - $index }}</td>
                                                <td>{{ $app['application_number'] }}</td>
                                                <td>{{ $app['application_status'] }}</td>
                                                <td>{{ $app['payment_status'] }}</td>
                                                <td>{{ $app['applicant_name'] }}</td>
                                                <td>{{ $app['school_name'] }}</td>
                                                <td>{{ $app['education_type'] }}</td>
                                                <td>{{ $app['program_name'] }}</td>
                                                <td>{{ $app['applicant_count'] }}명</td>
                                                <td>{{ $app['reception_status'] }}</td>
                                                <td>{{ $app['payment_method'] }}</td>
                                                <td>{{ number_format($app['participation_fee']) }}원</td>
                                                <td>{{ $app['participation_date'] }}</td>
                                                <td>{{ $app['applied_at'] }}</td>
                                                <td>{{ $app['attendance'] }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                    <tr style="border: none;">
                                        <td colspan="15" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">신청 내역이 없습니다.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>가입일시</label>
                            <span>{{ $member->joined_at ? $member->joined_at->format('Y-m-d H:i:s') : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>최종 로그인</label>
                            <span>{{ $member->last_login_at ? $member->last_login_at->format('Y-m-d H:i:s') : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>상태</label>
                            <span class="status-badge {{ $member->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $member->is_active ? '활성화' : '비활성화' }}
                            </span>
                        </div>
                        @if($member->withdrawal_at)
                        <div class="detail-item">
                            <label>탈퇴일시</label>
                            <span>{{ $member->withdrawal_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        <div class="detail-item">
                            <label>탈퇴사유</label>
                            <span>{{ $member->withdrawal_reason ?? '-' }}</span>
                        </div>
                        @endif
                        @if($member->memo)
                        <div class="detail-item" style="grid-column: span 3;">
                            <label>메모</label>
                            <span>{{ $member->memo }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="program-section">
                        <div class="section-title">교육 취소 내역</div>
                        @if($cancelledApplications && $cancelledApplications->count() > 0)
                            <div class="table-responsive">
                                <table class="board-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th>신청번호</th>
                                            <th>신청유형</th>
                                            <th>취소일시</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cancelledApplications as $index => $app)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $app['application_number'] ?? '-' }}</td>
                                                <td>{{ $app['type'] ?? '-' }}</td>
                                                <td>{{ $app['cancelled_at_display'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="no-data">
                                <p>취소 내역이 없습니다.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

