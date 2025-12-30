@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/group-programs.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('title', '단체 프로그램 등록')

@section('content')

<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.group-programs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
<div class="board-card-body">
            <form action="{{ route('backoffice.group-programs.store') }}" method="POST">
                @csrf

                <!-- 단체 프로그램 -->
                <div class="program-section">
                    <div class="section-title">단체 프로그램</div>

                    <!-- 교육유형 -->
                <div class="board-form-group">
                    <label class="board-form-label">교육유형 <span class="required">*</span></label>
                    <div class="board-radio-group">
                        @foreach($educationTypes as $key => $name)
                            <div class="board-radio-item">
                                <input type="radio" id="education_type_{{ $key }}" name="education_type" value="{{ $key }}" 
                                       class="board-radio-input" @checked(old('education_type') == $key || ($loop->first && !old('education_type'))) required>
                                <label for="education_type_{{ $key }}">{{ $name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('education_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 프로그램명 -->
                <div class="board-form-group">
                    <label for="program_name" class="board-form-label">프로그램명 <span class="required">*</span></label>
                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-6">
                            <input type="text" class="board-form-control @error('program_name') is-invalid @enderror" 
                                   id="program_name" name="program_name" value="{{ old('program_name') }}" required>
                            @error('program_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="board-form-col board-form-col-6">
                            <button type="button" class="btn btn-secondary" id="searchProgramBtn">
                                <i class="fas fa-search"></i> 검색
                            </button>
                        </div>
                    </div>
                    <small class="board-form-text">프로그램명 검색 버튼을 클릭하면 기존 프로그램명을 검색할 수 있습니다.</small>
                </div>

                <!-- 교육일정 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="education_start_date" class="board-form-label">교육 시작일 <span class="required">*</span></label>
                            <input type="date" class="board-form-control @error('education_start_date') is-invalid @enderror" 
                                   id="education_start_date" name="education_start_date" value="{{ old('education_start_date') }}" required>
                            @error('education_start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="education_end_date" class="board-form-label">교육 종료일 <span class="required">*</span></label>
                            <input type="date" class="board-form-control @error('education_end_date') is-invalid @enderror" 
                                   id="education_end_date" name="education_end_date" value="{{ old('education_end_date') }}" required>
                            @error('education_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 결제수단 -->
                <div class="board-form-group">
                    <label class="board-form-label">결제수단 <span class="required">*</span></label>
                    <div class="board-checkbox-group">
                        @foreach($paymentMethods as $key => $name)
                            <div class="board-checkbox-item">
                                <input type="checkbox" id="payment_method_{{ $key }}" name="payment_methods[]" value="{{ $key }}" 
                                       class="board-checkbox-input" @checked(in_array($key, old('payment_methods', [])))>
                                <label for="payment_method_{{ $key }}">{{ $name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('payment_methods')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                </div>

                <!-- 접수정보 -->
                <div class="program-section">
                    <div class="section-title">접수정보</div>

                    <!-- 접수유형 -->
                <div class="board-form-group">
                    <label for="reception_type" class="board-form-label">접수유형 <span class="required">*</span></label>
                    <select class="board-form-control @error('reception_type') is-invalid @enderror" 
                            id="reception_type" name="reception_type" required>
                        <option value="">선택하세요</option>
                        @foreach($receptionTypes as $key => $name)
                            <option value="{{ $key }}" @selected(old('reception_type') == $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('reception_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 신청기간 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="application_start_date" class="board-form-label">신청 시작일 <span class="required">*</span></label>
                            <input type="date" class="board-form-control @error('application_start_date') is-invalid @enderror" 
                                   id="application_start_date" name="application_start_date" value="{{ old('application_start_date') }}" required>
                            @error('application_start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="application_end_date" class="board-form-label">신청 종료일 <span class="required">*</span></label>
                            <input type="date" class="board-form-control @error('application_end_date') is-invalid @enderror" 
                                   id="application_end_date" name="application_end_date" value="{{ old('application_end_date') }}" required>
                            @error('application_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 신청정원 -->
                <div class="board-form-group">
                    <label for="capacity" class="board-form-label">신청정원</label>
                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-6">
                            <input type="number" class="board-form-control @error('capacity') is-invalid @enderror" 
                                   id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" 
                                   @if(old('is_unlimited_capacity')) disabled @endif>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="board-form-col board-form-col-6">
                            <div class="board-checkbox-item">
                                <input type="checkbox" id="is_unlimited_capacity" name="is_unlimited_capacity" value="1" 
                                       class="board-checkbox-input" @checked(old('is_unlimited_capacity'))>
                                <label for="is_unlimited_capacity">제한없음</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 교육비 -->
                <div class="board-form-group">
                    <label for="education_fee" class="board-form-label">교육비</label>
                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-6">
                            <input type="number" class="board-form-control @error('education_fee') is-invalid @enderror" 
                                   id="education_fee" name="education_fee" value="{{ old('education_fee') !== null ? (int) old('education_fee') : '' }}" min="0" step="1"
                                   @if(old('is_free')) disabled @endif
                                   style="display: inline-block; width: calc(100% - 50px);">
                            <span style="display: inline-block; margin-left: 10px; line-height: 38px;">원</span>
                            @error('education_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="board-form-col board-form-col-6">
                            <div class="board-checkbox-item">
                                <input type="checkbox" id="is_free" name="is_free" value="1" 
                                       class="board-checkbox-input" @checked(old('is_free'))>
                                <label for="is_free">무료</label>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.group-programs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 취소
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('backoffice.modals.program-search', [
    'mode' => 'name',
    'searchAction' => route('backoffice.group-programs.search-programs'),
    'educationTypes' => $educationTypes,
    'showDirectInputNotice' => true,
    'programInputId' => 'program_name',
])
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/group-program-form.js') }}"></script>
@endsection
