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
                        <div class="detail-item">
                            <label>회원구분</label>
                            <span class="badge {{ $member->member_type === 'teacher' ? 'badge-primary' : 'badge-info' }}">
                                {{ $member->member_type === 'teacher' ? '교사' : '학생' }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>회원그룹</label>
                            <span>{{ $member->memberGroup ? $member->memberGroup->name : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>ID</label>
                            <span>{{ $member->login_id }}</span>
                        </div>
                        <div class="detail-item">
                            <label>이름</label>
                            <span>{{ $member->name }}</span>
                        </div>
                        <div class="detail-item">
                            <label>성별</label>
                            <span>{{ $member->gender === 'male' ? '남' : ($member->gender === 'female' ? '여' : '-') }}</span>
                        </div>
                        <div class="detail-item">
                            <label>생년월일</label>
                            <span>{{ $member->birth_date ? $member->birth_date->format('Y-m-d') : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>이메일</label>
                            <span>{{ $member->email }}</span>
                        </div>
                        <div class="detail-item">
                            <label>연락처</label>
                            <span>{{ $member->formatted_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>보호자 연락처</label>
                            <span>{{ $member->formatted_parent_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>비상 연락처</label>
                            <span>{{ $member->formatted_emergency_contact ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>비상 연락처 관계</label>
                            <span>{{ $member->emergency_contact_relation ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>소속 시/도</label>
                            <span>{{ $member->city ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>소속 시/군/구</label>
                            <span>{{ $member->district ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>소속학교</label>
                            <span>{{ $member->school_name ?? '-' }}</span>
                        </div>
                        @if($member->member_type === 'student')
                        <div class="detail-item">
                            <label>학년</label>
                            <span>{{ $member->grade ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>반</label>
                            <span>{{ $member->class_number ?? '-' }}</span>
                        </div>
                        @endif
                        <div class="detail-item">
                            <label>주소</label>
                            <span>{{ $member->address ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>우편번호</label>
                            <span>{{ $member->zipcode ?? '-' }}</span>
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
</div>
@endsection

