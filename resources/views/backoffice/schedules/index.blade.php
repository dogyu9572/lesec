@extends('backoffice.layouts.app')

@section('title', '일정 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/reservation-calendar.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/schedules.css') }}">
@endsection

@section('content')
<div class="board-container">
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

    <div class="board-card">
        <div class="board-card-body">
            <div class="schedule_wrap">
                <!-- 왼쪽: 캘린더 -->
                <div class="schedule_table">
                    <div class="schedule_top">
                        <div class="month">
                            <button type="button" class="arrow prev" data-year="{{ $year }}" data-month="{{ $month }}">이전달</button>
                            <strong>{{ $year }}.{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</strong>
                            <button type="button" class="arrow next" data-year="{{ $year }}" data-month="{{ $month }}">다음달</button>
                        </div>
                    </div>
                    <div class="table">
                        <table>
                            <thead>
                                <tr>
                                    <th>일</th>
                                    <th>월</th>
                                    <th>화</th>
                                    <th>수</th>
                                    <th>목</th>
                                    <th>금</th>
                                    <th>토</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendar as $week)
                                <tr>
                                    @foreach($week as $dayData)
                                    @php
                                        $dateStr = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayData['day'], 2, '0', STR_PAD_LEFT);
                                        $daySchedules = $schedulesByDate[$dateStr] ?? [];
                                        $disabledScheduleTitle = null;
                                        foreach ($daySchedules as $schedule) {
                                            if ($schedule->disable_application) {
                                                $disabledScheduleTitle = $schedule->title;
                                                break;
                                            }
                                        }
                                        $isSelected = request()->input('date') === $dateStr;
                                        $isDisabledDate = $dayData['is_disabled_date'] ?? false;
                                        $disabledDateTitle = $dayData['disabled_date_title'] ?? null;
                                        $dayPrograms = $dayData['programs'] ?? [];
                                    @endphp
                                    <td @if($dayData['disabled']) class="disabled" @endif data-date="{{ $dateStr }}" class="calendar-day @if(!$dayData['disabled']) clickable @endif @if($isSelected) selected @endif">
                                        <span>{{ $dayData['day'] }}</span>
                                        @if($isDisabledDate && $disabledDateTitle)
                                        <div class="schedule-disabled">{{ $disabledDateTitle }}</div>
                                        @elseif(!$dayData['disabled'] && $disabledScheduleTitle)
                                        <div class="schedule-disabled">{{ $disabledScheduleTitle }}</div>
                                        @elseif(!$dayData['disabled'] && count($dayPrograms) > 0)
                                        <ul class="list">
                                            @foreach($dayPrograms as $program)
                                            @php
                                                $appliedCount = $program->applied_count ?? 0;
                                                $capacity = $program->capacity ?? 0;
                                                $isUnlimited = $program->is_unlimited_capacity ?? false;
                                                
                                                // reservation-calendar와 동일한 로직으로 마감 여부 확인
                                                $isClosed = false;
                                                $today = now();
                                                if ($program->application_end_date) {
                                                    $applicationEndDate = \Carbon\Carbon::parse($program->application_end_date);
                                                    if ($applicationEndDate->lt($today)) {
                                                        $isClosed = true;
                                                    }
                                                }
                                                if (!$isClosed && !$isUnlimited && $capacity && $appliedCount >= $capacity) {
                                                    $isClosed = true;
                                                }
                                                
                                                $statusClass = $isClosed ? 'i_impossible' : 'i_possible';
                                            @endphp
                                            <li class="{{ $statusClass }}" 
                                                data-applied="{{ $appliedCount }}" 
                                                data-total="{{ $capacity }}"
                                                title="{{ $program->program_name }}">{{ $program->program_name }}</li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 오른쪽: 일정 리스트 -->
                <div class="glbox select_day schedule-list-panel">
                    <div class="top">
                        <dl class="day">
                            <dt>선택일자</dt>
                            <dd id="selected-date-display">날짜를 선택하세요</dd>
                        </dl>
                        <div class="scroll tbl">
                            <table>
                                <thead>
                                    <tr>
                                        <th>NO</th>
                                        <th>제목</th>
                                        <th>시작날짜</th>
                                        <th>종료날짜</th>
                                        <th>내용</th>
                                    </tr>
                                </thead>
                                <tbody id="schedule-list-tbody">
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px;">날짜를 선택하세요</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="schedule-month-section">
            <div class="table-responsive">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>제목</th>
                            <th>시작날짜</th>
                            <th>종료날짜</th>
                            <th>내용</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlySchedules as $index => $schedule)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $schedule->title }}</td>
                            <td>{{ $schedule->start_date->format('Y.m.d') }}</td>
                            <td>{{ $schedule->end_date->format('Y.m.d') }}</td>
                            <td>{{ $schedule->content ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary btn-edit" data-id="{{ $schedule->id }}">수정</button>
                                <form action="{{ route('backoffice.schedules.destroy', $schedule) }}" method="POST" style="display: inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">삭제</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">해당 월에 등록된 일정이 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 일정 등록/수정 모달 -->
<div id="schedule-modal" class="category-modal schedule-modal" style="display: none;">
    <div class="category-modal-overlay" id="schedule-modal-overlay"></div>
    <div class="category-modal-content">
        <div class="category-modal-header">
            <h5 id="modal-title">일정등록</h5>
            <button type="button" class="category-modal-close" id="btn-close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <form id="schedule-form" method="POST">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="schedule_id" id="schedule-id">

                <div class="modal-form-group">
                    <label for="title" class="modal-form-label">제목 <span class="required">*</span></label>
                    <input type="text" class="modal-form-control" id="title" name="title" required>
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-col">
                        <div class="modal-form-group">
                            <label for="start_date" class="modal-form-label">시작 날짜 <span class="required">*</span></label>
                            <input type="date" class="modal-form-control" id="start_date" name="start_date" required>
                        </div>
                    </div>
                    <div class="modal-form-col">
                        <div class="modal-form-group">
                            <label for="end_date" class="modal-form-label">종료 날짜 <span class="required">*</span></label>
                            <input type="date" class="modal-form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                </div>

                <div class="modal-form-group modal-form-checkbox">
                    <div>
                        <input type="checkbox" id="disable_application" name="disable_application" value="1">
                        <label for="disable_application">기간 내 교육신청 불가</label>
                    </div>
                </div>

                <div class="modal-form-group">
                    <label for="content" class="modal-form-label">내용</label>
                    <textarea class="modal-form-control" id="content" name="content" rows="5" placeholder="내용을 입력하세요."></textarea>
                </div>
            </form>
        </div>
        <div class="category-modal-footer">
            <button type="submit" form="schedule-form" class="btn btn-primary">저장</button>
            <button type="button" class="btn btn-secondary" id="btn-cancel-modal">취소</button>
        </div>
    </div>
</div>
</div>

<div id="schedule-config" 
     data-base-url="{{ route('backoffice.schedules.index') }}"
     data-store-url="{{ route('backoffice.schedules.store') }}"
     data-schedules-url="{{ route('backoffice.schedules.schedules') }}"
     style="display: none;"></div>

@once
@push('scripts')
<script src="{{ asset('js/backoffice/schedules.js') }}"></script>
@endpush
@endonce
@endsection
