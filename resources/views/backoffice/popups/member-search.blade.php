@extends('backoffice.layouts.popup')

@section('popup-title', isset($groupName) && $groupName ? "그룹명 - {$groupName}" : '회원 검색')

@section('content')
@php
    $formAction = $formAction ?? null;
@endphp
<div class="member-search-filter">
    <form id="member-search-form" @if($formAction) action="{{ $formAction }}" method="GET" @endif data-member-search-url="{{ $formAction ?? route('backoffice.member-groups.search-members') }}">
        <div class="modal-form-row">
            <div class="modal-form-col">
                <div class="modal-form-group">
                    <label for="popup_member_type" class="modal-form-label">회원구분</label>
                    <select id="popup_member_type" name="member_type" class="modal-form-control">
                        <option value="">전체</option>
                        <option value="teacher">교사</option>
                        <option value="student">학생</option>
                    </select>
                </div>
            </div>
            <div class="modal-form-col">
                <div class="modal-form-group">
                    <label for="popup_search_type" class="modal-form-label">검색</label>
                    <select id="popup_search_type" name="search_type" class="modal-form-control">
                        <option value="all">전체</option>
                        <option value="login_id">아이디</option>
                        <option value="name">이름</option>
                        <option value="email">이메일</option>
                        <option value="school_name">학교</option>
                        <option value="contact">연락처</option>
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
                    <div style="display: flex; gap: 8px;">
                        <button type="button" id="popup-search-btn" class="btn btn-primary">검색</button>
                        <button type="button" id="popup-reset-btn" class="btn btn-secondary">뒤로가기</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="table-responsive">
    <table class="board-table">
        <thead>
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" id="popup-select-all">
                </th>
                <th>No</th>
                <th>ID</th>
                <th>이름</th>
                <th>학교</th>
                <th>이메일</th>
                <th>연락처</th>
            </tr>
        </thead>
        <tbody id="popup-member-list-body">
            <tr>
                <td colspan="7" class="text-center">검색어를 입력하거나 필터를 선택해주세요.</td>
            </tr>
        </tbody>
    </table>
</div>
<div id="popup-pagination" class="pagination-container" style="margin-top: 15px;"></div>
<div style="margin-top: 15px; text-align: right;">
    <button type="button" id="popup-add-btn" class="btn btn-primary" disabled>선택 완료</button>
    <button type="button" class="btn btn-secondary" onclick="window.close()" style="margin-left: 8px;">취소</button>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/member-search-popup.js') }}"></script>
@endsection

