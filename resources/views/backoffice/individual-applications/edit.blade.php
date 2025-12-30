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
                            <div class="section-title">프로그램 정보</div>
                            <div class="form-grid grid-2">
                                <div class="form-group">
                                    <label for="reception_type">신청유형</label>
                                    <select id="reception_type" name="reception_type" required>
                                        @foreach($receptionTypes as $key => $name)
                                            <option value="{{ $key }}" @selected($application->reception_type == $key)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('reception_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label>교육유형</label>
                                    <div class="radio-group">
                                        @foreach($educationTypes as $key => $name)
                                            <label class="radio-label">
                                                <input type="radio" name="education_type" value="{{ $key }}" @checked($application->education_type == $key) required>
                                                <span>{{ $name }}</span>
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
                                        <input type="hidden" id="program_reservation_id" name="program_reservation_id" value="{{ $application->program_reservation_id }}">
                                        <input type="text" id="program_name" name="program_name" value="{{ $application->program_name }}" readonly>
                                        <button type="button" id="program-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="participation_date_display">참가일</label>
                                    <input type="hidden" id="participation_date" name="participation_date" value="{{ optional($application->participation_date)->format('Y-m-d') }}">
                                    <input type="text" id="participation_date_display" value="{{ optional($application->participation_date)->format('Y.m.d') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="participation_fee_display">참가비</label>
                                    <input type="hidden" id="participation_fee" name="participation_fee" value="{{ $application->participation_fee ?? '' }}">
                                    <input type="text" id="participation_fee_display" value="{{ $application->participation_fee ? number_format($application->participation_fee) : '' }}" readonly>
                                </div>
                                <div>
                                    <label>결제방법</label>
                                    <div class="radio-group">
                                        @foreach($paymentMethods as $key => $name)
                                            <label class="radio-label">
                                                <input type="radio" name="payment_method" value="{{ $key }}" @checked($application->payment_method == $key)>
                                                <span>{{ $name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('payment_method')
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
                                    <input type="text" id="application_number" value="{{ $application->application_number }}" readonly>
                                </div>
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
@include('backoffice.modals.member-search', ['selectionMode' => 'single', 'formAction' => route('backoffice.member-groups.search-members')])
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
