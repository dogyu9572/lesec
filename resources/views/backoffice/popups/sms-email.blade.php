@extends('backoffice.layouts.popup')

@section('popup-title', '메일/SMS 발송')

@section('content')
@php
    $reservationIds = request('reservation_ids', []);
    if (is_string($reservationIds)) {
        $reservationIds = explode(',', $reservationIds);
    }
    $reservationId = request('reservation_id');
    $applicationType = request('application_type');
    $applicationIds = request('application_ids', '');
    $participantIds = request('participant_ids', '');
@endphp

<form id="sms-email-form">
    <input type="hidden" id="reservation-ids" value="{{ implode(',', $reservationIds) }}">
    @if($reservationId)
        <input type="hidden" id="reservation-id" value="{{ $reservationId }}">
    @endif
    @if($applicationType)
        <input type="hidden" id="application-type" value="{{ $applicationType }}">
    @endif
    @if($applicationIds)
        <input type="hidden" id="application-ids" value="{{ $applicationIds }}">
    @endif
    @if($participantIds)
        <input type="hidden" id="participant-ids" value="{{ $participantIds }}">
    @endif
    
    <div class="modal-form-group">
        <label class="modal-form-label">발송 유형</label>
        <div style="display: flex; gap: 20px; margin-top: 10px; align-items: center; flex-wrap: nowrap;">
            <label style="display: inline-flex; align-items: center; gap: 5px; margin: 0; cursor: pointer;">
                <input type="radio" name="message_type" value="email" checked style="margin: 0;">
                <span>EMAIL</span>
            </label>
            <label style="display: inline-flex; align-items: center; gap: 5px; margin: 0; cursor: pointer;">
                <input type="radio" name="message_type" value="sms" style="margin: 0;">
                <span>SMS</span>
            </label>
        </div>
    </div>
    
    <div class="modal-form-group" style="margin-top: 20px;">
        <label for="sms-email-content" class="modal-form-label">메시지 내용</label>
        <textarea id="sms-email-content" name="content" rows="12" class="modal-form-control" placeholder="메시지를 입력하세요" maxlength="2000" style="resize: vertical;"></textarea>
        <div class="text-right" style="margin-top: 5px;">
            <span id="sms-email-char-count">0</span> / <span id="sms-email-max-chars">2000</span>
        </div>
    </div>
    
    <div style="margin-top: 30px; text-align: right;">
        <button type="button" class="btn btn-secondary" onclick="window.close()">취소</button>
        <button type="button" id="sms-email-send-btn" class="btn btn-primary" style="margin-left: 8px;">발송</button>
    </div>
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/sms-email-popup.js') }}?v={{ time() }}"></script>
@endsection
