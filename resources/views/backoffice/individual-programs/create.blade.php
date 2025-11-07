@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/group-programs.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('title', '개인 프로그램 등록')

@section('content')
@if ($errors->any())
    <div class="board-alert board-alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.individual-programs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>개인 프로그램 등록</h6>
        </div>
        <div class="board-card-body">
            <form action="{{ route('backoffice.individual-programs.store') }}" method="POST">
                @csrf

                <!-- 개인 프로그램 -->
                <div class="program-section">
                    <div class="section-title">개인 프로그램</div>

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

                <!-- 신청유형 -->
                <div class="board-form-group">
                    <label for="reception_type" class="board-form-label">신청유형 <span class="required">*</span></label>
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
                            <label for="education_start_date" class="board-form-label">참가일정 시작일 <span class="required">*</span></label>
                            <input type="date" class="board-form-control @error('education_start_date') is-invalid @enderror" 
                                   id="education_start_date" name="education_start_date" value="{{ old('education_start_date') }}" required>
                            @error('education_start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="education_end_date" class="board-form-label">참가일정 종료일 <span class="required">*</span></label>
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

                <!-- 네이버폼 링크 (신청유형이 네이버폼일 때만 표시) -->
                <div class="board-form-group" id="naver_form_url_group" style="display: none;">
                    <label for="naver_form_url" class="board-form-label">네이버폼 링크 <span class="required">*</span></label>
                    <input type="url" class="board-form-control @error('naver_form_url') is-invalid @enderror" 
                           id="naver_form_url" name="naver_form_url" value="{{ old('naver_form_url') }}" 
                           placeholder="https://naver.me/...">
                    @error('naver_form_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="board-form-text">신청유형이 '네이버폼'인 경우 필수 입력입니다.</small>
                </div>

                <!-- 대기자 신청 링크 (신청유형이 선착순일 때만 표시) -->
                <div class="board-form-group" id="waitlist_url_group" style="display: none;">
                    <label for="waitlist_url" class="board-form-label">대기자 신청 링크</label>
                    <input type="url" class="board-form-control @error('waitlist_url') is-invalid @enderror" 
                           id="waitlist_url" name="waitlist_url" value="{{ old('waitlist_url') }}" 
                           placeholder="https://...">
                    @error('waitlist_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="board-form-text">신청유형이 '선착순'인 경우 대기자 화면 이동이 필요할 때 입력합니다.</small>
                </div>
                </div>

                <!-- 접수정보 -->
                <div class="program-section">
                    <div class="section-title">접수정보</div>

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
                    <label for="capacity" class="board-form-label">신청 정원</label>
                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-6">
                            <input type="number" class="board-form-control @error('capacity') is-invalid @enderror" 
                                   id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" 
                                   @if(old('is_unlimited_capacity') || old('reception_type') == 'lottery') disabled @endif>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="board-form-col board-form-col-6">
                            <div class="board-checkbox-item">
                                <input type="checkbox" id="is_unlimited_capacity" name="is_unlimited_capacity" value="1" 
                                       class="board-checkbox-input" @checked(old('is_unlimited_capacity')) 
                                       @if(old('reception_type') == 'lottery') disabled @endif>
                                <label for="is_unlimited_capacity">제한없음</label>
                            </div>
                        </div>
                    </div>
                    <small class="board-form-text">신청유형이 '추첨'인 경우 제한없음을 사용할 수 없습니다.</small>
                </div>

                <!-- 교육비 -->
                <div class="board-form-group">
                    <label for="education_fee" class="board-form-label">참가비</label>
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
                    <a href="{{ route('backoffice.individual-programs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 취소
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 프로그램명 검색 모달 -->
<div id="program-search-modal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay" onclick="closeProgramSearchModal()"></div>
    <div class="category-modal-content" style="max-width: 800px;">
        <div class="category-modal-header">
            <h5>프로그램명 검색</h5>
            <button type="button" class="category-modal-close" onclick="closeProgramSearchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="member-search-filter">
                <form id="program-search-form" method="GET" action="{{ route('backoffice.individual-programs.search-programs') }}">
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_education_type" class="modal-form-label">교육유형</label>
                                <select id="popup_education_type" name="education_type" class="modal-form-control">
                                    <option value="">전체</option>
                                    @foreach($educationTypes as $key => $name)
                                        <option value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_search_keyword" class="modal-form-label">검색어</label>
                                <input type="text" id="popup_search_keyword" name="search_keyword" class="modal-form-control" placeholder="검색어 입력">
                            </div>
                        </div>
                        <div class="modal-form-col search-button-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label search-button-label">버튼</label>
                                <button type="button" id="popup-search-btn" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
                    <span style="font-size: 14px; color: #666;">프로그램명이 없을 경우 입력해주세요.</span>
                    <button type="button" id="popup-confirm-btn" class="btn btn-secondary btn-sm" style="margin-left: 10px;">확인</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>프로그램명</th>
                            <th>선택</th>
                        </tr>
                    </thead>
                    <tbody id="popup-program-list-body">
                        <tr>
                            <td colspan="3" class="text-center">검색어를 입력하거나 필터를 선택해주세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="popup-pagination" class="pagination-container"></div>
        </div>
        <div class="category-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProgramSearchModal()">취소</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/individual-program-form.js') }}"></script>
@endsection

