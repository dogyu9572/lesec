@extends('backoffice.layouts.app')

@section('title', '메일/SMS 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
@php
    $messageId = $message->id ?? 0;
@endphp
<div class="admin-form-container">
    <div id="member-search-config"
        data-member-search-url="{{ route('backoffice.mail-sms.search-members') }}"
        data-csrf-token="{{ csrf_token() }}"></div>
    <div class="form-header">
       
        <form action="{{ route('backoffice.mail-sms.send', $message) }}" method="POST" style="display: inline-block; margin-right: 10px;">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('정말 발송하시겠습니까?');">
                <i class="fas fa-paper-plane"></i> <span class="btn-text">발송</span>
            </button>
        </form>
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
                <div class="admin-card-body">
                    @php
                        $currentType = old('message_type', $message->message_type ?? array_key_first($messageTypes));
                        $currentGroup = old('member_group_id', $message->member_group_id ?? '');
                    @endphp

                    <form action="{{ route('backoffice.mail-sms.update', $message) }}" method="POST" id="mailSmsForm">
                        @csrf
                        @method('PUT')

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
                                    <div class="recipients-accordion" style="margin-top: 10px;">
                                        <div class="accordion-header" onclick="toggleRecipientsAccordion()">
                                            <span>수신자 목록</span>
                                            <span class="recipient-count">({{ $message->recipients()->count() }}명)</span>
                                            <i class="fas fa-chevron-down accordion-icon"></i>
                                        </div>
                                        <div class="accordion-content" id="recipientsContent" style="display: none;">
                                            <div id="recipientsTableContainer" style="margin-top: 15px;">
                                                <div class="text-center" style="padding: 40px;">
                                                    <i class="fas fa-spinner fa-spin"></i> 로딩 중...
                                                </div>
                                            </div>
                                            <div id="recipientsPagination" style="margin-top: 15px;"></div>
                                        </div>
                                    </div>
                                    <div class="table-responsive" id="selectedMembersWrapper" style="margin-top: 10px; display: none;">
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
                                    <input type="text" id="title" name="title" value="{{ old('title', $message->title ?? '') }}" maxlength="255">
                                    @error('title')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group grid-span-2">
                                    <label for="content">내용 <span class="text-danger">*</span></label>
                                    <textarea id="content" name="content" rows="10">{{ old('content', $message->content ?? '') }}</textarea>
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
                            <button type="button" class="btn btn-danger" onclick="if(confirm('정말 삭제하시겠습니까?')) { document.getElementById('delete-form').submit(); }">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                    </form>

                    <form id="delete-form" method="POST" action="{{ route('backoffice.mail-sms.destroy', $message->id) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.member-search')
@endsection

@section('scripts')
<script>
    window.mailSmsFormConfig = {
        searchUrl: "{{ route('backoffice.mail-sms.search-members') }}",
        csrfToken: "{{ csrf_token() }}",
        groupMembersUrlTemplate: "{{ route('backoffice.mail-sms.member-groups.members', ['memberGroup' => '__GROUP_ID__']) }}",
        recipientsUrl: "{{ route('backoffice.mail-sms.recipients', $message) }}",
        deleteRecipientUrl: "{{ route('backoffice.mail-sms.recipients.delete', ['mailSmsMessage' => $message, 'recipient' => '__RECIPIENT_ID__']) }}"
    };

    let recipientsLoaded = false;
    let currentRecipientsPage = 1;

    function toggleRecipientsAccordion() {
        const content = document.getElementById('recipientsContent');
        const icon = document.querySelector('.accordion-icon');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            if (icon) {
                icon.style.transform = 'rotate(180deg)';
            }
            
            if (!recipientsLoaded) {
                loadRecipients(1);
            }
        } else {
            content.style.display = 'none';
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    }

    function loadRecipients(page) {
        const container = document.getElementById('recipientsTableContainer');
        const paginationContainer = document.getElementById('recipientsPagination');
        
        if (!container) return;

        container.innerHTML = '<div class="text-center" style="padding: 40px;"><i class="fas fa-spinner fa-spin"></i> 로딩 중...</div>';
        paginationContainer.innerHTML = '';

        const url = window.mailSmsFormConfig.recipientsUrl + '?page=' + page;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            recipientsLoaded = true;
            currentRecipientsPage = page;
            renderRecipientsTable(data.recipients);
            renderRecipientsPagination(data.pagination);
        })
        .catch(error => {
            console.error('Error loading recipients:', error);
            container.innerHTML = '<div class="text-center text-danger" style="padding: 40px;">수신자 목록을 불러오는 중 오류가 발생했습니다.</div>';
        });
    }

    function renderRecipientsTable(recipients) {
        const container = document.getElementById('recipientsTableContainer');
        
        if (!recipients || recipients.length === 0) {
            container.innerHTML = '<div class="text-center" style="padding: 40px;">등록된 수신자가 없습니다.</div>';
            return;
        }

        let html = '<div class="table-responsive"><table class="board-table"><thead><tr>';
        html += '<th>이름</th>';
        html += '<th>이메일</th>';
        html += '<th>연락처</th>';
        html += '<th style="width: 90px;">관리</th>';
        html += '</tr></thead><tbody>';

        recipients.forEach(recipient => {
            html += '<tr data-recipient-id="' + recipient.id + '">';
            html += '<td>' + (recipient.member_name || '-') + '</td>';
            html += '<td>' + (recipient.member_email || '-') + '</td>';
            html += '<td>' + (recipient.member_contact || '-') + '</td>';
            html += '<td>';
            html += '<button type="button" class="btn btn-danger btn-sm delete-recipient-btn" data-recipient-id="' + recipient.id + '" onclick="deleteRecipient(' + recipient.id + ')">';
            html += '<i class="fas fa-trash"></i> 삭제';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function deleteRecipient(recipientId) {
        if (!confirm('정말 이 수신자를 삭제하시겠습니까?')) {
            return;
        }

        const url = window.mailSmsFormConfig.deleteRecipientUrl.replace('__RECIPIENT_ID__', recipientId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('수신자가 삭제되었습니다.');
                loadRecipients(currentRecipientsPage);
            } else {
                alert('삭제 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting recipient:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }

    function renderRecipientsPagination(pagination) {
        const container = document.getElementById('recipientsPagination');
        
        if (!pagination || pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        const current = pagination.current_page;
        const lastPage = pagination.last_page;
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(lastPage, current + 2);

        let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

        // 첫 페이지
        html += '<li class="page-item' + (current === 1 ? ' disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="1" onclick="event.preventDefault(); loadRecipients(1); return false;">';
        html += '<i class="fas fa-angle-double-left"></i></a></li>';

        // 이전 페이지
        html += '<li class="page-item' + (current === 1 ? ' disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (current > 1 ? current - 1 : 1) + '" onclick="event.preventDefault(); loadRecipients(' + (current > 1 ? current - 1 : 1) + '); return false;">';
        html += '<i class="fas fa-chevron-left"></i></a></li>';

        // 페이지 번호
        for (let i = startPage; i <= endPage; i++) {
            html += '<li class="page-item' + (i === current ? ' active' : '') + '">';
            html += '<a class="page-link" href="#" data-page="' + i + '" onclick="event.preventDefault(); loadRecipients(' + i + '); return false;">' + i + '</a></li>';
        }

        // 다음 페이지
        html += '<li class="page-item' + (current === lastPage ? ' disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (current < lastPage ? current + 1 : lastPage) + '" onclick="event.preventDefault(); loadRecipients(' + (current < lastPage ? current + 1 : lastPage) + '); return false;">';
        html += '<i class="fas fa-chevron-right"></i></a></li>';

        // 마지막 페이지
        html += '<li class="page-item' + (current === lastPage ? ' disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + lastPage + '" onclick="event.preventDefault(); loadRecipients(' + lastPage + '); return false;">';
        html += '<i class="fas fa-angle-double-right"></i></a></li>';

        html += '</ul></nav>';
        container.innerHTML = html;
    }
</script>
<script src="{{ asset('js/backoffice/mail-sms-form.js') }}"></script>
@endsection

