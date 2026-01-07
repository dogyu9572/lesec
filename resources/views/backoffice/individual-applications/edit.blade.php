@extends('backoffice.layouts.app')

@section('title', '개인 신청 내역 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
@php
    $drawResultValue = $application->draw_result ?? \App\Models\IndividualApplication::DRAW_RESULT_PENDING;
@endphp
<div class="admin-form-container">
    <div class="form-header">      
        <a href="{{ route('backoffice.individual-applications.index') }}" class="btn btn-secondary">
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
                    <form method="POST" action="{{ route('backoffice.individual-applications.update', $application) }}">
                        @csrf
                        @method('PUT')
                       

                        <div class="program-section">
                            <div class="section-title">신청 정보</div>
                            <div class="form-grid grid-2">
                                <div class="form-group">
                                    <label for="draw_result_select">추첨결과</label>
                                    <input type="hidden" id="draw_result" name="draw_result" value="{{ $drawResultValue }}">
                                    <select id="draw_result_select" class="form-control" @if($application->reception_type !== 'lottery') disabled @endif>
                                        <option value="">-</option>
                                        @foreach($drawResults as $key => $name)
                                            <option value="{{ $key }}" @selected($application->draw_result == $key)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('draw_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="applicant_name">신청자명</label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="applicant_name" name="applicant_name" value="{{ $application->applicant_name }}" readonly>
                                        <input type="hidden" id="member_id" name="member_id" value="{{ $application->member_id }}">
                                        <button type="button" id="member-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 회원 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="school_name">학교/학교</label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="school_name" name="applicant_school_name" value="{{ $application->applicant_school_name ?? '' }}" readonly>
                                        <input type="hidden" id="school_id" value="">
                                        <button type="button" id="school-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>학년/반</label>
                                    <div class="grade-class-wrapper">
                                        <div class="grade-class-item">
                                            <input type="hidden" id="applicant_grade" name="applicant_grade" value="{{ $application->applicant_grade }}">
                                            <input type="text" id="applicant_grade_display" value="{{ $application->applicant_grade ? $application->applicant_grade . '학년' : '' }}" readonly>
                                        </div>
                                        <div class="grade-class-item">
                                            <input type="hidden" id="applicant_class" name="applicant_class" value="{{ $application->applicant_class }}">
                                            <input type="text" id="applicant_class_display" value="{{ $application->applicant_class ? $application->applicant_class . '반' : '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>연락처</label>
                                    <div class="grade-class-wrapper contact-wrapper">
                                        <div class="grade-class-item">
                                            <input type="text" id="applicant_contact" name="applicant_contact" value="{{ $application->applicant_contact }}" readonly>
                                        </div>
                                        <div class="grade-class-item">
                                            <input type="text" id="guardian_contact" name="guardian_contact" value="{{ $application->guardian_contact ?? '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label>결제상태</label>
                                    <div class="radio-group">
                                        @foreach($paymentStatuses as $key => $name)
                                            <label class="radio-label">
                                                <input type="radio" name="payment_status" value="{{ $key }}" @checked($application->payment_status == $key) required>
                                                <span>{{ $name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('payment_status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="applied_at">신청일시</label>
                                    <input type="text" id="applied_at" value="{{ optional($application->applied_at)->format('Y.m.d H:i') ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.individual-applications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                            <button type="button" class="btn btn-danger" onclick="if(confirm('정말 삭제하시겠습니까?')) { document.getElementById('delete-form').submit(); }">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                    </form>

                    <!-- 삭제 폼 -->
                    <form id="delete-form" method="POST" action="{{ route('backoffice.individual-applications.destroy', $application) }}" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.school-search')
@include('backoffice.modals.member-search', ['formAction' => route('backoffice.member-groups.search-members')])
@include('backoffice.modals.program-search', [
    'mode' => 'reservation',
    'searchAction' => route('backoffice.individual-applications.search-programs'),
    'educationTypes' => $educationTypes ?? [],
    'modalTitle' => '프로그램 검색',
    'showDirectInputNotice' => true,
    'programInputId' => 'program_name',
    'showFooter' => false,
])

@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/school-search.js') }}"></script>
<script src="{{ asset('js/backoffice/individual-application-search.js') }}"></script>
@endsection
