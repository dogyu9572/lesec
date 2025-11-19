@extends('backoffice.layouts.app')

@section('title', '명단 관리(상세)')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/rosters.css') }}">
@endsection

@section('content')
<div class="board-container users-page rosters-page">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    <div class="board-page-header roster-header">
        <div class="board-page-buttons roster-header-right">           
            <a href="{{ route('backoffice.rosters.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>명단 관리(상세)</h6>
            </div>
        </div>
        <div class="board-card-body">
            @php
                $programInfo = [
                    '신청구분' => $program['application_type'] === 'individual' ? '개인' : '단체',
                    '신청유형' => ($program['application_type'] === 'individual' && isset($program['reception_type']))
                        ? ($filters['reception_types'][$program['reception_type']] ?? '-')
                        : '-',
                    '교육유형' => $filters['education_types'][$program['education_type']] ?? '-',
                    '프로그램명' => $program['program_name'],
                    '참가일' => $program['participation_date_formatted'] ?? '-',
                    '정원' => $program['is_unlimited_capacity'] ? '무제한' : ($program['capacity'] ?? '-'),
                    '신청인원' => $program['applied_count'] ?? 0,
                ];
            @endphp
            <div class="program-section">
                <div class="section-title">1. 프로그램 정보</div>
                <dl class="program-info-list">
                    @foreach($programInfo as $label => $value)
                        <div class="program-info-row">
                            <dt>{{ $label }}</dt>
                            <dd>{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="program-section">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <span>2. 신청명단
                        @if($program['application_type'] === 'individual')
                            @if($program['reception_type'] === 'first_come')
                                (선착순)
                            @elseif($program['reception_type'] === 'lottery')
                                (추첨)
                            @elseif($program['reception_type'] === 'naver_form')
                                (네이버폼)
                            @endif
                        @else
                            (단체)
                        @endif
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        @if($program['application_type'] === 'individual' && $program['reception_type'] === 'lottery')
                            <div class="roster-filter-row">
                                <select id="draw-result-filter" class="filter-select roster-filter-select">
                                    <option value="">전체</option>
                                    <option value="win">당첨</option>
                                    <option value="fail">미당첨</option>
                                    <option value="waitlist">대기</option>
                                </select>
                                <button type="button" id="search-btn" class="btn btn-primary roster-filter-submit" data-skip-button>
                                    <i class="fas fa-search"></i> 검색
                                </button>
                            </div>
                        @endif
                        @if($program['application_type'] === 'individual')
                        <button type="button" id="sms-email-btn" class="btn btn-info" data-skip-button>
                            <i class="fas fa-envelope"></i> SMS/메일 발송
                        </button>
                        @endif
                        <button type="button" id="download-btn" class="btn btn-dark" data-skip-button>
                            <i class="fas fa-download"></i> 다운로드
                        </button>
                        @if($program['application_type'] === 'individual' && $program['reception_type'] === 'lottery')
                        <button type="button" id="lottery-btn" class="btn btn-primary" data-skip-button @if(empty($lotteryStatus) || !$lotteryStatus['is_pending']) disabled @endif>
                            <i class="fas fa-random"></i> {{ (isset($lotteryStatus) && $lotteryStatus['is_pending']) ? '추첨' : '추첨 완료' }}
                        </button>
                    @endif           
                    </div>
                </div>

                @if($rosterList->count() > 0)
                    <div class="table-responsive">
                        <table class="board-table">
                            <thead>
                                <tr>
                                    <th class="roster-checkbox-column"><input type="checkbox" id="select-all-checkbox"></th>
                                    <th>No</th>
                                    @if($program['application_type'] === 'individual')
                                        <th>신청번호</th>
                                        <th>신청자명</th>
                                        <th>학교명</th>
                                        <th>학년</th>
                                        <th>반</th>
                                        <th>생년월일</th>
                                        <th>성별</th>
                                        <th>결제상태</th>
                                        @if($program['reception_type'] === 'lottery')
                                            <th>추첨결과</th>
                                        @endif
                                        <th>신청일시</th>
                                        <th class="roster-table-actions">관리</th>
                                    @else
                                        <th>이름</th>
                                        <th>학교</th>
                                        <th>학년</th>
                                        <th>반</th>
                                        <th>생년월일</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rosterList as $index => $item)
                                    <tr data-row-index="{{ $index }}"
                                        @if($program['application_type'] === 'individual' && isset($item['draw_result']))
                                            data-draw-result="{{ $item['draw_result'] }}"
                                        @endif>
                                        <td><input type="checkbox" class="roster-checkbox" value="{{ $item['id'] }}"></td>
                                        <td>{{ $rosterList->count() - $index }}</td>
                                        @if($program['application_type'] === 'individual')
                                            <td>{{ $item['application_number'] ?? '-' }}</td>
                                            <td>{{ $item['applicant_name'] ?? '-' }}</td>
                                            <td>{{ $item['school_name'] ?? '-' }}</td>
                                            <td>{{ $item['grade'] ?? '-' }}</td>
                                            <td>{{ $item['class'] ?? '-' }}</td>
                                            <td>{{ $item['birthday_formatted'] ?? '-' }}</td>
                                            <td>{{ $item['gender'] ?? '-' }}</td>
                                            <td>{{ $item['payment_status_label'] ?? '-' }}</td>
                                            @if($program['reception_type'] === 'lottery')
                                                <td>{{ $item['draw_result_label'] ?? '-' }}</td>
                                            @endif
                                            <td>{{ $item['applied_at_formatted'] ?? '-' }}</td>
                                            <td class="roster-table-actions">
                                                <a href="{{ route('backoffice.individual-applications.edit', $item['id']) }}" class="btn btn-primary btn-sm">
                                                    보기
                                                </a>
                                            </td>
                                        @else
                                            <td>{{ $item['name'] ?? '-' }}</td>
                                            <td>{{ $item['school_name'] ?? '-' }}</td>
                                            <td>{{ $item['grade'] ?? '-' }}</td>
                                            <td>{{ $item['class'] ?? '-' }}</td>
                                            <td>{{ $item['birthday_formatted'] ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-data">
                        <p>등록된 명단이 없습니다.</p>
                    </div>
                @endif
            </div>

            <div class="form-actions">
                <button type="button" id="save-btn" class="btn btn-primary" data-skip-button>
                    <i class="fas fa-save"></i> 저장
                </button>
                <a href="{{ route('backoffice.rosters.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 취소
                </a>
                <button type="button" id="delete-btn" class="btn btn-danger" data-skip-button>
                    <i class="fas fa-trash"></i> 삭제
                </button>
            </div>
        </div>
    </div>
</div>

<div id="lottery-confirm-modal" class="roster-modal">
    <div class="roster-modal__dialog">
        <h5 class="roster-modal__title">추첨 확인</h5>
        <p class="roster-modal__body">추첨 시 당첨, 미당첨 여부가 결정됩니다. 추첨을 진행하시겠습니까?</p>
        <div class="roster-modal__actions">
            <button type="button" id="lottery-confirm-cancel" class="btn btn-secondary" data-skip-button>취소</button>
            <button type="button" id="lottery-confirm-submit" class="btn btn-primary" data-skip-button>확인</button>
        </div>
    </div>
</div>

<div id="download-modal" class="roster-modal">
    <div class="roster-modal__dialog roster-modal__dialog--wide">
        <h5 class="roster-modal__title">다운로드 설정</h5>
        <div class="roster-modal__body">
            <div class="roster-download-section-title">
                <strong>다운로드할 항목 선택:</strong>
            </div>
            <div class="roster-column-select-actions">
                <button type="button" id="select-all-columns" class="btn btn-sm btn-secondary">전체 선택</button>
                <button type="button" id="deselect-all-columns" class="btn btn-sm btn-secondary">전체 해제</button>
            </div>
            <div id="column-checkboxes" class="roster-column-checkboxes">
                @if($program['application_type'] === 'individual')
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="application_number" checked>
                        <span>신청번호</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="applicant_name" checked>
                        <span>신청자명</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="school_name" checked>
                        <span>학교명</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="grade" checked>
                        <span>학년</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="class" checked>
                        <span>반</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="birthday" checked>
                        <span>생년월일</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="gender" checked>
                        <span>성별</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="payment_status" checked>
                        <span>결제상태</span>
                    </label>
                    @if($program['reception_type'] === 'lottery')
                        <label class="roster-column-checkbox-label">
                            <input type="checkbox" class="column-checkbox" value="draw_result" checked>
                            <span>추첨결과</span>
                        </label>
                    @endif
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="applied_at" checked>
                        <span>신청일시</span>
                    </label>
                @else
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="name" checked>
                        <span>이름</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="school_name" checked>
                        <span>학교</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="grade" checked>
                        <span>학년</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="class" checked>
                        <span>반</span>
                    </label>
                    <label class="roster-column-checkbox-label">
                        <input type="checkbox" class="column-checkbox" value="birthday" checked>
                        <span>생년월일</span>
                    </label>
                @endif
            </div>
        </div>
        <div class="roster-modal__actions">
            <button type="button" id="download-modal-cancel" class="btn btn-secondary" data-skip-button>취소</button>
            <button type="button" id="download-modal-submit" class="btn btn-primary" data-skip-button>다운로드</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/roster-detail.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reservationId = {{ $program['id'] }};
        const lotteryUrl = '{{ route('backoffice.rosters.lottery', ['reservation' => $program['id']]) }}';
        const smsEmailUrl = '{{ route('backoffice.rosters.send-sms-email', ['reservation' => $program['id']]) }}';
        const downloadUrl = '{{ route('backoffice.rosters.download', ['reservation' => $program['id']]) }}';

        window.rosterDetailConfig = {
            reservationId,
            lotteryUrl,
            smsEmailUrl,
            downloadUrl,
        };
    });
</script>
@endsection

