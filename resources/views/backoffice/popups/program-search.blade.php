@extends('backoffice.layouts.popup')

@section('popup-title', $modalTitle ?? '프로그램명 검색')

@section('content')
<div class="program-search-filter">
    <form id="program-search-form" method="GET" action="{{ $searchAction }}" data-program-search-mode="{{ $mode }}" data-program-search-url="{{ $searchAction }}">
        <div class="modal-form-row">
            <div class="modal-form-col">
                <div class="modal-form-group">
                    <label for="popup_education_type" class="modal-form-label">교육유형</label>
                    <select id="popup_education_type" name="education_type" class="modal-form-control">
                        <option value="">전체</option>
                        @foreach($educationTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-form-col">
                <div class="modal-form-group">
                    <label for="popup_program_keyword" class="modal-form-label">프로그램명</label>
                    <input type="text" id="popup_program_keyword" name="search_keyword" class="modal-form-control" placeholder="프로그램명 입력">
                </div>
            </div>
            <div class="modal-form-col search-button-col">
                <div class="modal-form-group">
                    <label class="modal-form-label search-button-label">버튼</label>
                    <button type="button" id="popup-program-search-btn" class="btn btn-primary">검색</button>
                </div>
            </div>
        </div>
    </form>
    @if($showDirectInputNotice ?? false)
        <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;" class="program-direct-input">
            <label for="popup_direct_program_name" style="display:block; font-size: 14px; color: #666; margin-bottom: 6px;">프로그램명이 목록에 없으면 직접 입력해 주세요.</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="popup_direct_program_name" class="modal-form-control" placeholder="프로그램명 직접 입력">
                <button type="button" id="popup-confirm-btn" class="btn btn-secondary btn-sm">확인</button>
            </div>
        </div>
    @endif
</div>
<div class="table-responsive">
    <table class="board-table">
        <thead>
            <tr>
                <th>No</th>
                <th>프로그램명</th>
                @if($mode === 'reservation')
                    <th>교육유형</th>
                    <th>교육일</th>
                    <th>참가비</th>
                @endif
                <th style="width: 80px;">선택</th>
            </tr>
        </thead>
        <tbody id="popup-program-list-body">
            <tr>
                <td colspan="{{ $mode === 'reservation' ? 6 : 3 }}" class="text-center">검색 조건을 입력해주세요.</td>
            </tr>
        </tbody>
    </table>
</div>
<div id="popup-program-pagination" class="board-pagination" style="margin-top: 15px;"></div>
@if($mode === 'reservation' && ($showFooter ?? false))
    <div style="margin-top: 15px; text-align: right;">
        <button type="button" id="popup-program-confirm" class="btn btn-primary" disabled>{{ $confirmButtonLabel ?? '확인' }}</button>
        <button type="button" class="btn btn-secondary" onclick="window.close()" style="margin-left: 8px;">취소</button>
    </div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/program-search-popup.js') }}"></script>
@endsection

