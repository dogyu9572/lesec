@extends('backoffice.layouts.app')

@section('title', '메일/SMS 등록')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
<div class="admin-form-container">
    <div id="member-search-config"
        data-member-search-url="{{ route('backoffice.mail-sms.search-members') }}"
        data-csrf-token="{{ csrf_token() }}"></div>
    <div class="form-header">
        <a href="{{ route('backoffice.mail-sms.index') }}" class="btn btn-secondary">
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
                <div class="admin-card-header">
                    <h6>메일/SMS 관리</h6>
                </div>
                <div class="admin-card-body">
                    @include('backoffice.mail-sms.partials.form', [
                        'formAction' => route('backoffice.mail-sms.store'),
                        'method' => 'POST',
                        'message' => null,
                        'messageTypes' => $messageTypes,
                        'memberGroups' => $memberGroups,
                        'selectedMembers' => $selectedMembers,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.member-search', ['selectionMode' => 'multiple'])
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/mail-sms-form.js') }}"></script>
@endsection

