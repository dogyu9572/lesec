@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<style>
.access-statistics-page {
    margin: 20px 0;
}

.stats-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.stat-summary {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.stat-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-summary-item {
    text-align: center;
}

.stat-summary-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.stat-summary-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.stat-summary-date {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.stats-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.section-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.date-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-filter input {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.stats-table th,
.stats-table td {
    padding: 5px;
    text-align: center;
    border: 1px solid #eee;
}

.stats-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.stats-table tbody tr:hover {
    background: #f8f9fa;
}

</style>
@endsection

@section('title', '접속통계')

@section('content')
<div class="access-statistics-page board-container">
    <div class="stats-main">
        <!-- 통계 요약 -->
        <div class="stat-summary">
            <h6 style="margin-top: 0; margin-bottom: 15px;">접속통계</h6>
            <div class="stat-summary-grid">
                <div class="stat-summary-item">
                    <div class="stat-summary-label">전체 방문자수</div>
                    <div class="stat-summary-value">{{ number_format($total_visitors) }}</div>
                </div>
                <div class="stat-summary-item">
                    <div class="stat-summary-label">오늘 방문자수</div>
                    <div class="stat-summary-value">{{ number_format($today_visitors) }}</div>
                </div>
                <div class="stat-summary-item">
                    <div class="stat-summary-label">어제 방문자수</div>
                    <div class="stat-summary-value">{{ number_format($yesterday_visitors) }}</div>
                </div>
                <div class="stat-summary-item">
                    <div class="stat-summary-label">최고 방문자수</div>
                    <div class="stat-summary-value">{{ number_format($peak_visitors) }}</div>
                    @if($peak_date)
                        <div class="stat-summary-date">{{ $peak_date }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 연별 방문자 -->
        <div class="stats-section">
            <div class="section-header">
                <h5>연도별 방문자</h5>
                <div class="date-filter">
                    <label>연도:</label>
                    <input type="number" id="year-filter" min="{{ now()->year - 4 }}" max="{{ now()->year }}" value="{{ $selected_year }}" class="form-control" style="width: 100px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="loadYearStats()">조회</button>
                </div>
            </div>
            <div id="year-stats-table">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>연도</th>
                            @foreach($year_stats as $stat)
                                <th>{{ $stat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>방문자수</td>
                            @foreach($year_stats as $stat)
                                <td>{{ number_format($stat['count']) }}명</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 월별 방문자 -->
        <div class="stats-section">
            <div class="section-header">
                <h5>월별 방문자</h5>
                <div class="date-filter">
                    <label>시작월:</label>
                    <input type="month" id="month-start-filter" value="{{ now()->format('Y') }}-01" class="form-control" style="width: 150px;">
                    <label style="margin-left: 10px;">종료월:</label>
                    <input type="month" id="month-end-filter" value="{{ now()->format('Y') }}-12" class="form-control" style="width: 150px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="loadMonthStats()">조회</button>
                </div>
            </div>
            <div id="month-stats-table">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>월</th>
                            @foreach($month_stats as $stat)
                                <th>{{ $stat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>방문자수</td>
                            @foreach($month_stats as $stat)
                                <td>{{ number_format($stat['count']) }}명</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 날짜별 방문자 -->
        <div class="stats-section">
            <div class="section-header">
                <h5>날짜별 방문자</h5>
                <div class="date-filter">
                    <label>시작일:</label>
                    <input type="date" id="date-start-filter" value="{{ $selected_date }}" class="form-control" style="width: 150px;">
                    <label style="margin-left: 10px;">종료일:</label>
                    <input type="date" id="date-end-filter" value="{{ $selected_date }}" class="form-control" style="width: 150px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="loadDateStats()">조회</button>
                </div>
            </div>
            <div id="date-stats-table">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>날짜</th>
                            @foreach($date_stats as $stat)
                                <th>{{ $stat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>방문자수</td>
                            @foreach($date_stats as $stat)
                                <td>{{ number_format($stat['count']) }}명</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 시간별 방문자 -->
        <div class="stats-section">
            <div class="section-header">
                <h5>시간별 방문자</h5>
                <div class="date-filter">
                    <label>날짜:</label>
                    <input type="date" id="hour-date-filter" value="{{ $selected_date }}" class="form-control" style="width: 150px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="loadHourStats()">조회</button>
                </div>
            </div>
            <div id="hour-stats-table">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>시간</th>
                            @foreach($hour_stats as $stat)
                                <th>{{ $stat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>방문자수</td>
                            @foreach($hour_stats as $stat)
                                <td>{{ number_format($stat['count']) }}명</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/access-statistics.js') }}"></script>
@endsection

