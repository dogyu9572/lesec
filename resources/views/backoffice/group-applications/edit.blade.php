@extends('backoffice.layouts.app')

@section('title', '단체 신청 내역 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
@php
    $applicationId = data_get($application, 'id');
    $participants = data_get($application, 'participants', []);
@endphp
<div class="admin-form-container">
    <div id="application-search-config"
        data-member-search-url="{{ route('backoffice.group-applications.search-members') }}"
        data-program-search-url="{{ route('backoffice.group-applications.search-programs') }}"></div>
    <div id="roster-config"
        data-application-id="{{ (int) ($applicationId ?? 0) }}"
        data-sample-url="{{ route('backoffice.group-applications.download-roster-sample', $applicationId) }}"
        data-upload-url="{{ route('backoffice.group-applications.upload-roster', $applicationId) }}"
        data-participant-store-url="{{ route('backoffice.group-applications.participants.store', $applicationId) }}"
        data-csrf-token="{{ csrf_token() }}"></div>
    <div class="form-header">
        <a href="{{ route('backoffice.group-applications.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">목록으로</span>
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <form method="POST" action="{{ route('backoffice.group-applications.update', $applicationId ?? 0) }}">
                        @csrf
                        @method('PUT')

                        <div class="program-section">
                            <div class="section-title">프로그램 정보</div>
                            <div class="form-grid grid-2">
                                <div>
                                    <label>교육유형</label>
                                    <div class="radio-group">
                                        @foreach(data_get($formOptions, 'education_types', []) as $value => $label)
                                            <label class="radio-label">
                                                <input type="radio" name="education_type" value="{{ $value }}" @checked(data_get($application, 'education_type') === $value)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group grid-span-2">
                                    <label for="program_name">프로그램명</label>
                                    <div class="school-search-wrapper">
                                        <input type="hidden" id="program_reservation_id" name="program_reservation_id" value="{{ data_get($application, 'program_reservation_id') }}">
                                        <input type="text" id="program_name" value="{{ data_get($application, 'reservation.program_name', data_get($application, 'program_name')) }}" readonly>
                                        <button type="button" id="program-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="participation_date_display">참가일</label>
                                    <input type="hidden" id="participation_date" name="participation_date" value="{{ data_get($application, 'participation_date') }}">
                                    <input type="text" id="participation_date_display" value="{{ data_get($application, 'participation_date_formatted', data_get($application, 'participation_date')) }}" readonly>
                                </div>
                                <div>
                                    <label>결제방법</label>
                                    <div class="checkbox-group">
                                        @foreach(data_get($formOptions, 'payment_methods', []) as $value => $label)
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="payment_methods[]" value="{{ $value }}" @checked(in_array($value, data_get($application, 'payment_methods', []), true))>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title">신청 정보</div>
                            <div class="form-grid grid-2">
                                <div class="form-group">
                                    <label for="application_number">신청번호</label>
                                    <input type="text" id="application_number" value="{{ data_get($application, 'application_number', '-') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="application_status">신청상태</label>
                                    <select id="application_status" name="application_status">
                                        @foreach(data_get($formOptions, 'application_statuses', []) as $value => $label)
                                            <option value="{{ $value }}" @selected(data_get($application, 'application_status') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="applicant_name">신청자명</label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="applicant_name" name="applicant_name" value="{{ data_get($application, 'applicant_name') }}" readonly>
                                        <input type="hidden" id="member_id" name="member_id" value="{{ data_get($application, 'member_id') }}">
                                        <button type="button" id="member-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 회원 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="applicant_contact">연락처</label>
                                    <input type="text" id="applicant_contact" name="applicant_contact" value="{{ data_get($application, 'applicant_contact') }}">
                                </div>
                                <div class="form-group">
                                    <label for="school_level">학교급/학교</label>
                                    <div class="school-search-wrapper">
                                        @php
                                            $level = data_get($application, 'school_level');
                                            $levelLabel = $level === 'middle' ? '중학교' : ($level === 'high' ? '고등학교' : $level);
                                        @endphp
                                        <input type="text" id="school_level" name="school_level" value="{{ $levelLabel }}">
                                        <input type="text" id="school_name" name="school_name" value="{{ data_get($application, 'school_name') }}" readonly>
                                        <button type="button" id="school-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="applicant_count">신청인원</label>
                                    <input type="number" min="0" id="applicant_count" name="applicant_count" value="{{ data_get($application, 'applicant_count') }}">
                                </div>
                                <div class="form-group">
                                    <label for="applied_at">신청일시</label>
                                    <input type="text" id="applied_at" value="{{ data_get($application, 'applied_at_formatted', data_get($application, 'applied_at')) }}" readonly>
                                </div>
                                <div>
                                    <label>결제상태</label>
                                    <div class="radio-group">
                                        @foreach(data_get($formOptions, 'payment_statuses', []) as $value => $label)
                                            <label class="radio-label">
                                                <input type="radio" name="payment_status" value="{{ $value }}" @checked(data_get($application, 'payment_status') === $value)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title d-flex align-items-center justify-content-between">
                                <span>신청 명단</span>
                                <div>
                                    <a href="{{ route('backoffice.group-applications.download-roster-sample', $applicationId) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-file-download"></i> 샘플파일 받기
                                    </a>
                                    <input type="file" id="admin_csv_upload" accept=".csv" style="display:none;">
                                    <button type="button" class="btn btn-light btn-sm" id="btn-admin-upload">
                                        <i class="fas fa-upload"></i> 일괄 업로드
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="btn-admin-add-row">
                                        <i class="fas fa-plus"></i> 추가
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="board-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>이름</th>
                                            <th>학년</th>
                                            <th>반</th>
                                            <th>생년월일</th>
                                            <th style="width: 150px;">관리</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($participants))
                                            @foreach($participants as $index => $participant)
                                                <tr data-participant-id="{{ data_get($participant, 'id') }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="pv-name">{{ data_get($participant, 'name', '-') }}</td>
                                                    <td class="pv-grade">{{ data_get($participant, 'grade', '-') }}</td>
                                                    <td class="pv-class">{{ data_get($participant, 'class', '-') }}</td>
                                                    <td class="pv-birthday">
                                                        @php
                                                            $b = data_get($participant, 'birthday');
                                                            $bFormatted = $b ? \Carbon\Carbon::parse($b)->format('Y-m-d') : '-';
                                                        @endphp
                                                        {{ $bFormatted }}
                                                    </td>
                                                    <td>
                                                        <div class="board-btn-group">
                                                            <button type="button" class="btn btn-primary btn-sm" data-action="edit">수정</button>
                                                            <button type="button" class="btn btn-danger btn-sm" data-action="delete">삭제</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">등록된 신청 명단이 없습니다.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.group-applications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                            <button type="button" class="btn btn-danger" onclick="if(confirm('정말 삭제하시겠습니까?')) { document.getElementById('delete-form').submit(); }">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                            <a href="{{ route('print.estimate', ['id' => $applicationId]) }}" target="_blank" class="btn btn-dark">
                                <i class="fas fa-file-alt"></i> 견적서 출력
                            </a>
                        </div>
                    </form>

                    <form id="delete-form" method="POST" action="{{ route('backoffice.group-applications.destroy', $applicationId ?? 0) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.school-search')
@include('backoffice.modals.member-search', ['formAction' => route('backoffice.group-applications.search-members')])
@include('backoffice.modals.program-search', [
    'mode' => 'reservation',
    'searchAction' => route('backoffice.group-applications.search-programs'),
    'educationTypes' => data_get($formOptions, 'education_types', []),
    'modalTitle' => '프로그램 검색',
])
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/school-search.js') }}"></script>
<script src="{{ asset('js/backoffice/individual-application-search.js') }}"></script>
<script src="{{ asset('js/backoffice/group-application-roster.js') }}"></script>
@endsection



