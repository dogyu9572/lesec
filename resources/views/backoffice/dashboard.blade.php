@extends('backoffice.layouts.app')

@section('title', $pageTitle ?? '')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/reservation-calendar.css') }}">
@endsection

@section('content')
<div class="dashboard-content">
    <!-- 대시보드 헤더 -->
    <div class="dashboard-header">
        <div class="dashboard-welcome">
            <p>{{ auth()->user()->name ?? '관리자' }}님, 환영합니다!</p>
            <p>{{ date('Y년 m월 d일') }} 백오피스 대시보드 현황입니다.</p>
        </div>
        <div class="dashboard-actions">
            <a href="{{ route('backoffice.setting.index') }}" class="dashboard-action-btn">
                <i class="fas fa-cog"></i> 환경설정
            </a>
            <a href="{{ url('/') }}" target="_blank" class="dashboard-action-btn">
                <i class="fas fa-home"></i> 사이트 방문
            </a>
        </div>
    </div>

    <!-- 통계 요약 -->
    <div class="stats-row">
        <div class="stat-card stat-programs">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3>활성 프로그램</h3>
                <p class="stat-number">{{ $programStats['active_count'] ?? 0 }}</p>
            </div>
        </div>

        <div class="stat-card stat-applications">
            <div class="stat-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-info">
                <h3>오늘 신청</h3>
                <p class="stat-number">{{ $applicationStats['today_count'] ?? 0 }}</p>
            </div>
        </div>

        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>승인 대기</h3>
                <p class="stat-number">{{ $applicationStats['pending_count'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- 예약 캘린더 -->
    <div class="board-container" style="margin-bottom: 2rem;">
        <div class="board-card">
            <div class="board-card-body">
                <div class="schedule_wrap">
                    <div class="schedule_table">
            <div class="schedule_top">
                <div class="month">
                    <button type="button" class="arrow prev" data-year="{{ $year ?? now()->year }}" data-month="{{ $month ?? now()->month }}">이전달</button>
                    <strong>{{ $year ?? now()->year }}.{{ str_pad($month ?? now()->month, 2, '0', STR_PAD_LEFT) }}</strong>
                    <button type="button" class="arrow next" data-year="{{ $year ?? now()->year }}" data-month="{{ $month ?? now()->month }}">다음달</button>
                    <button type="button" class="btn_today">당일보기</button>
                </div>
                <ul class="info flex">
                    <li class="i_select">선택날짜</li>
                    <li class="i_possible">예약가능</li>
                    <li class="i_impossible">예약마감</li>
                </ul>
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
                        @if(isset($calendar))
                            @foreach($calendar as $week)
                            <tr>
                                @foreach($week as $dayData)
                                @php
                                    $currentYear = $year ?? now()->year;
                                    $currentMonth = $month ?? now()->month;
                                    $dateStr = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayData['day'], 2, '0', STR_PAD_LEFT);
                                    $isSelected = request()->input('date') === $dateStr;
                                    $tdClasses = ['calendar-day'];
                                    if ($dayData['disabled']) {
                                        $tdClasses[] = 'disabled';
                                    } else {
                                        $tdClasses[] = 'clickable';
                                    }
                                    if ($isSelected) {
                                        $tdClasses[] = 'selected';
                                    }
                                    $summary = $dayData['reservation_summary'] ?? ['individual' => 0, 'group' => 0, 'total' => 0];
                                    $reservationsForDate = ($reservationsByDate[$dateStr] ?? ['individual' => [], 'group' => []]) ?? ['individual' => [], 'group' => []];
                                @endphp
                                <td class="{{ implode(' ', $tdClasses) }}" data-date="{{ $dateStr }}">
                                    <span>{{ $dayData['day'] }}</span>
                                    @if(!empty($dayData['is_disabled_date']))
                                        <div class="schedule-disabled">예약불가</div>
                                    @elseif(!$dayData['disabled'] && ($summary['individual'] > 0 || $summary['group'] > 0))
                                        <ul class="list">
                                            @foreach(($reservationsForDate['individual'] ?? []) as $reservation)
                                            @php
                                                $statusClass = ($reservation['is_closed'] ?? false) ? 'i_impossible' : 'i_possible';
                                            @endphp
                                            <li class="{{ $statusClass }}">
                                                {{ \Illuminate\Support\Str::limit($reservation['program_name'] ?? '', 20) }}
                                            </li>
                                            @endforeach
                                            @foreach(($reservationsForDate['group'] ?? []) as $reservation)
                                            @php
                                                $statusClass = ($reservation['is_closed'] ?? false) ? 'i_impossible' : 'i_possible';
                                            @endphp
                                            <li class="{{ $statusClass }}">
                                                {{ \Illuminate\Support\Str::limit($reservation['program_name'] ?? '', 20) }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 데이터 그리드 -->
    <div class="dashboard-grid">
        <!-- 최근 신청 현황 -->
        <div class="grid-item grid-col-12">
            <div class="grid-item-header">
                <h3>최근 신청 현황</h3>
                <div class="more-btn-group application-tabs">
                    <button type="button" class="more-btn application-tab-btn active" data-type="individual">
                        개인
                    </button>
                    <button type="button" class="more-btn application-tab-btn" data-type="group">
                        단체
                    </button>
                </div>
            </div>
            <div class="grid-item-body">
                <table class="dashboard-table" id="recentApplicationsTable">
                    <thead>
                        <tr>
                            <th>신청번호</th>
                            <th>프로그램명</th>
                            <th>신청자명</th>
                            <th>신청일시</th>
                            <th>상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentGroupApplications as $application)
                            <tr class="application-row" data-application-type="group" style="display: none;">
                                <td>{{ $application['application_number'] ?? '-' }}</td>
                                <td>
                                    <span class="text-dark fw-medium">
                                        {{ \Illuminate\Support\Str::limit($application['program_name'], 20) }}
                                    </span>
                                </td>
                                <td>{{ $application['applicant_name'] ?? '-' }}</td>
                                <td>{{ $application['applied_at'] ?? '-' }}</td>
                                <td>
                                    <span class="table-badge badge-{{ $application['status'] === '승인완료' || $application['status'] === '당첨' ? 'success' : ($application['status'] === '승인대기' || $application['status'] === '대기중' ? 'warning' : 'secondary') }}">
                                        {{ $application['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="application-row empty-message" data-application-type="group" style="display: none;">
                                <td colspan="5" class="text-center">신청 내역이 없습니다.</td>
                            </tr>
                        @endforelse
                        @forelse($recentIndividualApplications as $application)
                            <tr class="application-row" data-application-type="individual">
                                <td>{{ $application['application_number'] ?? '-' }}</td>
                                <td>
                                    <span class="text-dark fw-medium">
                                        {{ \Illuminate\Support\Str::limit($application['program_name'], 20) }}
                                    </span>
                                </td>
                                <td>{{ $application['applicant_name'] ?? '-' }}</td>
                                <td>{{ $application['applied_at'] ?? '-' }}</td>
                                <td>
                                    <span class="table-badge badge-{{ $application['status'] === '승인완료' || $application['status'] === '당첨' ? 'success' : ($application['status'] === '승인대기' || $application['status'] === '대기중' ? 'warning' : 'secondary') }}">
                                        {{ $application['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="application-row empty-message" data-application-type="individual">
                                <td colspan="5" class="text-center">신청 내역이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 접속 통계 그래프 -->
    <div class="stats-chart-section">
        <div class="grid-item grid-col-12">
            <div class="grid-item-header">
                <h3>방문객 통계</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="daily">일별</button>
                    <button class="chart-type-btn" data-type="monthly">월별</button>
                </div>
            </div>
            <div class="grid-item-body">
                <div class="visitor-summary">
                    <div class="visitor-stat">
                        <span class="visitor-label">오늘 방문객</span>
                        <span class="visitor-number">{{ $visitorStats['today_visitors'] ?? 0 }}</span>
                    </div>
                    <div class="visitor-stat">
                        <span class="visitor-label">총 방문객</span>
                        <span class="visitor-number">{{ number_format($visitorStats['total_visitors'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="visitorChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<div id="reservation-calendar-config" 
     data-reservations-url="{{ route('backoffice.reservation-calendar.reservations') }}"
     data-base-url="{{ route('backoffice.reservation-calendar.index') }}"
     style="display: none;"></div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/backoffice/dashboard.js') }}"></script>
<script src="{{ asset('js/backoffice/reservation-calendar.js') }}"></script>
@endsection
