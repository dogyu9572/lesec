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
        data-member-group-url="{{ route('backoffice.mail-sms.member-groups.members', ['memberGroup' => '__GROUP_ID__']) }}"
        data-csrf-token="{{ csrf_token() }}"></div>
    <div class="form-header">
        <a href="{{ route('backoffice.mail-sms.index') }}" class="btn btn-secondary btn-sm">
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
                    @php
                        $message = null;
                        $currentType = old('message_type', array_key_first($messageTypes));
                        $currentGroup = old('member_group_id', '');
                    @endphp

                    <form action="{{ route('backoffice.mail-sms.store') }}" method="POST" id="mailSmsForm">
                        @csrf

                        <div class="program-section">
                            <div class="section-title">기본 정보</div>
                            <div class="form-grid grid-2">
                                <div>
                                    <label>구분 <span class="text-danger">*</span></label>
                                    <div class="radio-group">
                                        @foreach($messageTypes as $value => $label)
                                            <label class="radio-label">
                                                <input type="radio" name="message_type" value="{{ $value }}" @checked($currentType === $value)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('message_type')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="member_group_id">회원 그룹</label>
                                    <select id="member_group_id" name="member_group_id">
                                        <option value="">선택</option>
                                        @foreach($memberGroups as $group)
                                            <option value="{{ $group->id }}" @selected((string)$currentGroup === (string)$group->id)>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('member_group_id')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                    <p class="text-muted small" id="memberGroupGuide">
                                        회원 그룹을 선택하면 해당 그룹의 전체 회원이 자동으로 선택되며, 개별 검색은 사용할 수 없습니다.
                                    </p>
                                </div>
                                <div class="form-group grid-span-2">
                                    <label>회원 <span class="text-danger">*</span></label>
                                    <div class="school-search-wrapper">
                                        <input type="text" id="selected_members_display" value="{{ $selectedMembers->pluck('member_name')->join(', ') }}" readonly placeholder="회원을 선택해주세요">
                                        <button type="button" id="openMemberSearchBtn" class="btn btn-secondary btn-sm" data-disabled-label="회원 그룹 선택 시 검색을 사용할 수 없습니다.">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                    <div class="text-muted small" id="memberSelectionStatus" style="margin-top: 6px;">
                                        회원 그룹을 선택하거나 검색 버튼을 통해 발송 대상을 지정해 주세요.
                                    </div>
                                    <div class="table-responsive" id="selectedMembersWrapper" style="margin-top: 10px;">
                                        <table class="board-table">
                                            <thead>
                                                <tr>
                                                    <th>이름</th>
                                                    <th>이메일</th>
                                                    <th>연락처</th>
                                                    <th style="width: 90px;">관리</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selectedMembersBody">
                                                @forelse($selectedMembers as $member)
                                                    @if($member->member_id)
                                                        <tr data-member-id="{{ $member->member_id }}">
                                                            <td>{{ $member->member_name }}</td>
                                                            <td>{{ $member->member_email ?? '-' }}</td>
                                                            <td>{{ $member->member_contact ?? '-' }}</td>
                                                            <td>
                                                                <div class="board-btn-group">
                                                                    <button type="button" class="btn btn-danger btn-sm selected-member-remove" data-member-id="{{ $member->member_id }}">삭제</button>
                                                                </div>
                                                                <input type="hidden" name="member_ids[]" value="{{ $member->member_id }}">
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    <tr class="selected-member-empty">
                                                        <td colspan="4" class="text-center">선택된 회원이 없습니다.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @error('member_ids')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group grid-span-2">
                                    <label for="title">제목 <span class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" value="{{ old('title', '') }}" maxlength="255">
                                    @error('title')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group grid-span-2">
                                    <label for="content">내용 <span class="text-danger">*</span></label>
                                    <textarea id="content" name="content" rows="10">{{ old('content', '') }}</textarea>
                                    @error('content')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.mail-sms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.member-search', ['selectionMode' => 'multiple'])
@endsection

@section('scripts')
<script>
    window.mailSmsFormConfig = {
        searchUrl: "{{ route('backoffice.mail-sms.search-members') }}",
        csrfToken: "{{ csrf_token() }}",
        groupMembersUrlTemplate: "{{ route('backoffice.mail-sms.member-groups.members', ['memberGroup' => '__GROUP_ID__']) }}"
    };
</script>
<script src="{{ asset('js/backoffice/mail-sms-form.js') }}"></script>
@endsection

