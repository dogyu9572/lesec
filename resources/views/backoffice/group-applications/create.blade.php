@extends('backoffice.layouts.app')

@section('title', '단체 신청 내역 등록')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
@php
    $generalApplicationError = $errors->first('application');
    $otherErrors = collect($errors->getMessages())->except('application')->flatten();
    $oldParticipationDate = old('participation_date');
    $participationDateDisplay = $oldParticipationDate ? \Carbon\Carbon::parse($oldParticipationDate)->format('Y.m.d') : '';
    $oldParticipationFee = old('participation_fee');
    $participationFeeDisplay = $oldParticipationFee !== null && $oldParticipationFee !== '' ? number_format((int) $oldParticipationFee) : '';
    $oldApplicantCount = old('applicant_count', 1);
@endphp
<div class="admin-form-container">
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

    @if(!empty($generalApplicationError))
        <div class="alert alert-danger">
            {{ $generalApplicationError }}
        </div>
    @endif

    @if($otherErrors->isNotEmpty())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($otherErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div id="application-search-config"
                         data-member-search-url="{{ route('backoffice.group-applications.search-members') }}"
                         data-program-search-url="{{ route('backoffice.group-applications.search-programs') }}">
                    </div>
                    <form method="POST" action="{{ route('backoffice.group-applications.store') }}">
                        @csrf

                        <div class="program-section">
                            <div class="section-title">프로그램 정보</div>
                            <div class="form-grid grid-2">
                                <div>
                                    <label>교육유형</label>
                                    <div class="radio-group">
                                        @foreach(data_get($formOptions, 'education_types', []) as $value => $label)
                                            <label class="radio-label">
                                                <input type="radio" name="education_type" value="{{ $value }}" @checked(old('education_type') === $value)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('education_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group grid-span-2">
                                    <label for="program_name">프로그램명</label>
                                    <div class="school-search-wrapper">
                                        <input type="hidden" id="program_reservation_id" name="program_reservation_id" value="{{ old('program_reservation_id') }}">
                                        <input type="text" id="program_name" name="program_name" value="{{ old('program_name') }}" readonly>
                                        <button type="button" id="program-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                    @error('program_reservation_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="participation_date_display">참가일</label>
                                    <input type="hidden" id="participation_date" name="participation_date" value="{{ old('participation_date') }}">
                                    <input type="text" id="participation_date_display" value="{{ $participationDateDisplay }}" readonly>
                                    @error('participation_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="participation_fee_display">참가비</label>
                                    <input type="hidden" id="participation_fee" name="participation_fee" value="{{ old('participation_fee') }}">
                                    <input type="text" id="participation_fee_display" value="{{ $participationFeeDisplay }}" readonly>
                                    @error('participation_fee')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label>결제방법</label>
                                    <div class="checkbox-group">
                                        @foreach(data_get($formOptions, 'payment_methods', []) as $value => $label)
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="payment_methods[]" value="{{ $value }}" @checked(in_array($value, old('payment_methods', []), true))>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('payment_methods')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('payment_methods.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title">신청 정보</div>
                            <div class="form-grid grid-2">
                                <div class="form-group">
                                    <label for="application_number">신청번호</label>
                                    <input type="text" id="application_number" value="저장 시 자동 생성" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="application_status">신청상태</label>
                                    <select id="application_status" name="application_status">
                                        @foreach(data_get($formOptions, 'application_statuses', []) as $value => $label)
                                            <option value="{{ $value }}" @selected(old('application_status', 'pending') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('application_status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="applicant_name">신청자명</label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" readonly>
                                        <input type="hidden" id="member_id" name="member_id" value="{{ old('member_id') }}">
                                        <button type="button" id="member-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 회원 검색
                                        </button>
                                    </div>
                                    @error('applicant_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="applicant_contact">연락처</label>
                                    <input type="text" id="applicant_contact" name="applicant_contact" value="{{ old('applicant_contact') }}">
                                    @error('applicant_contact')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="school_level">학교급</label>
                                    <input type="text" id="school_level" name="school_level" value="{{ old('school_level') }}">
                                    @error('school_level')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="school_name">학교명</label>
                                    <div class="school-search-wrapper">
                                        <input type="hidden" id="school_id" value="{{ old('school_id') }}">
                                        <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" readonly>
                                        <button type="button" id="school-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                    @error('school_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="applicant_count">신청인원</label>
                                    <input type="number" id="applicant_count" name="applicant_count" min="1" value="{{ $oldApplicantCount }}">
                                    @error('applicant_count')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label>결제상태</label>
                                    <div class="radio-group">
                                        @foreach(data_get($formOptions, 'payment_statuses', []) as $value => $label)
                                            <label class="radio-label">
                                                <input type="radio" name="payment_status" value="{{ $value }}" @checked(old('payment_status', 'unpaid') === $value)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('payment_status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.group-applications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.school-search')
@include('backoffice.modals.member-search', ['selectionMode' => 'single', 'formAction' => route('backoffice.member-groups.search-members')])
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
@endsection


