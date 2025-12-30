@extends('backoffice.layouts.app')

@section('title', '개인 신청 내역 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
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
                                        <input type="text" id="program_name" value="{{ $application->program_name }}" readonly>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>검색</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="participation_date">참가일</label>
                                    <input type="text" id="participation_date" value="{{ optional($application->participation_date)->format('Y.m.d') ?? '' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="participation_fee">참가비</label>
                                    <input type="text" id="participation_fee" value="{{ $application->participation_fee ? number_format($application->participation_fee) : '0' }}" readonly>
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
                                    <label for="draw_result">추첨결과</label>
                                    <select id="draw_result" name="draw_result" required>
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
                                        <input type="text" id="applicant_name" value="{{ $application->applicant_name }}" readonly>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>회원 검색</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="applicant_school_name">학교/학교</label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="applicant_school_name" value="{{ $application->applicant_school_name ?? '' }}" readonly>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>검색</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>학년/반</label>
                                    <div class="grade-class-wrapper">
                                        <div class="grade-class-item">
                                            <select id="applicant_grade" name="applicant_grade" disabled>
                                                <option value="">학년</option>
                                                @for($i = 1; $i <= 3; $i++)
                                                    <option value="{{ $i }}" @selected($application->applicant_grade == $i)>{{ $i }}학년</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="grade-class-item">
                                            <select id="applicant_class" name="applicant_class" disabled>
                                                <option value="">반</option>
                                                @for($i = 1; $i <= 20; $i++)
                                                    <option value="{{ $i }}" @selected($application->applicant_class == $i)>{{ $i }}반</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>연락처</label>
                                    <div class="grade-class-wrapper">
                                        <div class="grade-class-item">
                                            <input type="text" value="{{ $application->applicant_contact }}" readonly>
                                        </div>
                                        <div class="grade-class-item">
                                            <input type="text" value="{{ $application->guardian_contact ?? '' }}" readonly>
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
@endsection
